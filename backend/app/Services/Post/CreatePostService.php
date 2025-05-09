<?php
namespace App\Services\Post;

use App\Models\User;
use App\Models\Post;
use App\Models\UserFollower;
use App\Repositories\Notification\NotificationRepositoryInterface; 
use App\Services\Image\S3StorageService;
use App\DTOs\NotificationDTO; 
use Illuminate\Support\Collection; 
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Str; 
use DateTimeImmutable;
use Illuminate\Database\QueryException; 

class CreatePostService {
    private $user;
    private $post;
    private $userFollower;
    private $s3StorageService;
    private NotificationRepositoryInterface $notificationRepository; 
    private $response;

    public function  __construct(User $user, Post $post, UserFollower $userFollower, S3StorageService $s3StorageService, NotificationRepositoryInterface $notificationRepository) {
        $this->user = $user;
        $this->post = $post;
        $this->userFollower = $userFollower;
        $this->s3StorageService = $s3StorageService;
        $this->notificationRepository = $notificationRepository;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }

     //在資料庫修改貼文者總發文數
     public function changePostAmount($userId) {
        if ($this->user->changeUserPostAmount($userId, 1) != true) {
            $this->response['error'] = '貼文數更新失敗';
            return $this->response;
        }
        
        $this->response['success'] = true;
        return $this->response; 
     }

     //上傳圖片，並回傳圖片網址
     public function uploadBase64Image($base64Image){
        $uploadBase64Image = $this->s3StorageService->uploadBase64Image($base64Image,'posts');
        return $uploadBase64Image;
     }

     //在資料庫新增貼文
     public function createPostToDatabase($userId, $name, $content, $tag, $imageUrl){
        if ($this->post->createPost($userId, $name, $content, $tag, $imageUrl)) {
            $this->response['success'] = true;
            $this->response['data'] = ['message' => '新增貼文至資料庫成功'];
        } else {
            $this->response['error'] = '新增貼文至資料庫失敗';
        }

        return $this->response;
     }

     //找出用戶粉絲id發送通知
    public function notifyFollowersOfNewPost($userId){
        try {
            $userData = $this->user->userInformation($userId);
        } catch (QueryException $e) {
            Log::error("Failed to query user data for user {$userId}: " . $e->getMessage(), ['exception' => $e]);
            $response = [
                'success' => false,
                'error' => '查詢用戶資料失敗'
            ];
            return $response;
        }
        try {
            $followersId = $this->userFollower->getUserFollowers($userId);
        } catch (QueryException $e) {
            Log::error("Failed to query followers for user {$userId}: " . $e->getMessage(), ['exception' => $e]);
            $response = [
                'success' => false,
                'error' => '查詢粉絲失敗'
            ];
            return $response;
        }

        if (empty($followersId)) {
            $response = ['success' => true];
            return $response;
        }

        $notifications = new Collection();
        $userModelClass = User::class; 
        $userName = $userData->name;
        $now = new DateTimeImmutable();

        foreach ($followersId as $followerId) {
            if (!isset($followerId->follower_id)) {
                Log::warning('Follower data item missing follower_id property.', ['item' => $followerId, 'user_id' => $userId]);
                continue;
            }
            $followerId = (string) $followerId->follower_id;

            $notificationMessage = [
                'message' => "{$userName}發布了新貼文"
            ];

            $dto = new NotificationDTO(
                id: Str::uuid()->toString(),
                type: 'NewPostNotification', 
                notifiableType: $userModelClass, 
                notifiableId: $followerId,       
                causerId: (string) $userId,     
                causerType: $userModelClass,     
                data: $notificationMessage, 
                readAt: null,                   
                createdAt: $now,
                updatedAt: $now
            );
            $notifications->push($dto);
        }

        if ($notifications->isEmpty()) {
            $response['success'] = true;
            return $response;
        }

        $saveSuccess = $this->notificationRepository->saveMany($notifications);

        if ($saveSuccess) {
            $response =[
                'success' =>  true,
                'message' => '通知發出成功'
            ];
        } else {
            $response =[
                'success' =>  false,
                'message' => '通知發出失敗'
            ];
        }

        return $response;
    }
}
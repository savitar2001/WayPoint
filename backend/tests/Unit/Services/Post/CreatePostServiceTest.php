<?php
use Tests\TestCase;
use App\Services\Post\CreatePostService;
use App\Models\User;
use App\Models\Post;
use App\Models\UserFollower;
use App\Services\Image\S3StorageService;
use App\Repositories\Notification\NotificationRepositoryInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use App\DTOs\NotificationDTO;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Str; 

class CreatePostServiceTest extends TestCase {
    private $userMock;
    private $postMock;
    private $s3StorageServiceMock;
    private $userFollowerMock; 
    private $notificationRepositoryMock;
    private $createPostService;

    protected function setUp(): void {
        $this->userMock = $this->createMock(User::class);
        $this->postMock = $this->createMock(Post::class);
        $this->s3StorageServiceMock = $this->createMock(S3StorageService::class);
        $this->userFollowerMock = $this->createMock(UserFollower::class); 
        $this->notificationRepositoryMock = $this->createMock(NotificationRepositoryInterface::class); 

        $this->createPostService = new CreatePostService(
            $this->userMock,
            $this->postMock,
            $this->userFollowerMock, 
            $this->s3StorageServiceMock,
            $this->notificationRepositoryMock 
        );
    }

    public function testChangePostAmountSuccess() {
        $this->userMock->method('changeUserPostAmount')->with(1)->willReturn(1); 

        $response = $this->createPostService->changePostAmount(1);

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }

    public function testChangePostAmountFailure() {
        $this->userMock->method('changeUserPostAmount')->with(1)->willReturn(0);

        $response = $this->createPostService->changePostAmount(1);

        $this->assertFalse($response['success']);
        $this->assertEquals('貼文數更新失敗', $response['error']);
    }

    public function testUploadBase64ImageSuccess() {
        $this->s3StorageServiceMock->method('uploadBase64Image')->with('base64Image', 'post/')->willReturn(['success' => true, 'data' => ['url' => 'http://example.com/image.jpg']]);

        $response = $this->createPostService->uploadBase64Image('base64Image');

        $this->assertTrue($response['success']);
        $this->assertEquals('http://example.com/image.jpg', $response['data']['url']);
    }

    public function testUploadBase64ImageFail() {
        $this->s3StorageServiceMock->method('uploadBase64Image')->with('base64Image', 'post/')->willReturn(['success' => false, 'error' => '上傳圖片失敗']);

        $response = $this->createPostService->uploadBase64Image('base64Image');

        $this->assertFalse($response['success']);
        $this->assertEquals('上傳圖片失敗', $response['error']);
    }

    public function testCreatePostToDatabaseSuccess() {
        $this->postMock->method('createPost')->with(1, 'Authur', 'Post Content', 'Tag', 'imageUrl')->willReturn(true); 

        $response = $this->createPostService->createPostToDatabase(1, 'Authur', 'Post Content', 'Tag', 'imageUrl');

        $this->assertTrue($response['success']);
        $this->assertEmpty($response['error']);
    }

    public function testCreatePostToDatabaseFailure() {
        $this->postMock->method('createPost')->with(1, 'Authur', 'Post Content', 'Tag', 'imageUrl')->willReturn(false);  

        $response = $this->createPostService->createPostToDatabase(1, 'Authur', 'Post Content', 'Tag', 'imageUrl');
        
        $this->assertFalse($response['success']);
        $this->assertEquals('新增貼文至資料庫失敗', $response['error']);
    }

    public function testNotifyFollowersOfNewPostSuccess() {
        $userId = 1;
        $userName = 'Test User';
        $userData = (object)['name' => $userName];
        $follower1 = (object)['follower_id' => '101'];
        $follower2 = (object)['follower_id' => '102'];
        $followers = new Collection([$follower1, $follower2]);

        $this->userMock->expects($this->once())
            ->method('userInformation')
            ->with($userId)
            ->willReturn($userData);

        $this->userFollowerMock->expects($this->once())
            ->method('getUserFollowers')
            ->with($userId)
            ->willReturn($followers);

        $this->notificationRepositoryMock->expects($this->once())
            ->method('saveMany')
            ->with($this->callback(function ($notifications) use ($userId, $userName) {
                if (!($notifications instanceof Collection) || $notifications->count() !== 2) {
                    return false;
                }
                foreach ($notifications as $notification) {
                    if (!($notification instanceof NotificationDTO)) return false;
                    if ($notification->causerId !== (string)$userId) return false;
                    if ($notification->data['message'] !== "{$userName}發布了新貼文") return false;
                }
                return true;
            }))
            ->willReturn(true);

        $response = $this->createPostService->notifyFollowersOfNewPost($userId);

        $this->assertTrue($response['success']);
        $this->assertEquals('通知發出成功', $response['message']);
    }

    public function testNotifyFollowersOfNewPostUserInformationQueryException() {
        $userId = 1;
        Log::shouldReceive('error')->once(); // Optional: assert logging

        $this->userMock->expects($this->once())
            ->method('userInformation')
            ->with($userId)
            ->willThrowException(new QueryException('SQL', [], new \Exception()));

        $response = $this->createPostService->notifyFollowersOfNewPost($userId);

        $this->assertFalse($response['success']);
        $this->assertEquals('查詢用戶資料失敗', $response['error']);
    }

    public function testNotifyFollowersOfNewPostGetUserFollowersQueryException() {
        $userId = 1;
        $userData = (object)['name' => 'Test User'];
        Log::shouldReceive('error')->once(); // Optional: assert logging

        $this->userMock->expects($this->once())
            ->method('userInformation')
            ->with($userId)
            ->willReturn($userData);

        $this->userFollowerMock->expects($this->once())
            ->method('getUserFollowers')
            ->with($userId)
            ->willThrowException(new QueryException('SQL', [], new \Exception()));

        $response = $this->createPostService->notifyFollowersOfNewPost($userId);

        $this->assertFalse($response['success']);
        $this->assertEquals('查詢粉絲失敗', $response['error']);
    }

    public function testNotifyFollowersOfNewPostNoFollowers() {
        $userId = 1;
        $userData = (object)['name' => 'Test User'];
        $followers = new Collection([]);

        $this->userMock->expects($this->once())
            ->method('userInformation')
            ->with($userId)
            ->willReturn($userData);

        $this->userFollowerMock->expects($this->once())
            ->method('getUserFollowers')
            ->with($userId)
            ->willReturn($followers);

        $this->notificationRepositoryMock->expects($this->never()) // Ensure saveMany is not called
            ->method('saveMany');

        $response = $this->createPostService->notifyFollowersOfNewPost($userId);

        $this->assertTrue($response['success']);
        $this->assertArrayNotHasKey('error', $response); // Or check for specific message if applicable
        $this->assertArrayNotHasKey('message', $response);
    }
    
    public function testNotifyFollowersOfNewPostFollowerMissingFollowerId() {
        $userId = 1;
        $userName = 'Test User';
        $userData = (object)['name' => $userName];
        // One follower with follower_id, one without
        $follower1 = (object)['follower_id' => '101'];
        $follower2 = (object)['some_other_property' => 'data']; // Missing follower_id
        $followers = new Collection([$follower1, $follower2]);

        Log::shouldReceive('warning')->once()->with(
            'Follower data item missing follower_id property.',
            ['item' => $follower2, 'user_id' => $userId]
        );

        $this->userMock->expects($this->once())
            ->method('userInformation')
            ->with($userId)
            ->willReturn($userData);

        $this->userFollowerMock->expects($this->once())
            ->method('getUserFollowers')
            ->with($userId)
            ->willReturn($followers);

        $this->notificationRepositoryMock->expects($this->once())
            ->method('saveMany')
            ->with($this->callback(function ($notifications) {
                // Expecting only one notification to be created
                return ($notifications instanceof Collection) && $notifications->count() === 1 && $notifications->first()->notifiableId === '101';
            }))
            ->willReturn(true);

        $response = $this->createPostService->notifyFollowersOfNewPost($userId);

        $this->assertTrue($response['success']);
        $this->assertEquals('通知發出成功', $response['message']);
    }


    public function testNotifyFollowersOfNewPostSaveManyFails() {
        $userId = 1;
        $userName = 'Test User';
        $userData = (object)['name' => $userName];
        $follower1 = (object)['follower_id' => '101'];
        $followers = new Collection([$follower1]);

        $this->userMock->expects($this->once())
            ->method('userInformation')
            ->with($userId)
            ->willReturn($userData);

        $this->userFollowerMock->expects($this->once())
            ->method('getUserFollowers')
            ->with($userId)
            ->willReturn($followers);

        $this->notificationRepositoryMock->expects($this->once())
            ->method('saveMany')
            ->willReturn(false);

        $response = $this->createPostService->notifyFollowersOfNewPost($userId);

        $this->assertFalse($response['success']);
        $this->assertEquals('通知發出失敗', $response['message']); // Corrected expected message
    }
}
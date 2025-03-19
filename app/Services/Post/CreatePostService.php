<?php
namespace App\Services\Post;


use App\Models\User;
use App\Models\Post;
use App\Services\Image\S3StorageService;

class CreatePostService {
    private $user;
    private $post;
    private $s3StorageService;
    private $response;

    //創立user、post、S3StorageService對象
    public function  __construct(User $user, Post $post, S3StorageService $s3StorageService) {
        $this->user = $user;
        $this->post = $post;
        $this->s3StorageService = $s3StorageService;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }

     //在資料庫修改貼文者總發文數
     public function changePostAmount($userId) {
        if ($this->user->changeUserPostAmount($userId, 1) !== 1) {
            $this->response['error'] = '貼文數更新失敗';
            return $this->response;
        }
        
        $this->response['success'] = true;
        return $this->response; 
     }

     //上傳圖片，並回傳圖片網址
     public function uploadBase64Image($base64Image){
        $uploadBase64Image = $this->s3StorageService->uploadBase64Image($base64Image,'post/');
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
}
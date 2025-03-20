<?php
namespace App\Services\Post;

use App\Models\User;
use App\Models\Post;
use App\Services\Image\S3StorageService;   

class DeletePostService {
    private $user;
    private $post;
    private $s3StorageService;
    private $response;

    //創立user、post、s3Storage對象
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
        if ($this->user->changeUserPostAmount($userId, -1) !== 1) {
            $this->response['error'] = '貼文數更新失敗';
            return $this->response;
        }
        
        $this->response['success'] = true;
        return $this->response; 
     }

     //刪除s3上的圖片
     public function deleteImage($postId) {
        $fileName = $this->post->searchPost(null,$postId,null);
        $deleteImage = $this->s3StorageService->deleteImage('post/',$fileName['image_url']);
        return $deleteImage;
     }

     //在資料庫刪除貼文
     public function deletePostToDatabase($userId, $postId = null){
        if ($this->post->deletePost($userId, $postId)) {
            $this->response['success'] = true;
            $this->response['data'] = '資料庫貼文刪除成功';
        } else {
            $this->response['error'] = '資料庫貼文刪除失敗';
        }

        return $this->response;
     }
}
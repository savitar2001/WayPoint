<?php
namespace App\Services\User;

use App\Models\User;
use App\Services\Image\S3StorageService;

class CreateAvatarService {
    private $user;
    private $s3StorageService;
    private $response;

    //創立user、 s3Storage對象
    public function  __construct(User $user, S3StorageService $s3StorageService) {
        $this->user= $user;
        $this->s3StorageService = $s3StorageService;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }

    //上傳圖片，並回傳圖片網址
    public function uploadBase64Image($base64Image){
        $uploadBase64Image = $this->s3StorageService->uploadBase64Image($base64Image,'avatar');
        return $uploadBase64Image;
    }

     //在資料庫添加該使用者頭貼資訊
    public function createAvatar($userId,$avatarUrl) {
        if ($this->user->changeUserAvatar($userId, $avatarUrl) !== 1) {
            $this->response['error'] = '頭像更新失敗';
            return $this->response;
        }
        
        $this->response['success'] = true;
        return $this->response; 
     }
}
<?php
namespace App\Services\User;

use App\Models\User;

class CreateAvatarService {
    private $user;
    private $response;

    //創立user對象
    public function  __construct(User $user) {
        $this->user= $user;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
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
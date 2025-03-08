<?php
namespace App\Services\Auth;

use App\Models\User;
use App\Models\Post;
use App\Models\LoginAttempt;


class DeleteAccountService {
    private $user;
    private $loginAttempt;
    private $post;
    private $response;

    //創立user、loginAttempt、post對象
    public function  __construct(User $user, LoginAttempt $loginAttempt, Post $post) {
        $this->user = $user;
        $this->loginAttempt = $loginAttempt;
        $this->post = $post;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }

    //清除登入請求紀錄
    public function clearLoginAttempt($userId) {
        if ($this->loginAttempt->clearAttempt($userId)) {
            $this->response['success'] = true;
        } else{
            $this->response['error'] = '清除登入請求紀錄失敗';
        }

        return $this->response;
    }

    //清除貼文資料
    public function clearPost($userId){
        if ($this->post->deletePost($userId)) {
            $this->response['success'] = true;
        } else {
            $this->response['error'] = '刪除貼文失敗';
        }
        return $this->response;
    }

    //清除用戶資料
    public function clearUserInformation($userId) {
        if ($this->user->deleteUserInformation($userId)) {
            $this->response['success'] = true;
        } else {
            $this->response['error'] = '刪除用戶資料失敗';
        }
        return $this->response;
    }
}
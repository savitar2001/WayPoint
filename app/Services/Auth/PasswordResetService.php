<?php
namespace App\Services\Auth;

use App\Models\User;

class PasswordResetService {
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
    //檢查帳戶是否存在
    public function checkUserExist($email) {
        $checkUserExistByEmailResult = $this->user->checkUserExistByEmail($email);
        if ($checkUserExistByEmailResult) {
            $this->response['success'] = true;
        } else {
            $this->response['error'] = '此帳戶尚未建立';
        }

        return $this->response;
    }

    //檢查名字、郵件帳號、密碼是否符合規範，密碼與確認密碼是否一致
    public function validateUserPasswordReset($password,$confirmPassword) {
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\~?!@#\$%\^&\*])(?=.{8,})/', $password)) {
            $this->response['error'] = '密碼格式不符合規範';
            return $this->response;
        }

        if ($confirmPassword !== $password) {
            $this->response['error'] = '確認密碼與密碼不同';
            return $this->response;
        }

        $this->response['success'] = true;
        return $this->response;
    }

    //在資料庫更新該用戶之密碼
    public function passwordReset($userId, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $changeUserPasswordResult = $this->user->changeUserPassword($userId, $hash);
        if ($changeUserPasswordResult !== 1) {
            $this->response['error'] = '更新密碼失敗';
            return $this->response;
        }

        $this->response['success'] = true;
        return $this->response;
    }
}
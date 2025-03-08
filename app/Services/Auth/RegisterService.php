<?php
namespace App\Services\Auth;

use App\Models\User;

class RegisterService {
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
            $this->response['error'] = '此帳號已經存在';
        } else {
            $this->response['success'] = true;
        }

        return $this->response;
    }

    //檢查名字、郵件帳號、密碼是否符合規範，密碼與確認密碼是否一致
    public function validateUserRegistration($name,$email,$password,$confirmPassword) {
        if (strlen($name) < 1 || strlen($name) > 255 || !preg_match('/^[a-zA-Z0-9\s_-]{0,255}$/', $name)) {
            $this->response['error'] = '名字格式不符合規範';
            return $this->response;
        }

        if (strlen($email) > 255 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->response['error'] = 'email格式不符合規範';
            return $this->response;
        }

        if (!checkdnsrr(substr($email, strpos($email, '@') + 1), 'MX')) {
            $this->response['error'] = '無效郵件地址';
            return $this->response;
        }

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

    //在資料庫創建該用戶
    public function createUser($name,$email,$password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $registrationResult = $this->user->registration($name,$email,$hash);
        if ($registrationResult) {
            $this->response['success'] = true;
            $this->response['data'][] = '帳號註冊完成，請繼續至郵箱進行驗證';
        } else {
            $this->response['error'] = '帳號註冊失敗';
        }
    }

     //更新使用者驗證狀態
     public function updateUserState($userId) {
        if ($this->user->updateValidationState($userId) !== true) {
            $this->response['error'] = '更新驗證狀態失敗';
            return $this->response;
        }

        $this->response['success'] = true;
        return $this->response;
    }
}
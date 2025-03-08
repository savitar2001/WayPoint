<?php
namespace App\Services\Auth;


use App\Models\User;
use App\Models\LoginAttempt;

class LoginService {
    private $user;
    private $loginAttempt;
    private $response;

    //創立user、loginAttempt對象
    public function  __construct(User $user, LoginAttempt $loginAttempt) {
        $this->user= $user;
        $this->loginAttempt = $loginAttempt;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }

    //此帳號是否經過驗證
    public function isVerified($email) {
        if ($this->user->findUserByEmail($email)['verified'] === 0) {
            $this->response['error'] = '用戶尚未經過驗證';
        } else {
            $this->response['success'] = true;
        }
        return $this->response;
    }

    //是否超過登入限制次數
    public function hasExceedLoginAttempt($email) {
        if ($this->user->findUserByEmail($email)['attempts'] > config('auth.max_login_attempt')) {
            $this->response['error'] = '嘗試登入次數超過上限，請在一小時後嘗試';
        } else {
            $this->response['success'] = true;
        }
        return $this->response;
    }

    //驗證密碼，回傳布林
    public function verifyPassword($email,$password) {
        if (password_verify($password,$this->user->findUserByEmail($email)['password']) === false) {
            $this->loginAttempt->recordFailedAttempt($this->getId($email), $_SERVER['REMOTE_ADDR']);
            $this->response['error'] = '密碼錯誤';
        } else {
            $this->response['success'] = true;
            $this->loginAttempt->clearAttempt($this->getId($email));
        }
        return $this->response;
    }

    //登入剩餘次數
    public function getRemainAttempt($email) {
        $remain_attempt = config('auth.max_login_attempt') - $this->user->findUserByEmail($email)['attempts'];
        return $remain_attempt;
    }

    //取得id並記錄於session中
    public function getId($email) {
        $id = $this->user->findUserByEmail($email)['id'];
        return $id;
    }
    
    //取得使用者姓名並記錄於session中
    public function getName($email) {
        $name = $this->user->findUserByEmail($email)['name'];
        return $name;
    }
}
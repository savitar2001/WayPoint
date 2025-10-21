<?php
namespace App\Services\Auth;


use App\Models\User;
use App\Models\LoginAttempt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

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

    //檢查資料格式
    public function validateRequest($email, $password) {
        if (empty($email) || empty($password)) {
            $this->response['error'] = '缺少必要的登入資料';
            return $this->response;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->response['error'] = '無效的郵箱格式';
            return $this->response;
        }

        $this->response['success'] = true;
        return $this->response;
    }

    //此帳號是否經過驗證
    public function isVerified($email) {
        if ($this->user->findUserByEmail($email)->verified === 0) {
            $this->response['error'] = '用戶尚未經過驗證';
        } else {
            $this->response['success'] = true;
        }
        return $this->response;
    }

    //是否超過登入限制次數
    public function hasExceedLoginAttempt($email) {
        if ($this->user->findUserByEmail($email)->attempts > config('auth.max_login_attempt')) {
            $this->response['error'] = '嘗試登入次數超過上限，請在一小時後嘗試';
        } else {
            $this->response['success'] = true;
        }
        return $this->response;
    }

    //驗證密碼，回傳布林
    public function verifyPassword($email,$password) {
        $data = $this->user->findUserByEmail($email);
        if (password_verify($password,$data->password) === false) {
            $this->loginAttempt->recordFailedAttempt($this->getId($email), $_SERVER['REMOTE_ADDR']);
            $this->response['error'] = '密碼錯誤';
        } else {
            $authenticatableUser = $this->user->find($data->id);
            $this->response['success'] = true;
            $this->loginAttempt->clearAttempt($this->getId($email));
            Auth::login($authenticatableUser); 
        }
        return $this->response;
    }

    //登入剩餘次數
    public function getRemainAttempt($email) {
        $useAttempt = $this->user->findUserByEmail($email);
        $remain_attempt = config('auth.max_login_attempt') - $useAttempt['attempts'];
        return $remain_attempt;
    }

    //取得id並記錄於session中
    public function getId($email) {
        $id = $this->user->findUserByEmail($email)->id;
        return $id;
    }
    
    //取得使用者姓名並記錄於session中
    public function getName($email) {
        $name = $this->user->findUserByEmail($email)->name;
        return $name;
    }

    //生成 JWT Token
    public function generateToken($email) {
        try {
            $user = $this->user->findUserByEmail($email);
            
            // 使用 JWTAuth 生成 token
            $token = JWTAuth::fromUser($user);
            
            $this->response['success'] = true;
            $this->response['data'] = [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60, // 秒為單位
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar_url' => $user->avatar_url,
                ]
            ];
        } catch (\Exception $e) {
            $this->response['error'] = '生成 Token 時發生錯誤: ' . $e->getMessage();
            return $this->response;
        }
        return $this->response;
    }

    // 保留舊的 startSession 方法以向後兼容（如果需要）
    public function startSession($email) {
        try {
            Session::put('loggedin', true);
            Session::put('userId', $this->getId($email));
            Session::put('userName', $this->getName($email));
            $this->response['success'] = true;
            $this->response['data']=['loggedin' => true
            ,'userId' => $this->getId($email),
            'userName' => $this->getName($email)];
        } catch (\Exception $e) {
            $this->response['error'] = '加入會話資料時發生錯誤: ' . $e->getMessage();
            return $this->response;
        }
        return $this->response;
    
    }
}
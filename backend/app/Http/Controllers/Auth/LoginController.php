<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\LoginService;
use Illuminate\Http\Request;

class LoginController extends Controller {
    private $loginService;

    public function __construct(LoginService $loginService) {
        $this->loginService = $loginService;
    }

    //登入api
    public function login(Request $request) {
        $email = $request->input('email');
        $password = $request->input('password');

        // 驗證請求資料
        $validation = $this->loginService->validateRequest($email, $password);
        if (!$validation['success']) {
            return response()->json($validation, 400);
        }

        // 檢查是否驗證
        $isVerified = $this->loginService->isVerified($email);
        if (!$isVerified['success']) {
            return response()->json($isVerified, 403);
        }

        // 檢查登入嘗試次數
        $hasAttempts = $this->loginService->hasExceedLoginAttempt($email);
        if (!$hasAttempts['success']) {
            return response()->json($hasAttempts, 429);
        }

        // 驗證密碼
        $verifyPassword = $this->loginService->verifyPassword($email, $password);
        if (!$verifyPassword['success']) {
            $verifyPassword['remaining_attempts'] = $this->loginService->getRemainAttempt($email);
            return response()->json($verifyPassword, 401);
        }

        // 生成 JWT Token
        $tokenData = $this->loginService->generateToken($email);
        if (!$tokenData['success']) {
            return response()->json($tokenData, 500);
        }

        return response()->json($tokenData, 200);
    }
}

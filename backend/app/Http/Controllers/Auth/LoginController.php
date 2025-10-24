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
        try {
            $email = $request->input('email');
            $password = $request->input('password');

            // 記錄請求資訊（生產環境可移除）
            \Log::info('Login attempt', [
                'email' => $email,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

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
                \Log::error('Token generation failed', [
                    'email' => $email,
                    'error' => $tokenData['error'] ?? 'Unknown error'
                ]);
                return response()->json($tokenData, 500);
            }

            return response()->json($tokenData, 200);
            
        } catch (\Exception $e) {
            \Log::error('Login exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'error' => '登入時發生錯誤',
                'message' => config('app.debug') ? $e->getMessage() : '請稍後再試'
            ], 500);
        }
    }
}

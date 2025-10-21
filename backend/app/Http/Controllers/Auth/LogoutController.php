<?php

namespace App\Http\Controllers\Auth;

use App\Services\Auth\LogoutService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class LogoutController extends Controller {
    protected $logoutService;

    public function __construct(LogoutService $logoutService) {
        $this->logoutService = $logoutService;
    }

    // JWT 登出
    public function logout(Request $request) {
        try {
            // 使 JWT Token 失效
            JWTAuth::invalidate(JWTAuth::getToken());
            
            // 如果有其他清理邏輯，可以調用 LogoutService
            $this->logoutService->logout();
            
            return response()->json([
                'success' => true,
                'message' => '登出成功'
            ], 200);
            
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'error' => '登出失敗'
            ], 500);
        }
    }

    // 刷新 Token
    public function refresh() {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
            
            return response()->json([
                'success' => true,
                'access_token' => $newToken,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ], 200);
            
        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'error' => '刷新 Token 失敗'
            ], 401);
        }
    }

    // 獲取當前用戶
    public function me() {
        try {
            $user = auth()->user();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => '未找到用戶信息'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar_url' => $user->avatar_url,
                    'verified' => $user->verified,
                ]
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => '獲取用戶信息失敗'
            ], 500);
        }
    }
}

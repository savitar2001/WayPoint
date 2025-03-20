<?php
namespace App\Services\Auth;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class LogoutService {
    private $response;

    public function  __construct() {
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }
    public function logout() {
        try {
            $this->clearAuth();
            $this->clearSession();
            $this->response['success'] = true;
            $this->response['data'][] = '成功登出';
        } catch(\Exception $e) {
            $this->response['error'] = '登出發生錯誤: ' . $e->getMessage();
            return $this->response;
        }
        return $this->response;
        
    }

    private function clearAuth() {
        Auth::logout();
    }

    private function clearSession() {
        Session::invalidate();
        Session::regenerateToken();
    }
}

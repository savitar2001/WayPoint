<?php

namespace App\Http\Controllers\Auth;

use App\Services\Auth\LogoutService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LogoutController extends Controller {
    protected $logoutService;

    public function __construct(LogoutService $logoutService) {
        $this->logoutService = $logoutService;
    }

    public function logout(Request $request) {
        $logout = $this->logoutService->logout();

        return response()->json($logout, 200);
    }
}

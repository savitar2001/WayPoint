<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\DeleteAccountController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\TestEventController;

Route::get('/', function () {
    return view('welcome');
});

Route::match(['options', 'post'], '/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
Route::delete('/deleteAccount', [DeleteAccountController::class, 'deleteAccount']);
Route::post('/passwordReset', [PasswordResetController::class, 'passwordReset'])->name('passwordReset');
Route::post('/passwordResetVerify', [PasswordResetController::class, 'passwordResetVerify'])->name('passwordResetVerify');
Route::get('/test-event', [TestEventController::class, 'fireTestEvent']);

// 調試路由 - 檢查 session 和 CSRF 狀態
Route::get('/debug-session', function () {
    return response()->json([
        'session_id' => session()->getId(),
        'csrf_token' => csrf_token(),
        'session_data' => session()->all(),
        'headers' => request()->headers->all(),
        'server' => [
            'HTTP_X_FORWARDED_FOR' => request()->header('X-Forwarded-For'),
            'HTTP_X_FORWARDED_PROTO' => request()->header('X-Forwarded-Proto'),
            'HTTP_X_FORWARDED_HOST' => request()->header('X-Forwarded-Host'),
            'REMOTE_ADDR' => request()->ip(),
            'SERVER_NAME' => request()->getHost(),
        ],
        'config' => [
            'session_domain' => config('session.domain'),
            'session_secure' => config('session.secure'),
            'session_same_site' => config('session.same_site'),
            'app_url' => config('app.url'),
        ]
    ]);
});
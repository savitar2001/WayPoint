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

Route::get('/csrf-token', function () {
    return response()->json([
        'csrf_token' => csrf_token(),
    ]);
});
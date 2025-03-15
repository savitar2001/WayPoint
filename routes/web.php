<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\DeleteAccountController;
use App\Http\Controllers\Auth\PasswordResetController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['web'])->group(function () {
    Route::post('/login', [LoginController::class, 'login'])->name('login');
    Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');
    Route::delete('/deleteAccount', [DeleteAccountController::class, 'deleteAccount']);
    Route::post('/passwordReset', [PasswordResetController::class, 'passwordReset'])->name('passwordReset');
    Route::post('/passwordResetVerify', [PasswordResetController::class, 'passwordResetVerify'])->name('passwordResetVerify');
});

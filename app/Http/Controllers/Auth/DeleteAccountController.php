<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\DeleteAccountService;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class DeleteAccountController extends Controller {
    private $deleteAccountService;

    public function __construct(DeleteAccountService $deleteAccountService) {
        $this->deleteAccountService = $deleteAccountService;
    }

    //刪除帳戶api
    public function deleteAccount(Request $request) {
        $userId = Session::get('userId');

        //清除登入請求
        $clearLoginAttempt = $this->deleteAccountService->clearLoginAttempt($userId);
        if ($clearLoginAttempt['success'] != true) {
            return response()->json($clearLoginAttempt, 400);
        }

        //刪除所有貼文
        $clearPost = $this->deleteAccountService->clearPost($userId);
        if ($clearPost['success'] != true){
            return response()->json($clearPost, 500);
        }

        //刪除用戶資訊
        $clearUserInformation = $this->deleteAccountService->clearUserInformation($userId);
        if ($clearUserInformation['success'] != true) {
            return response()->json($clearUserInformation, 500);
        }

        //清除會話資訊
        $clearSession = $this->deleteAccountService->clearSession();
        if ($clearSession['success'] != true){
            return response()->json($clearSession, 500);
        }

        return response()->json($clearSession,200);
    }
}
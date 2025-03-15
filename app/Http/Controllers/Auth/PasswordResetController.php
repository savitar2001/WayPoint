<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\PasswordResetService;
use Illuminate\Http\Request;

class PasswordResetController extends Controller {
    private $passwordResetService;

    public function __construct(PasswordResetService $passwordResetService) {
        $this->passwordResetService = $passwordResetService;
    }

    //重設密碼api
    public function passwordReset(Request $request){
        $data = $request->only(['email']);

        //檢查用戶是否存在
        $checkUser = $this->passwordResetService->checkUserExist($data['email']);
        if (!$checkUser['success']) {
            return response()->json($checkUser, 400);
        }

        //檢查用戶寄驗證信次數
        $verificationRequest = $this->passwordResetService->checkVerificationRequest($data['email']);
        if (!$verificationRequest['success']) {
            return response()->json($verificationRequest, 429); 
        }

        //在資料庫建立寄信紀錄
        $insertSendRecord = $this->passwordResetService->insertSendRecord($data['email']);
        if (!$insertSendRecord['success']) {
            return response()->json($insertSendRecord, 400);
        } 

        //寄驗證信
        $sendEmail = $this->passwordResetService->sendEmail($data['email'], $insertSendRecord['data']['hash'], $insertSendRecord['data']['requestId'], $insertSendRecord['data']['userName'], $insertSendRecord['data']['userId']);
        if (!$sendEmail['success']) {
            return response()->json($sendEmail, 500);
        }

        return response()->json([
            'success' => true,
            'error' => '',
            'data' => ['請至郵件繼續完成密碼重設流程']
        ], 201);
    }

    //驗證信件api
    public function passwordResetVerify(Request $request) {
        $data = $request->only(['requestId', 'hash', 'userId','password', 'confirm_password']);
        
        //檢查驗證信內容
        $inspectVerification = $this->passwordResetService->inspectVerification($data['requestId'], $data['hash']);
        if (!$inspectVerification['success']) {
            return response()->json($inspectVerification, 400);
        }

        //驗證輸入資料
        $validate = $this->passwordResetService->validateUserPasswordReset(
            $data['password'], $data['confirm_password']
         );
         if (!$validate['success']) {
             return response()->json($validate, 400);
         }

        //清除發送信件紀錄
        $clearUserRequest = $this->passwordResetService->clearUserRequest($data['userId']);
        if (!$clearUserRequest['success']) {
            return response()->json($clearUserRequest, 500);
        }

        //更新用戶密碼
        $updateUserPassword = $this->passwordResetService->passwordReset($data['userId'], $data['password']);
        if (!$updateUserPassword['success']) {
            return response()->json($updateUserPassword, 500);
        }

        return response()->json($updateUserPassword, 200);
    }
}
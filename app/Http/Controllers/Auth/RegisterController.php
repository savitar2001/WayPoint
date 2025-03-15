<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\RegisterService;
use Illuminate\Http\Request;

class RegisterController extends Controller {
    private $registerService;

    public function __construct(RegisterService $registerService) {
        $this->registerService = $registerService;
    }

    //註冊api
    public function register(Request $request){
        $data = $request->only(['name', 'email', 'password', 'confirm_password']);

        //檢查用戶是否存在
        $checkUser = $this->registerService->checkUserExist($data['email']);
        if (!$checkUser['success']) {
            return response()->json($checkUser, 400);
        }

        //驗證輸入資料
        $validate = $this->registerService->validateUserRegistration(
            $data['name'], $data['email'], $data['password'], $data['confirm_password']
        );
        if (!$validate['success']) {
            return response()->json($validate, 400);
        }

        //在資料庫建立用戶資訊
        $createUser = $this->registerService->createUser($data['name'], $data['email'], $data['password']);
        if (!$createUser['success']) {
            return response()->json($createUser, 500);
        }

        //檢查用戶寄驗證信次數
        $verificationRequest = $this->registerService->checkVerificationRequest($data['email']);
        if (!$verificationRequest['success']) {
            return response()->json($verificationRequest, 429); 
        }

        //在資料庫建立寄信紀錄
        $insertSendRecord = $this->registerService->insertSendRecord($data['email']);
        if (!$insertSendRecord['success']) {
            return response()->json($insertSendRecord, 400);
        } 

        //寄驗證信
        $sendEmail = $this->registerService->sendEmail($data['email'], $insertSendRecord['data']['hash'], $insertSendRecord['data']['requestId'], $insertSendRecord['data']['userName'], $insertSendRecord['data']['userId']);
        if (!$sendEmail['success']) {
            return response()->json($sendEmail, 500);
        }

        return response()->json($createUser, 201);
    }

    //驗證信件api
    public function verify(Request $request) {
        $data = $request->only(['requestId', 'hash', 'userId']);
        
        //檢查驗證信內容
        $inspectVerification = $this->registerService->inspectVerification($data['requestId'], $data['hash']);
        if (!$inspectVerification['success']) {
            return response()->json($inspectVerification, 400);
        }

        //清除發送信件紀錄
        $clearUserRequest = $this->registerService->clearUserRequest($data['userId']);
        if (!$clearUserRequest['success']) {
            return response()->json($clearUserRequest, 500);
        }

        //更新用戶驗證狀態
        $updateUserState = $this->registerService->updateUserState($data['userId']);
        if (!$updateUserState['success']) {
            return response()->json($updateUserState, 500);
        }

        return response()->json($updateUserState, 200);
    }
}
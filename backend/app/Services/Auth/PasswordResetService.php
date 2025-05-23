<?php
namespace App\Services\Auth;

use App\Models\User;
use App\Services\Auth\SendEmailService;
use App\Services\Auth\VerifyService;

class PasswordResetService {
    private $user;
    private $sendEmailService;
    private $verifyService;
    private $response;

    //創立user, sendEmailService, verifyService對象
    public function  __construct(User $user, SendEmailService $sendEmailService, VerifyService $verifyService) {
        $this->user= $user;
        $this->sendEmailService = $sendEmailService;
        $this->verifyService = $verifyService;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }
    //檢查帳戶是否存在
    public function checkUserExist($email) {
        $checkUserExistByEmailResult = $this->user->checkUserExistByEmail($email);
        if ($checkUserExistByEmailResult) {
            $this->response['success'] = true;
        } else {
            $this->response['error'] = '此帳戶尚未建立';
        }

        return $this->response;
    }

    //檢查郵件密碼是否符合規範，密碼與確認密碼是否一致
    public function validateUserPasswordReset($password,$confirmPassword) {
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[\~?!@#\$%\^&\*])(?=.{8,})/', $password)) {
            $this->response['error'] = '密碼格式不符合規範';
            return $this->response;
        }

        if ($confirmPassword !== $password) {
            $this->response['error'] = '確認密碼與密碼不同';
            return $this->response;
        }

        $this->response['success'] = true;
        return $this->response;
    }

    //檢查用戶註冊信寄發次數是否超過上限
    public function checkVerificationRequest($email) {
        $checkVerificationRequest = $this->sendEmailService->checkVerificationRequest($email, 1);
        return $checkVerificationRequest;
    }

    //插入用戶寄信紀錄
    public function insertSendRecord($email) {
        $insertSendRecord = $this->sendEmailService->insertSendRecord($email, 1);
        return $insertSendRecord;
    }

    //寄發信件
    public function sendEmail($email, $hash, $requestId, $toName, $userId) {
        $sendEmail = $this->sendEmailService->sendEmail($email, 1, $hash, $requestId, $toName, $userId);
        return $sendEmail;
    }

    //驗證信件內容
    public function inspectVerification($requestId, $hash) {
        $inspectVerification = $this->verifyService->inspectVerification($requestId, $hash, 1);
        return $inspectVerification; 
    }

    //清除寄信請求紀錄
    public function clearUserRequest($userId) {
        $clearUserRequest = $this->verifyService->clearUserRequest($userId, 1);
        return $clearUserRequest;
    }

    //在資料庫更新該用戶之密碼
    public function passwordReset($userId, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $changeUserPasswordResult = $this->user->changeUserPassword($userId, $hash);
        if ($changeUserPasswordResult !== 1) {
            $this->response['error'] = '更新密碼失敗';
            return $this->response;
        }

        $this->response['success'] = true;
        $this->response['data'][] = '更新密碼完成';
        return $this->response;
    }
}
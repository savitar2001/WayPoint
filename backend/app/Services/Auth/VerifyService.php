<?php
namespace App\Services\Auth;


use App\Models\User;
use App\Models\Request;

class VerifyService {
    private $user;
    private $request;
    private $response;

    //創立user、request對象
    public function  __construct(User $user, Request $request) {
        $this->user= $user;
        $this->request = $request;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }

    //檢查該用戶信件資訊
    public function inspectVerification($requestId, $hash, $type) {
        $findSendEmailInformationResult = $this->request->findSendEmailInformation($requestId, $type);
        if ($findSendEmailInformationResult['timestamp']  < time() - 60*60*24) {
            $this->response['error'] = '此封信件已過期，請重新請求';
            return $this->response;
        }

        if ($findSendEmailInformationResult['hash'] !== $hash) {
            $this->response['error'] = '無效哈希碼';
            return $this->response;
        }

        $this->response['success'] = true;
        return $this->response;
    }

    //清除請求紀錄
    public function clearUserRequest($userId, $type) {
        if ($this->request->clearRequestRecord($userId, $type) !== 1) {
            $this->response['error'] = '清除請求紀錄失敗';
            return $this->response;
        }
        
        $this->response['success'] = true;
        return $this->response;
    }
}
<?php
namespace App\Services\Auth;


use App\Models\User;
use App\Models\Request;
use App\Mail\VerificationMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendEmailService {
    private $user;
    private $request;
    private $mailer;
    private $response;
    private $temp;

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

    //檢查該用戶信件發送次數是否超過上限
    public function checkVerificationRequest($email, $type) {
        $findUserWithSendEmailRequestResult = $this->user->findUserWithSendEmailRequest($email, $type);
        
        // 修正：使用對象屬性訪問而非陣列訪問
        if ($type === 0) {
            if ($findUserWithSendEmailRequestResult->verified !== 0) {
                $this->response['error'] = '此帳號已驗證過';
                return $this->response;
            }
    
            if ($findUserWithSendEmailRequestResult->request_count >= config('mail.max_verification_requests')) {
                $this->response['error'] = '驗證次數超過當日上限';
                return $this->response;
            }
    
            $this->response['success'] = true;
            return $this->response;
        } else {
            if ($findUserWithSendEmailRequestResult->request_count >= config('mail.max_passwordreset_requests')) {
                $this->response['error'] = '重設密碼次數超過當日上限';
                return $this->response;
            }
    
            $this->response['success'] = true;
            return $this->response;
        }
        
    }

    //插入寄信紀錄
    public function insertSendRecord($email,$type) {
        $findUserWithSendEmailRequestResult = $this->user->findUserWithSendEmailRequest($email,$type);
        
        // 修正：使用對象屬性訪問而非陣列訪問
        $userId = $findUserWithSendEmailRequestResult->id;
        $userName = $findUserWithSendEmailRequestResult->name;
        $hash = password_hash(base64_encode(random_bytes(32)), PASSWORD_DEFAULT);
        $requestId = $this->request->recordSendEmailInformation($userId, $hash, $type);
        if ($requestId === -1){
            $this->response['error'] = '紀錄插入失敗';
        } else {
            $this->response['data'] = [
                'hash' => $hash,
                'requestId' => $requestId,
                'userName' => $userName,
                'userId' => $userId];
            $this->response['success'] = true;
        }
        return $this->response;
    }

    //寄驗證信
    public function sendEmail($email, $type, $hash, $requestId, $toName, $userId){
        if ($type === 0) {
            $url = config('mail.verify_endpoint') . '?' .http_build_query(['id' => $requestId, 'hash' => $hash, 'user' => $userId]);
        } else {
            $url = config('mail.reset_endpoint') . '?' .http_build_query(['id' => $requestId, 'hash' => $hash, 'user' => $userId]);
        }

        try {
            Mail::to($email)->send(new VerificationMail($toName, $url));
            $this->response['success'] = true;
            return $this->response;
        } catch (\Exception $e) {
            Log::error('寄送郵件失敗', ['email' => $email, 'error' => $e->getMessage()]);
            $this->response['error'] = '寄送驗證信失敗';
            return $this->response;
        }
        
    }
} 
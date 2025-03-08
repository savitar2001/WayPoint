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
        $this->temp = array();
    }

    //檢查該用戶信件發送次數是否超過上限
    public function checkVerificationRequest($email, $type) {
        $findUserWithSendEmailRequestResult = $this->user->findUserWithSendEmailRequest($email, $type);
        if ($type === 0) {
            if ($findUserWithSendEmailRequestResult['verified'] !== 0) {
                $this->response['error'] = '此帳號已驗證過';
                return $this->response;
            }
    
            if ($findUserWithSendEmailRequestResult['COUNT(requests.id)'] >= config('mail.max_verification_requests')) {
                $this->response['error'] = '驗證次數超過當日上限';
                return $this->response;
            }
    
            $this->response['success'] = true;
            return $this->response;
        } else {
            if ($findUserWithSendEmailRequestResult['COUNT(requests.id)'] >= config('mail.max_passwordreset_requests')) {
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
        $userId = $findUserWithSendEmailRequestResult['id'];
        $userName = $findUserWithSendEmailRequestResult['name'];
        $hash = password_hash(base64_encode(random_bytes(32)), PASSWORD_DEFAULT);
        $requestId = $this->request->recordSendEmailInformation($userId, $hash, $type);
        if ($requestId === -1){
            $this->response['error'] = '紀錄插入失敗';
        } else {
            $this->temp[] = $hash;
            $this->temp[] = $requestId;
            $this->temp[] = $userName;
            $this->temp[] = $userId;
            $this->response['success'] = true;
        }
        return $this->response;
    }

    //寄驗證信
    public function sendEmail($email, $type){
        if (count($this->temp) !== 4) {
            $this->response['error'] = '未初始化發送紀錄';
            return $this->response;
        }
        $hash = $this->temp[0];
        $requestId = $this->temp[1];
        $toName = $this->temp[2];
        $userId = $this->temp[3];
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
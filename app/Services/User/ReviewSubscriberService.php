<?php
namespace App\Services\User;

use App\Models\UserSubscriber;

class ReviewSubscriberService{
    private $userSubscriber;
    private $response;

     //創立userSubscriber對象
    public function  __construct(UserSubscriber $userSubscriber ) {
        $this->userSubscriber = $userSubscriber;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }

    //查詢用戶所有追蹤用戶id
    public function  getAllUserSubscribers($userId) {
        $res = $this->userSubscriber->getUserSubscribers($userId);
        if ($res === false) {
            $this->response['error'] = '查詢訂閱者失敗';
        } else {
            $this->response['success'] = true;
            $this->response['data'][] = $res;
        }
        return $this->response;
    }
}
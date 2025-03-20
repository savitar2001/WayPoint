<?php
namespace App\Services\User;

use App\Models\User;
use App\Models\UserFollower;
use App\Models\UserSubscriber;

class AddSubscriberService{
    private $user;
    private $userFollower;
    private $userSubscriber;
    private $response;

     //創立user、userFollower、userSubscriber對象
    public function  __construct(User $user, UserFollower $userFollower, UserSubscriber $userSubscriber ) {
        $this->user = $user;
        $this->userFollower = $userFollower;
        $this->userSubscriber = $userSubscriber;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }

    //添加被追蹤用戶至用戶訂閱者列表
    public function addSubscriberToDatabase($userId, $userSubscriberId) {
        if ($this->userSubscriber->addSubscriber($userId, $userSubscriberId) === false) {
            $this->response['error'] = '添加訂閱者失敗';
        } else {
            $this->response['success'] = true;
        }
        return $this->response;
    }

    //添加用戶至粉絲列表
    public function addFollowerToDatabase($userSubscriberId, $userId) {
        if ($this->userFollower->addFollower($userSubscriberId, $userId) === false) {
            $this->response['error'] = '成為粉絲失敗';
        } else {
            $this->response['success'] = true;
        }
        return $this->response;

    }

    //修改用戶追蹤數
    public function updateUserSubscriberCount($userId){
        if ($this->user->updateSubscriberCount($userId, 1) === false) {
            $this->response['error'] = '修改用戶追蹤數失敗';
        } else {
            $this->response['success'] = true;
        }
        return $this->response;
    }

    //修改用戶粉絲數
    public function updateUserFollowerCount($userId){
        if ($this->user->updateFollowerCount($userId, 1) === false) {
            $this->response['error'] = '修改粉絲數失敗';
        } else {
            $this->response['success'] = true;
        }
        return $this->response;
    }

}
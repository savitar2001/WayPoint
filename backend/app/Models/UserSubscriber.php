<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class UserSubscriber extends Model
{
    use HasFactory;
    
    protected $table = 'user_subscribers';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'subscriber_id',
    ]; 

    //寫入該用戶所追蹤的用戶id
    public function addSubscriber($userId, $userSubscriberId) {
        $query = "INSERT INTO user_subscribers (user_id, subscriber_id) VALUES (?,?)";
        $params = [$userId, $userSubscriberId];
    
        $result = DB::insert($query, $params);

        return (bool) $result;
    }

    //移除該用戶所追蹤的用戶id
    public function removeSubscriber($userId, $userSubscriberId) {
        $query = "DELETE FROM user_subscribers WHERE user_id = ? AND subscriber_id = ?";
        $params = [$userId, $userSubscriberId];
        $result = DB::delete($query, $params);

        return $result > 0;
    }

    //查詢所有用戶追蹤的用戶
    public function getUserSubscribers($userId) {
        $query = "SELECT subscriber_id FROM user_subscribers WHERE user_id = ?";
        $params = [$userId];
        return DB::select($query, $params) ?? null;
    }

}

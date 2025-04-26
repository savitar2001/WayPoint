<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class UserFollower extends Model
{
    use HasFactory;
    
    protected $table = 'user_followers';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'follower_id',
    ]; 

    //寫入該用戶的粉絲id
    public function addFollower($userId, $userFollowerId) {
        $query = "INSERT INTO user_followers (user_id, follower_id) VALUES (?,?)";
        $params = [$userId, $userFollowerId];
        $result = DB::insert($query, $params);

        return (bool) $result;
    }

    //移除該用戶的粉絲id
    public function removeSubscriber($userId, $userFollowerId) {
        $query = "DELETE FROM user_followers WHERE user_id = ? AND follower_id = ?";
        $params = [$userId, $userFollowerId];
        $result = DB::delete($query, $params);

        return $result > 0;
    }

    //查詢該用戶所有粉絲
    public function getUserFollowers($userId) {
        $query = "SELECT follower_id FROM user_followers WHERE user_id = ?";
        $params = [$userId];
        return DB::select($query, $params) ?? null;
    }
}

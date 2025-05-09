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

    /**
     * 查詢該用戶所有粉絲的 ID 陣列，ai推薦寫法，在larvel中推薦query builder寫法
     * @param int $userId
     * @return array<int>
    
     * public function getUserFollowerIds(int $userId): array
      *  {
       *     // 使用查詢建構器的 pluck 方法直接獲取 follower_id 陣列
        *    return DB::table($this->table)
         *           ->where('user_id', $userId)
          *          ->pluck('follower_id')
           *         ->all(); // 將 Collection 轉換為 array
        *}
    */  
}

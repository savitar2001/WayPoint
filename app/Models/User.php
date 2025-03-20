<?php
 
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    //定義賦值欄位
    protected $fillable = [
        'name',
        'email',
        'password',
        'verified', 
        'avatar_url', 
        'post_amount', 
        'subscriber_count', 
        'follower_count'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */

    //隱藏欄位
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */

    

    //login使用函數 
    //返回基本資料與嘗試登入次數
    public function findUserByEmail($email) {
        $hourAgo = time() - 3600;
        $query = "SELECT users.id, name, password, verified, COUNT(loginattempts.id) as attempts
                 FROM users
                 LEFT JOIN loginattempts ON users.id = user AND loginattempts.timestamp > ?
                 WHERE email = ?
                 GROUP BY users.id";
        $params = [$hourAgo, $email];

        return DB::select($query, $params)[0] ?? null;
    }

    //寄信函數可用於註冊與重設密碼
    //返回基本資料與寄信次數
    public function findUserWithSendEmailRequest($email, $type) {
        $oneDayAgo = time() - 60 * 60 * 24;
        $query = "SELECT users.id, name, verified, COUNT(requests.id) as request_count
                  FROM users
                  LEFT JOIN requests ON users.id = requests.user AND type = ? AND timestamp > ?
                  WHERE email = ?
                  GROUP BY users.id";
        $params = [$type, $oneDayAgo, $email];
        return DB::select($query, $params)[0] ?? null;
    }


    //註冊使用函數
     //確認該帳號是否已經創立
     public function checkUserExistByEmail($email) {
        $query = "SELECT id FROM users WHERE email = ?";
        $params = [$email];
        $result = DB::select($query, $params);

        return !empty($result);
    }
     //將使用者加入資料庫
     public function registration($name,$email,$hash) {
        $query = "INSERT INTO users VALUES (NULL, ?, ?, ?, 0,NUll,0,0,0)";
        $params = [$name, $email,$hash];
        $result = DB::insert($query, $params);

        if ($result) {
            return true;
        }

        return false;
    }
    
    //更新用戶驗證狀態
    public function updateValidationState($userId) {
        $query = "UPDATE users SET verified=1 WHERE id=?";
        $params = [$userId];
        $result = DB::update($query, $params);

        return $result > 0;
    }

    //重設密碼使用函數
    //更新用戶密碼
    public function changeUserPassword($userId, $hash) {
        $query = 'UPDATE users SET password=? WHERE id=?';
        $params = [$hash, $userId];
        $result = DB::update($query, $params);

        return $result > 0;
    }

    //刪除帳戶使用函數
    //刪除用戶資料
    public function deleteUserInformation($userId) {
        $query = 'DELETE FROM users WHERE id=?';
        $params = [$userId];
        $result = DB::delete($query, $params);

        return $result > 0;
    }

    //用戶社群資料使用函數
    //更新用戶頭貼
    public function changeUserAvatar($userId, $avatarUrl) {
        $query = 'UPDATE users SET avatar_url = ? WHERE id =?';
        $params = [$avatarUrl, $userId];
        $result = DB::update($query, $params);

        return $result > 0;
    }

    //更新用戶貼文數量
    public function changeUserPostAmount($userId, $amount) {
        $query = 'UPDATE users SET post_amount = post_amount + ? WHERE id = ?';
        $params = [$amount, $userId];
        $result = DB::update($query, $params);

        return $result > 0;
    }

    //用名字搜尋用戶資訊
    public function findUserByName($name) {
        $query = "SELECT id, name, avatar_url FROM users WHERE name = ?";
        $params = [$name];
        return DB::select($query, $params)[0] ?? null;
    }

    //查詢用戶的粉絲id、名字、頭像
    public function findUserFollowerId($userId) {
        $query = "SELECT u.id, u.name, u.avatar_url FROM users u JOIN user_followers uf ON u.id = uf.follower_id WHERE uf.user_id = ?";
        $params = [$userId];
        return DB::select($query, $params) ?? null;
    }

    //查詢用戶所追蹤的用戶id、名字、頭像
    public function findUserSubscriberId($userId) {
        $query = "SELECT u.id, u.name, u.avatar_url FROM users u JOIN user_subscribers us ON u.id = us.subscriber_id WHERE us.user_id = ?";
        $params = [$userId];
        return DB::select($query, $params) ?? null;
    }

    //查詢用戶社交資訊
    public function userInformation($userId) {
        $query = "SELECT id, name, avatar_url, post_amount, subscriber_count, follower_count FROM users WHERE id = ?";
        $params = [$userId];
        return DB::select($query, $params)[0] ?? null;
    }

    //更新用戶追蹤數
    public function updateSubscriberCount($userId, $amount){
        $query = "UPDATE users SET subscriber_count = subscriber_count + ? WHERE id = ?";
        $params = [$amount, $userId];
        $result = DB::update($query, $params);

        return $result > 0;
    }
    //更新用戶粉絲數
    public function updateFollowerCount($userId, $amount){
        $query = "UPDATE users SET follower_count = follower_count + ? WHERE id = ?";
        $params = [$amount, $userId];
        $result = DB::update($query, $params);

        return $result > 0;
    }
}
  

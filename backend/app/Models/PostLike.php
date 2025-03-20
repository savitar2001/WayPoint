<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class PostLike extends Model
{
    use HasFactory;
    
    protected $table = 'post_likes';

    protected $primaryKey = 'id';

    protected $fillable = [
        'post_id',
        'user_id',
    ]; 

    //查詢貼文按讚相關資訊使用函數
    //返回該用戶是否已經對於貼文表達喜歡
    public function hasPostLike($userId, $postId) {
        $query = "SELECT 1 FROM post_likes WHERE post_id = ? AND user_id = ?";
        $params = [$postId, $userId];
        $result = DB::select($query, $params);
    
        return !empty($result);
    }

    //返回所有喜歡該貼文的用戶
    public function getUserLikePost($postId) {
        $query = "SELECT u.id as user_id, u.name, u.avatar_url 
            FROM post_likes pl
            JOIN users u ON pl.user_id = u.id
            WHERE pl.post_id = ?";
        $params = [$postId];

        return DB::select($query, $params) ?? null;
    }

    //對貼文按讚紀錄操作使用函數
     //將喜歡貼文的用戶記錄於資料庫
     public function likePost($userId, $postId) {
        $query = "INSERT INTO post_likes (post_id, user_id, created_at) VALUES (?, ?, NOW())";
        $params = [$postId, $userId];
        $result = DB::insert($query, $params);

        if ($result) {
            return true;
        }

        return false;
    }
    //取消用戶喜歡紀錄
    public function unlikePost($userId, $postId) {
        $query = "DELETE FROM post_likes WHERE post_id = ? AND user_id = ?";
        $params = [$postId, $userId];
        $result = DB::delete($query, $params);

        return $result > 0;
    }
}

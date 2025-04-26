<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class PostComment extends Model
{
    use HasFactory;
    
    protected $table = 'post_comments';

    protected $primaryKey = 'id';

    protected $fillable = [
        'post_id',
        'user_id',
        'content',
        'parent_id',
        'status',
        'reply_count'
    ];
 
    //查詢貼文評論使用函數
    //查詢某則貼文的評論
    public function getPostComment($postId) {
        $query = "SELECT pc.id, pc.content,pc.reply_count, u.id as user_id, u.name, u.avatar_url FROM post_comments pc JOIN users u ON pc.user_id = u.id WHERE pc.post_id = ? AND pc.parent_id IS NULL ORDER BY pc.created_at DESC";
        $params = [$postId];
        return DB::select($query, $params) ?? null;
    }

     //查詢某則評論的回覆
     public function getCommentReply($commentId) {
        $query = "SELECT pc.id, pc.content,pc.reply_count, u.id as user_id, u.name, u.avatar_url FROM post_comments pc JOIN users u ON pc.user_id = u.id  WHERE pc.parent_id = ? ORDER BY pc.created_at DESC";
        $params = [$commentId];
        return DB::select($query, $params) ?? null;
    }

    //針對貼文評論使用函數
    //新增評論
    public function addComment($postId, $userId, $content, $parentId = null) {
        $query = "INSERT INTO post_comments (post_id, user_id, content, parent_id) VALUES (?,?,?,?)";
        $params = [$postId, $userId, $content, $parentId];
        $result = DB::insert($query, $params);

        if ($result) {
            return true;
        }

        return false;
    }

    //刪除評論
    public function deleteComment($commentId, $userId) {
        if ($commentId === null) {
            $query = "DELETE FROM post_comments WHERE user_id = ?";
            $params = [$userId];
        } else {
            $query = "DELETE FROM post_comments WHERE id = ? AND user_id = ?";
            $params = [$commentId, $userId];
        }
        $result = DB::delete($query, $params);

        return $result > 0;
    }

    //修改評論
    public function updateComment($commentId, $userId, $content) {
        $query = "UPDATE post_comments SET content = ? WHERE id = ? AND user_id = ?";
        $params = [$content, $commentId, $userId];
        $result = DB::update($query, $params);

        return $result > 0;
    }

    //針對回覆留言使用函數
    //新增回覆留言
    public function addReplyToComment($commentId, $userId, $content) {
        $query = "INSERT INTO post_comments (post_id, user_id, content, parent_id) 
                      SELECT post_id, ?, ?, ? 
                      FROM post_comments WHERE id = ?";
        $params = [$userId, $content, $commentId, $commentId];
        $result = DB::insert($query, $params);

        if ($result) {
            return true;
        }

        return false;
    }

    //刪除回覆留言
    public function deleteReply($replyId, $userId) {
        if ($replyId === null) {
            $query = "DELETE FROM post_comments WHERE user_id = ? AND parent_id IS NOT NULL";
            $params = [$userId];
        } else {
            $query = "DELETE FROM post_comments WHERE id = ? AND user_id = ? AND parent_id IS NOT NULL";
            $params = [$replyId, $userId];

        }
        $result = DB::delete($query, $params);

        return $result > 0;
    }

    //更新回覆留言
    public function updateReply($replyId, $userId, $content) {
        $query = "UPDATE post_comments SET content = ? WHERE id = ? AND user_id = ? AND parent_id IS NOT NULL";
        $params = [$content, $replyId, $userId];
        $result = DB::update($query, $params);

        return $result > 0;
    }

    //更新評論回覆數量
    public function updateReplyCount($commentId, $amount) {
        $query = "UPDATE `post_comments` SET `reply_count` = `reply_count` + ? WHERE `id` = ?";
        $params = [$amount, $commentId];

        $result = DB::update($query, $params);
        return $result > 0;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
    use HasFactory;
    
    protected $table = 'posts';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id',
        'user_name',
        'content',
        'tag',
        'image_url',
        'status',
        'likes_count',
        'comments_count'
    ];

    //創建貼文
    public function createPost($userId, $name, $content, $tag, $imageUrl) {
        $query = "INSERT INTO posts (user_id, user_name, content, tag, image_url, status, likes_count, comments_count) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [$userId, $name, $content, $tag, $imageUrl, 1,0,0];//數字代表status/likeCount/commentCount
        $result = DB::insert($query, $params);

        return (bool) $result;
    }

    //刪除貼文 
    public function deletePost($userId, $postId = null) {
        if ($postId === null) {
            $query = 'DELETE FROM posts WHERE user_id = ?';
            $params = [$userId];
        } else {
            $query = 'DELETE FROM posts WHERE user_id = ? AND id=?';
            $params = [$userId, $postId];
        }
    
        $result = DB::delete($query, $params);
        return $result > 0;
    }

    //搜尋貼文
    public function searchPost($userId,$postId, $tag) {
        $query = "SELECT * FROM posts WHERE status = 1"; 
        $params = [];

        if ($userId !== null) {
            $query .= " AND user_id = ?";
            $params[] = $userId;
        }

        if ($postId !== null) {
            $query .= " AND id = ?";
            $params[] = $postId;
        }

        if ($tag !== null) {
            $query .= " AND tag LIKE ?";
            $params[] = '%' . $tag . '%';
        }

        return DB::select($query, $params) ?? null;
    }

    //更新貼文喜歡數
    public function updateLikesCount($postId, $amount) {
        $query = "UPDATE `posts` SET `likes_count` = `likes_count` + ? WHERE `id` = ?";;
        $params = [$amount, $postId];

        $result = DB::update($query, $params);

        return $result > 0;
    }

    //更新貼文評論數
    public function updateCommentsCount($postId, $amount) {
        $query = "UPDATE `posts` SET `comments_count` = `comments_count` + ? WHERE `id` = ?";
        $params = [$amount, $postId];

        $result = DB::update($query, $params);

        return $result > 0;
    }
}

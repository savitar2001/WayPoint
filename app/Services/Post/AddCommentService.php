<?php
namespace App\Services\Post;


use App\Models\Post;
use App\Models\PostComment;

class AddCommentService {
    private $post;
    private $postComment;
    private $response;

    //創立post、postComment對象
    public function  __construct(Post $post, PostComment $postComment) {
        $this->post = $post;
        $this->postComment = $postComment;
        $this->response = [
            'success' => false,
            'error' => '',
            'data' => []
        ];
    }

     //在資料庫新增用戶對貼文的留言
     public function addCommentToPost($postId, $userId, $content) {
        if ($this->postComment->addComment($postId, $userId, $content) == false) {
            $this->response['error'] = '新增貼文留言失敗';
            return $this->response;
        }
        
        $this->response['success'] = true;
        return $this->response; 
     }

     //在資料庫新增對其他用戶的回覆
     public function addReplyToComment($commentId, $userId, $content){
        if ($this->postComment->addReplyToComment($commentId, $userId, $content) == false) {
            $this->response['error'] = '新增回覆其他用戶留言失敗';
        } else {
            $this->response['success'] = true;
        }

        return $this->response;
     }

     //在資料庫更新評論數量
     public function updatePostCommentsCount($postId, $amount = 1) {
        if ($this->post->updateCommentsCount($postId, $amount) === false) {
            $this->response['error'] = '更新貼文評論數失敗';
        } else {
            $this->response['success'] = true;
        }

        return $this->response;
     }

     //在資料庫更新評論回覆數量
     public function updateCommentReplyCount($commentId, $amount = 1) {
        if ($this->postComment->updateReplyCount($commentId, $amount) === false) {
            $this->response['error'] = '更新留言回覆數失敗';
        } else {
            $this->response['success'] = true;
        }
        return $this->response;
     }
}
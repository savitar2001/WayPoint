<?php
namespace App\Services\Post;


use App\Models\Post;
use App\Models\PostComment;

class DeleteCommentService {
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

     //在資料庫刪除用戶對貼文的留言
     public function deleteCommentToPost($userId, $commentId = null) {
        if ($this->postComment->deleteComment($commentId, $userId) == false) {
            $this->response['error'] = '刪除貼文留言失敗';
            return $this->response;
        }
        
        $this->response['success'] = true;
        return $this->response; 
     }

     //在資料庫刪除對其他用戶的回覆
     public function deleteReplyToComment($userId, $replyId = null){
        if ($this->postComment->deleteReply($replyId, $userId) == false) {
            $this->response['error'] = '刪除回覆其他用戶留言失敗';
        } else {
            $this->response['success'] = true;
        }

        return $this->response;
     }

     //在資料庫更新評論回覆數量
     public function updateCommentReplyCount($commentId, $amount = -1) {
        if ($this->postComment->updateReplyCount($commentId, $amount) === false) {
            $this->response['error'] = '更新留言回覆數失敗';
        } else {
            $this->response['success'] = true;
        }
        return $this->response;
     }
}
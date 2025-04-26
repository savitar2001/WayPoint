import React, { useState, useEffect } from 'react';
import { useSelector } from 'react-redux';
import './HomePage.css';
import Header from '../../components/Header/Header.js';
import PostCard from '../../components/PostCard/PostCard.js';
import Comment from '../../components/Comment/Comment.js';
import Like from '../../components/Like/Like.js';
import Modal from '../../components/Modal/Modal.js'; // 引入 Modal 組件
import useSubscriber from '../../hooks/UseSubscriber';
import usePosts from '../../hooks/UsePosts';
import useComment from '../../hooks/UseComment'; 
import useReply from '../../hooks/UseReply'; 
import useLike from '../../hooks/UseLike'; 

const HomePage = () => {
  const userId = useSelector((state) => state.auth.userId); // 獲取當前用戶 ID
  const { subscribers, error: subscriberError } = useSubscriber(userId); // 獲取追蹤者資料
  const { posts, fetchPosts, error: postError } = usePosts({ });
  const [selectedPost, setSelectedPost] = useState(null); // 用於存儲選中的貼文
  const { comments, fetchComments, submitComment } = useComment(userId);
  const [selectedComment, setSelectedComment] = useState(null);//用於存儲選中的留言
  const { replies, fetchReplies, addReply } = useReply(userId);
  const [isModalOpen, setIsModalOpen] = useState(false); 
  const { likes,fetchLikes,isLiked,toggleLike } = useLike(userId);
  const [modalType, setModalType] = useState(null); // 'like', 'comment', 'reply' 或 null

  useEffect(() => {
    const fetchSubscriberPosts = async () => {
      if (subscribers.length > 0) {
        const subscriberIds = subscribers.map((subscriber) => subscriber.id); // 獲取追蹤者 ID
        try {
          await fetchPosts(subscriberIds,null,null); // 使用 usePosts 的 fetchPosts 方法調閱貼文
          setSelectedPost(posts);
        } catch (error) {
          console.error('Error fetching posts:', error);
        }
      }
    };

    fetchSubscriberPosts();
  }, [subscribers, fetchPosts]);

  const handleLikeClick = async (postId) => {
    const postToSelect = posts.find(post => post.id === postId);
    try {
      await fetchLikes(postId); 
      setSelectedPost({
        ...postToSelect,
        isLiked: likes.some((like) => Number(like.user_id) === Number(userId)), 
      });
      setModalType('like'); 
      setIsModalOpen(true); // 確保 Modal 打開
    } catch (error) {
      console.error(`Failed to fetch comments for postId: ${postId}`, error);
    }
  };

  const handleToggleLike = async (postId) => {
    if (postId) {
      await toggleLike(postId); // 切換按讚狀態
      setSelectedPost((prevPost) => ({
        ...prevPost,
        isLiked: !prevPost?.isLiked, // 本地切換 isLiked 狀態
      }));
    }
  };

  const handleCommentClick = async (postId) => {
    try {
      const postToSelect = posts.find(post => post.id === postId);
      await fetchComments(postId); // 使用 fetchComments 獲取評論資訊
      setSelectedPost({
        ...postToSelect,
        comments,
        postId, // 將評論資訊存入選中的貼文
      });
      setModalType('comment');
      setIsModalOpen(true); // 確保 Modal 打開
    } catch (error) {
      console.error(`Failed to fetch comments for postId: ${postId}`, error);
    }
  };

  const handleSubmitComment = (newComment) => {
    if (selectedPost?.id) {
      submitComment(selectedPost?.id, newComment);
    }
  };

  const handleReplyClick = async (commentId) => {
    try {
      await fetchReplies(commentId); // 使用 fetchComments 獲取評論資訊
      setSelectedComment(commentId); 
      setModalType('reply') 
      setIsModalOpen(true); // 確保 Modal 打開
    } catch (error) {
      console.error(`Failed to fetch replies for commentId: ${commentId}`, error);
    } 
  }; 

  const handleSubmitReply = async (newReply) => {
    if (selectedComment) { // 確保 selectedComment 是有效的 commentId
      try {
        await addReply(selectedComment, newReply); // 發送回覆
      } catch (error) {
        console.error(`Failed to submit reply for commentId: ${selectedComment}`, error);
      }
    } else {
      console.error('無法提交回覆，因為 selectedComment 無效');
    }
  };

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setModalType(null); // 重置彈窗類型
    setSelectedPost(null);
    setSelectedComment(null);
  };

  if (subscriberError) {
    return <div>Error: {subscriberError}</div>;
  }

  return (
    <div className="home-page">
      <Header title="Home Page" />
      <PostCard
        posts={posts}
        onLike={handleLikeClick} // 直接傳遞 handleLikeClick
        onComment={handleCommentClick} // 直接傳遞 handleCommentClick
      />
        {isModalOpen && selectedPost && (
        <Modal isOpen={isModalOpen} onClose={handleCloseModal}>
          {modalType === 'like' && (
            <div className="likes-section">
              <h3>Likes</h3>
              <Like 
                data={likes} 
                onLike={()=>handleToggleLike(selectedPost.id)} 
                isLiked={selectedPost?.isLiked}
              />
            </div>
          )}
          {modalType === 'comment' && (
            <div className="comments-section">
              <h3>Comments</h3>
              <Comment
                data={comments}
                onSubmit={handleSubmitComment} 
                onReplyClick={handleReplyClick}
                isReplyMode={false}
              />
            </div>
          )}
          {selectedComment && modalType === 'reply' && (
            <div className="replies-section">
              <h4>Replies to Comment #{selectedComment}</h4>
              <Comment
                data={replies}
                onSubmit={handleSubmitReply}
                isReplyMode={true}
              />
            </div>
          )}
        </Modal>
      )}
    </div>
  );
};

export default HomePage;
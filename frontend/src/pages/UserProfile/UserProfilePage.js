import React, { useEffect, useState } from 'react';
import { useSelector } from 'react-redux';
import './UserProfilePage.css';
import Header from '../../components/Header/Header.js';
import ProfileCard from '../../components/ProfileCard/ProfileCard.js';
import FileUploader from '../../components/FileUploader/FileUploader.js';
import OtherUserList from '../../components/OtherUserList/OtherUserList.js';
import PostCard from '../../components/PostCard/PostCard.js';
import PhotoGrid from '../../components/PhotoGrid/PhotoGrid.js';
import Modal from '../../components/Modal/Modal.js'; 
import Comment from '../../components/Comment/Comment.js';
import Like from '../../components/Like/Like.js';
import useUser from '../../hooks/UseUser';
import useFollower from '../../hooks/UseFollower';
import useSubscriber from '../../hooks/UseSubscriber';
import usePosts from '../../hooks/UsePosts';
import useComment from '../../hooks/UseComment';
import useReply from '../../hooks/UseReply';
import useLike from '../../hooks/UseLike';

const UserProfilePage = () => {
  const userId = useSelector((state) => state.auth.userId); 
  const { userInfo, fetchUserInformation, uploadAvatar, loading, error } = useUser();
  const { followers, fetchFollowers, removeFollower } = useFollower(userId);//顯示傳參數因為要知道是誰的粉絲列表
  const { subscribers, fetchSubscribers,handleRemoveSubscriber, error: subscriberError } = useSubscriber(userId);
  const [isFileUploaderOpen, setIsFileUploaderOpen] = useState(false);
  const [isFollowerListOpen, setIsFollowerListOpen] = useState(false);
  const [isSubscriberListOpen, setIsSubscriberListOpen] = useState(false);

  // 貼文相關狀態
  const { posts, fetchPosts, error: postError } = usePosts({ userId });
  const [selectedPost, setSelectedPost] = useState(null); // 用於存儲選中的貼文
  const { comments, fetchComments, submitComment } = useComment(userId);
  const [selectedComment, setSelectedComment] = useState(null);//用於存儲選中的留言
  const { replies, fetchReplies, addReply } = useReply(userId);
  const [isModalOpen, setIsModalOpen] = useState(false); 
  const { likes,fetchLikes,isLiked,toggleLike } = useLike(userId);
  const [modalType, setModalType] = useState(null); // 'like', 'comment', 'reply' 或 null
  useEffect(() => {
    if (userId) {
      fetchUserInformation(userId);
      fetchPosts(userId);
    }
  }, [userId]); // 僅在 userId 變化時觸發

  // 點擊圖片時打開 Modal 並設置選中的貼文
  const handleImageClick = (post) => {
    setSelectedPost(post);
    setIsModalOpen(true);
  };

  const handleLikeClick = async (postId) => {
    try {
      await fetchLikes(postId); 
      setSelectedPost((prevPost) => ({
        ...prevPost,
        isLiked: likes.some((like) => Number(like.user_id) === Number(userId)), 
      }));
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
      await fetchComments(postId); // 使用 fetchComments 獲取評論資訊
      setSelectedPost((prevPost) => ({
        ...prevPost,
        comments, // 將評論資訊存入選中的貼文
      }));
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

  const handleAvatarClick = () => {
    setIsFileUploaderOpen(true);
  };

  const handleFileUpload = async (files) => {
    if (files.length > 0) {
      const file = files[0];
      const reader = new FileReader();
      reader.onloadend = async () => {
        const base64Image = reader.result.split(',')[1];
        await uploadAvatar(userId, base64Image);
        fetchUserInformation(userId);
        setIsFileUploaderOpen(false);
      };
      reader.readAsDataURL(file);
    }
  };

  const handleFollowersClick = async () => {
    await fetchFollowers();
    setIsFollowerListOpen(true);
  };

  const handleSubscribersClick = async () => {
    await fetchSubscribers();
    setIsSubscriberListOpen(true);
  };


  // 關閉 Modal
  const handleCloseModal = () => {
    setIsModalOpen(false);
    setModalType(null); // 重置彈窗類型
    setSelectedPost(null);
    setSelectedComment(null);
  };
  if (error || subscriberError) {
    return <div>Error: {error || subscriberError}</div>;
  }

  return (
    <div className="user-profile-page">
      <Header />
  
      {userInfo && (
        <ProfileCard
          name={userInfo.name}
          avatarSrc={userInfo.avatar_url}
          stats={{
            followers: userInfo.followerCount,
            subscriptions: userInfo.subscriberCount,
            posts: userInfo.postAmount || 0,
          }}
          onAvatarClick={handleAvatarClick}
          onFollowersClick={handleFollowersClick}
          onSubscriptionsClick={handleSubscribersClick}
          actions={null}
        />
      )}
      {isFileUploaderOpen && (
        <FileUploader onFileUpload={handleFileUpload} />
      )}
      {isFollowerListOpen && (
        <OtherUserList
          users={followers}
          mode="f"
          onActionClick={(followerId) => removeFollower(followerId)}
          onClose={() => setIsFollowerListOpen(false)} 
        />
      )}
      {isSubscriberListOpen && (
        <OtherUserList
          users={subscribers}
          mode="s"
          onActionClick={(subscriberId) => handleRemoveSubscriber(subscriberId)}
          onClose={() => setIsSubscriberListOpen(false)}
        />
      )}
      {/* 使用 PhotoGrid 組件展示 posts */}
      {posts && posts.length > 0 && (
        <PhotoGrid posts={posts} onImageClick={handleImageClick} />
      )}
      {isModalOpen && selectedPost && (
      <Modal isOpen={isModalOpen} onClose={handleCloseModal}>
        <PostCard
          posts={posts}
          onLike={() => handleLikeClick(selectedPost?.id)}
          onComment={() => handleCommentClick(selectedPost.id)}
        />
        {/* 只在點擊 like 按鈕時顯示 Like 組件 */}
        {modalType === 'like' && (
          <div className="likes-section">
            <h3>Likes</h3>
            <Like 
              data={likes} 
              onLike={() => handleToggleLike(selectedPost.id)} 
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
        
        {/* Replies section */}
        {selectedComment && modalType === 'reply'  && (
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

export default UserProfilePage;
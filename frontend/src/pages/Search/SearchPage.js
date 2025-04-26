import React, { useState, useEffect, use } from 'react';
import { useSelector } from 'react-redux';
import { useNavigate } from 'react-router-dom'; // 引入 useNavigate
import './SearchPage.css';
import Header from '../../components/Header/Header.js';
import SearchBar from '../../components/SearchBar/SearchBar';
import OtherUserList from '../../components/OtherUserList/OtherUserList';
import PhotoGrid from '../../components/PhotoGrid/PhotoGrid'; // 引入 PhotoGrid
import PostCard from '../../components/PostCard/PostCard'; // 添加引
import Modal from '../../components/Modal/Modal'; // 引入 Modal
import Comment from '../../components/Comment/Comment';
import Like from '../../components/Like/Like';
import useUser from '../../hooks/UseUser';
import usePosts from '../../hooks/UsePosts';
import useComment from '../../hooks/UseComment';
import useReply from '../../hooks/UseReply';
import useLike from '../../hooks/UseLike';

const SearchPage = () => { 
  const userId = useSelector((state) => state.auth.userId); 
  const navigate = useNavigate();
  const [searchType, setSearchType] = useState(null); // 搜索類型
  const [searchValue, setSearchValue] = useState(''); // 搜索值
  //搜尋用戶
  const { users, fetchUsersByName, loading: userLoading, error: userError } = useUser();


  // 搜尋貼文
  const { posts, fetchPosts, error: postError } = usePosts({ userId: null });
  const [selectedPost, setSelectedPost] = useState(null); // 用於存儲選中的貼文
  const { comments, fetchComments, submitComment } = useComment(userId);
  const [selectedComment, setSelectedComment] = useState(null);//用於存儲選中的留言
  const { replies, fetchReplies, addReply } = useReply(userId);
  const [isModalOpen, setIsModalOpen] = useState(false); 
  const { likes,fetchLikes,isLiked,toggleLike } = useLike(userId);
  const [modalType, setModalType] = useState(null); // 'like', 'comment', 'reply' 或 null

  // 處理搜索操作
  const handleSearch = async (value, type) => {
    setSearchValue(value);
    setSearchType(type);

    if (type === 'user') {
      await fetchUsersByName(value); // 搜尋用戶
    } else if (type === 'post') {
      await fetchPosts(null,null, value);
    }
  }
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

  const handleCloseModal = () => {
    setIsModalOpen(false);
    setModalType(null); // 重置彈窗類型
    setSelectedPost(null);
    setSelectedComment(null);
  };


  return (
    <div className="search-page">
      <Header title="Search Page" />
      <SearchBar onSearch={handleSearch} />

      {/* 渲染用戶搜索結果 */}
      {searchType === 'user' && (
        <div>
          {!userLoading && !userError && (
            <OtherUserList
              users={users}
              mode="d" // 使用 mode d
              onActionClick={(clickedUserId) => {
                if (Number(clickedUserId) === Number(userId)) {
                  navigate('/user-profile'); // 如果是本人，導航到 /user-profile
                } else {
                  navigate(`/user/${clickedUserId}`); // 否則導航到 /user/:id
                }
              }}
            />
          )}
        </div>
      )}
      {/* 渲染貼文搜索結果 */}
      {searchType === 'post' && posts && posts.length > 0 && (
        <PhotoGrid posts={posts} onImageClick={handleImageClick} />
      )}

      {/* Modal 顯示選中的貼文 */}
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

export default SearchPage;
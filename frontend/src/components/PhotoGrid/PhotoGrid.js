import React from 'react';
import PropTypes from 'prop-types';
import './PhotoGrid.css';

const PhotoGrid = ({ posts, onImageClick }) => {
  return (
    <div className="photo-grid">
      {posts.map((post) => (
        post.image_url && ( // 只顯示有圖片的貼文
          <div
            key={post.id}
            className="photo-grid-item"
            onClick={() => onImageClick && onImageClick(post)} // 點擊圖片時傳遞整個貼文資訊
          >
            <img src={post.image_url} alt={post.content || 'Post Image'} />
            <p className="photo-title">{post.user_name}</p>
          </div>
        )
      ))}
    </div>
  );
};

PhotoGrid.propTypes = {
  posts: PropTypes.arrayOf(
    PropTypes.shape({
      postId: PropTypes.string.isRequired,
      user_name: PropTypes.string.isRequired,
      content: PropTypes.string.isRequired,
      image_url: PropTypes.string, // 圖片 URL
    })
  ).isRequired,
  onImageClick: PropTypes.func, // 點擊圖片的回調函數
};

export default PhotoGrid;
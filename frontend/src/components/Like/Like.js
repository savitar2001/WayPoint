import React from 'react';
import PropTypes from 'prop-types';
import Avatar from '../Avatar/Avatar.js'
import './Like.css';

const Like = ({ data, onLike, isLiked }) => {
  return (
    <div className="like-container">
      {data.map((item) => (
        <div key={item.user_Id} className="like-item">
          <Avatar
            src={item.avatar_url}
            alt={`${item.name}'s avatar`}
            size="medium"
            shape="circle"
            />
          <span className="user-name">{item.name}</span>
        </div>
      ))}
      <button
        className="like-button"
        onClick={onLike} // 只觸發外部傳入的 onLike 方法
        aria-label="Like"
      >
        {isLiked ? '已經點讚了' : '點讚'}
      </button>
    </div>
  );
};
Like.propTypes = {
    data: PropTypes.arrayOf(
      PropTypes.shape({
        user_Id: PropTypes.oneOfType([PropTypes.string, PropTypes.number])
          .isRequired,
        avatar_url: PropTypes.string.isRequired,
        user_name: PropTypes.string.isRequired,
      })
    ),
    onLike: PropTypes.func.isRequired,
  };

export default Like;
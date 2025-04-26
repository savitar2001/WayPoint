import React, { useState } from 'react';
import PropTypes from 'prop-types';
import './PostCard.css';

const PostCard = ({posts, onLike, onComment}) => {
  return (
    <div className="post-list">
      {posts.map((post) => {
        const {
          id,
          user_Id,
          user_name,
          content,
          tag,
          image_url,
          comments_count,
          likes_count,
        } = post;

        const formattedTags = tag
          ? tag.split('/').map((tagItem) => `#${tagItem}`)
          : []
          return (
            <div key={id} className="card">
              {/* 上方區域 */}
              <div className="card-header">
                <div className="card-user-info">
                  <h4 className="card-user-name">{user_name}</h4>
                </div>
              </div>
  
              {/* 中間區域 */}
              <div className="card-body">
                {image_url && (
                  <img src={image_url} alt="Post" className="card-image" />
                )}
                <p className="card-content">{content}</p>
                <div className="card-tags">
                  {formattedTags.map((tagItem, index) => (
                    <span key={index} className="tag-item">
                      {tagItem}
                    </span>
                  ))}
                </div>
              </div>
  
              {/* 下方區域 */}
              <div className="card-footer">
                <div className="card-likes">
                  <button
                    onClick={() => onLike(id)}
                    className="like-button"
                  >
                    👍 Like
                  </button>
                  <span>{likes_count} Likes</span>
                </div>
                <div className="card-comments">
                  <button
                    onClick={() => onComment(id)}
                    className="comment-button"
                  >
                    💬 Comment
                  </button>
                  <span>{comments_count} Comments</span>
                </div>
              </div>
            </div>
          );
        })}
      </div>
    );
  };
PostCard.propTypes = {
  posts: PropTypes.arrayOf(
    PropTypes.shape({
      user_Id: PropTypes.string.isRequired,
      id: PropTypes.number.isRequired, // 使用 id，假設 ID 是數字
      user_name: PropTypes.string.isRequired,
      content: PropTypes.string.isRequired,
      tag: PropTypes.string,
      image_url: PropTypes.string,
      comments_count: PropTypes.number.isRequired,
      likes_count: PropTypes.number.isRequired,
    })
  ).isRequired,
  onLike: PropTypes.func.isRequired,
  onComment: PropTypes.func.isRequired,
};

export default PostCard;
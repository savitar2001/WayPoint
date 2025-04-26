import React, { useState } from 'react';
import PropTypes from 'prop-types';
import Avatar from '../Avatar/Avatar.js';
import './Comment.css';

const Comment = ({ data, onSubmit, onReplyClick, isReplyMode = false }) => {
  const [comment, setComment] = useState('');

  const handleSubmit = () => {
    if (comment.trim()) {
      onSubmit(comment); // 只傳出用戶輸入的留言內容
      setComment(''); // 清空輸入框
    }
  };

  return (
    <div className="comment-container">
      {/* 渲染評論或回覆列表 */}
      <div className="comment-list">
        {data.map((item) => (
          <div key={item.user_Id} className="comment-item">
            <Avatar
              src={item.avatar_url}
              alt={`${item.user_name}'s avatar`}
              size="medium"
              shape="circle"
            />
            <div className="comment-content">
              <div className="comment-header">
                <span className="comment-username">{item.name}</span>
                <span className="comment-id">ID: {item.user_Id}</span>
              </div>
              <p className="comment-text">{item.content}</p>
              {!isReplyMode && (
                <div className="comment-footer">
                  <button
                    className="comment-reply-button"
                    onClick={() => onReplyClick(item.id)}
                  >
                    回覆數: {item.reply_count}
                  </button>
                </div>
              )}
            </div>
          </div>
        ))}
      </div>

      {/* 用戶提交評論或回覆 */}
      <div className="comment-input-container">
        <textarea
          className="comment-input"
          value={comment}
          onChange={(e) => setComment(e.target.value)}
          placeholder={isReplyMode ? '輸入您的回覆...' : '輸入您的評論...'}
        />
        <button className="comment-submit" onClick={handleSubmit}>
          {isReplyMode ? '提交回覆' : '提交'}
        </button>
      </div>
    </div>
  );
};

Comment.propTypes = {
  data: PropTypes.arrayOf(
    PropTypes.shape({
      id: PropTypes.number.isRequired, // 留言 ID
      user_Id:PropTypes.number.isRequired,
      name: PropTypes.string.isRequired, // 用戶名稱
      avatar_url: PropTypes.string.isRequired, // 用戶頭像 URL
      content: PropTypes.string.isRequired, // 留言內容
      reply_count: PropTypes.number, // 回覆數量（僅在評論模式下需要）
    })
  ).isRequired,
  onSubmit: PropTypes.func.isRequired, // 提交評論或回覆的回調函數
  onReplyClick: PropTypes.func, // 點擊回覆按鈕的回調函數
  isReplyMode: PropTypes.bool, // 是否為回覆模式
};

export default Comment;
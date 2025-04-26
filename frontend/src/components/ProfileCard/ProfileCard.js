import React from 'react';
import PropTypes from 'prop-types';
import Avatar from '../Avatar/Avatar'; // 引入之前的 Avatar 組件
import './ProfileCard.css';

const ProfileCard = ({ name, avatarSrc, stats, onAvatarClick, onFollowersClick, onSubscriptionsClick, actions }) => {
  return (
    <div className="profile-card">
      {/* 頭像 */}
      <div className="profile-avatar">
        <Avatar
          src={avatarSrc}
          alt={name}
          size="large"
          onClick={onAvatarClick}
        />
      </div> 

      {/* 用戶名字 */}
      <h2 className="profile-name">{name}</h2>

      {/* 用戶數據 */}
      <div className="profile-stats">
        <div className="profile-stat">
          <button className="stat-button" onClick={onFollowersClick}>
            <span className="stat-value">{stats.followers}</span>
            <span className="stat-label">追蹤數</span>
          </button>
        </div>
        <div className="profile-stat">
          <button className="stat-button" onClick={onSubscriptionsClick}>
            <span className="stat-value">{stats.subscriptions}</span>
            <span className="stat-label">訂閱數</span>
          </button>
        </div>
        <div className="profile-stat">
          <span className="stat-value">{stats.posts}</span>
          <span className="stat-label">發文數</span>
        </div>
      </div>

      {/* 操作按鈕 */}
      {actions && <div className="profile-actions">{actions}</div>}
    </div>
  );
};

ProfileCard.propTypes = {
  name: PropTypes.string.isRequired, // 用戶名字
  avatarSrc: PropTypes.string.isRequired, // 頭像 URL
  stats: PropTypes.shape({
    followers: PropTypes.number.isRequired, // 追蹤數
    subscriptions: PropTypes.number.isRequired, // 訂閱數
    posts: PropTypes.number.isRequired, // 發文數
  }).isRequired,
  onAvatarClick: PropTypes.func, // 點擊頭像的回調函數
  onFollowersClick: PropTypes.func.isRequired, // 點擊追蹤數的回調函數
  onSubscriptionsClick: PropTypes.func.isRequired, // 點擊訂閱數的回調函數
  actions: PropTypes.node, // 額外的操作按鈕（可選）
};

export default ProfileCard;
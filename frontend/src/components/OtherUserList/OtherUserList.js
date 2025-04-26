import React from 'react';
import PropTypes from 'prop-types';
import List from '../List/List';
import Avatar from '../Avatar/Avatar';
import './OtherUserList.css';

const OtherUserList = ({ users, mode, onActionClick,onClose }) => {
  const renderUserItem = (user) => (
    <div className="user-item" key={user.id}>
      <Avatar src={user.avatar_url} alt={user.name} size="small" />
      <span className="user-name">{user.name}</span>
      <button
        className="action-button"
        onClick={() => onActionClick(user.id)}
      >
        {mode === 's' ? '取消追蹤' : mode === 'f' ? '移除粉絲' : '去用戶首頁'}
      </button>
    </div>
  );

  return (
    <div className="other-user-list">
      <List
        items={users}
        renderItem={renderUserItem}
        emptyMessage="目前沒有用戶"
      />
      <button className="close-button" onClick={onClose}>
        &times;
      </button>
    </div>
  );
};
 
OtherUserList.propTypes = {
  users: PropTypes.arrayOf(
    PropTypes.shape({
      id: PropTypes.oneOfType([PropTypes.string, PropTypes.number]).isRequired,
      avatar_url: PropTypes.string, // 頭像 URL
      name: PropTypes.string.isRequired, // 用戶名稱
    })
  ).isRequired, // 用戶列表數據
  mode: PropTypes.oneOf(['s', 'f', 'd']).isRequired, // 模式：s（訂閱者）、f（粉絲）、d（去用戶首頁）
  onActionClick: PropTypes.func.isRequired, // 按鈕點擊事件回調
  onClose: PropTypes.func.isRequired,
};

export default OtherUserList;
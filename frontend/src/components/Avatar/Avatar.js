import React from 'react';
import PropTypes from 'prop-types';
import './Avatar.css';

const Avatar = ({ src, alt, size = 'medium', shape = 'circle', status, onClick }) => {
  return (
    <div
      className={`avatar avatar-${size} avatar-${shape}`}
      onClick={onClick}
      style={{ cursor: onClick ? 'pointer' : 'default' }}
    >
      {src ? (
        <img src={src} alt={alt || 'Avatar'} />
      ) : (
        <div className="avatar-placeholder">{alt ? alt[0].toUpperCase() : '?'}</div>
      )}
      {status && <span className={`avatar-status avatar-status-${status}`}></span>}
    </div>
  );
};

Avatar.propTypes = {
  src: PropTypes.string, // 頭像圖片的 URL
  alt: PropTypes.string, // 替代文字或用戶名稱
  size: PropTypes.oneOf(['small', 'medium', 'large']), // 頭像尺寸
  shape: PropTypes.oneOf(['circle', 'square']), // 頭像形狀
  status: PropTypes.oneOf(['online', 'offline', 'busy']), // 用戶狀態
  onClick: PropTypes.func, // 點擊事件回調
};

export default Avatar;
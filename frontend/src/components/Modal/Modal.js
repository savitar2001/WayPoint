import React from 'react';
import PropTypes from 'prop-types';
import Button from '../Button/Button.js'; // 引入之前的 Button 組件
import './Modal.css';

const Modal = ({ isOpen, title, children, onClose, size = 'medium' }) => {
  if (!isOpen) return null;

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div
        className={`modal modal-${size}`}
        onClick={(e) => e.stopPropagation()} // 阻止冒泡，防止点击内容区域关闭
      >
        <div className="modal-header">
          <h2>{title}</h2>
          {/* 使用自定義的 Button 組件 */}
          <Button
            variant="link"
            size="small"
            onClick={onClose}
            className="modal-close"
          >
            ×
          </Button>
        </div>
        <div className="modal-body">{children}</div>
      </div>
    </div>
  );
};

Modal.propTypes = {
  isOpen: PropTypes.bool.isRequired, // 控制模态框是否打开
  title: PropTypes.string, // 模态框标题
  children: PropTypes.node, // 模态框内容
  onClose: PropTypes.func.isRequired, // 关闭模态框的回调
  size: PropTypes.oneOf(['small', 'medium', 'large']), // 模态框大小
};

export default Modal;
import React from 'react';
import PropTypes from 'prop-types';
import './Button.css'; // 样式文件

const Button = ({
  children,
  type = 'button',
  variant = 'primary',
  size = 'medium',
  disabled = false,
  loading = false,
  icon,
  onClick,
  className = '',
  ...props
}) => {
  return (
    <button
      type={type}
      className={`btn btn-${variant} btn-${size} ${className}`}
      disabled={disabled || loading}
      onClick={onClick}
      {...props}
    >
      {loading ? (
        <span className="btn-spinner"></span> // 加载动画
      ) : (
        <>
          {icon && <span className="btn-icon">{icon}</span>}
          {children}
        </>
      )}
    </button>
  );
};

Button.propTypes = {
  children: PropTypes.node.isRequired, // 按钮文本或内容
  type: PropTypes.oneOf(['button', 'submit', 'reset']),
  variant: PropTypes.oneOf(['primary', 'secondary', 'danger', 'link']),
  size: PropTypes.oneOf(['small', 'medium', 'large']),
  disabled: PropTypes.bool,
  loading: PropTypes.bool,
  icon: PropTypes.node, // 图标
  onClick: PropTypes.func,
  className: PropTypes.string,
};

export default Button;
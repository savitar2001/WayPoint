import React, { useState } from 'react';
import { useSearchParams } from 'react-router-dom'; // 用於提取 URL 參數
import Header from '../../components/Header/Header.js';
import InputField from '../../components/InputField/InputField.js'; 
import { passwordResetVerify } from '../../services/AuthService'; // 引入 API 方法
import './ResetPasswordPage.css';

const ResetPassword = () => {
  const [searchParams] = useSearchParams(); // 提取 URL 參數
  const [password, setPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  // 從 URL 中提取參數
  const requestId = searchParams.get('id');
  const hash = searchParams.get('hash');
  const userId = searchParams.get('user');

  const handleButtonClick = async (e) => {
    e.preventDefault();

    // 簡單驗證密碼是否一致
    if (password !== confirmPassword) {
      setError('Passwords do not match!');
      return;
    }

    try {
      // 調用 API 傳遞參數
      const response = await passwordResetVerify(requestId, hash, userId, password, confirmPassword);
      if (response.data.success) {
        setSuccess('Password reset successful!');
        setError('');
      } else {
        setError(response.data.error || 'Password reset failed.');
      }
    } catch (err) {
      setError('An error occurred while resetting the password.');
    }
  };

  return (
    <div className="reset-password-page">
      <Header title="Reset Your Password" />
      <InputField
        formName="輸入重新設定的密碼"
        fields={[
          {
            label: 'Password 密碼必須至少包含 8 個字元，且包含大小寫字母、數字及特殊符號（如 ~?!@#$%^&*）',
            placeholder: 'Enter your password',
            onChange: (e) => setPassword(e.target.value),
          },
          {
            label: 'Password Confirmation',
            placeholder: 'Enter your password again',
            onChange: (e) => setConfirmPassword(e.target.value),
          },
        ]}
        onButtonClick={handleButtonClick} // 傳遞按鈕點擊事件
      />
      {error && <p className="error-message">{error}</p>}
      {success && <p className="success-message">{success}</p>}
    </div>
  );
};

export default ResetPassword;
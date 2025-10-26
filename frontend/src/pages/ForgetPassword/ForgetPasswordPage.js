import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import Header from '../../components/Header/Header.js';
import InputField from '../../components/InputField/InputField.js'; 
import { passwordReset } from '../../services/AuthService'; // 引入 AuthService
import './ForgetPasswordPage.css';

const ForgetPassword = () => {
  const [email, setEmail] = useState(''); // 用於儲存用戶輸入的 email
  const navigate = useNavigate();

  const handleButtonClick = async (e) => {
    e.preventDefault();
    try {
      const response = await passwordReset(email);
      // 修正：response 是 axios response 對象，數據在 response.data 中
      if (response.data && response.data.success) {
        alert('請檢查您的電子郵件以完成密碼重設流程');
        navigate('/');
      } else {
        // 處理後端返回的錯誤（HTTP 200 但 success: false）
        alert(response.data?.error || '發生未知錯誤');
      }
    } catch (error) {
      console.error('Error during password reset:', error);
      // 處理 HTTP 錯誤狀態（400, 500 等）
      alert(error.response?.data?.error || error.response?.data?.message || '無法發送密碼重設請求，請稍後再試');
    }
  };

  return (
    <div className="forget-password-page">
      <Header title="reset your password request" />
      {/* 帳號表單 */}
      <InputField
        formName="輸入接收重設密碼信件的帳號"
        fields={[
          {
            label: 'Email',
            placeholder: 'Enter your email',
            value: email,
            onChange: (e) => setEmail(e.target.value),
          },
        ]}
        onButtonClick={handleButtonClick}
      />
    </div>
  );
};

export default ForgetPassword;
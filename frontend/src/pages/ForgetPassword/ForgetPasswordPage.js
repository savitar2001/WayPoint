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
      const response = await passwordReset(email); // 呼叫 AuthService 的 passwordReset
      if (response.data.success) {
        alert('請檢查您的電子郵件以完成密碼重設流程');
        navigate('/'); // 成功後導向首頁或其他頁面
      } else {
        alert(response.data.error || '發生未知錯誤');
      }
    } catch (error) {
      console.error('Error during password reset:', error);
      alert('無法發送密碼重設請求，請稍後再試');
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
            onChange: (e) => setEmail(e.target.value), // 更新 email 狀態
          },
        ]}
        onButtonClick={handleButtonClick} // 傳遞按鈕點擊事件
      />
    </div>
  );
};

export default ForgetPassword;
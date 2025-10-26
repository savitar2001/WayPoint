import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import './RegisterPage.css';
import { register } from '../../services/AuthService';
import Header from '../../components/Header/Header.js';
import InputField from '../../components/InputField/InputField.js'; 


const RegisterPage = () => {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    password: '',
    confirm_password: '', 
  });
  const [error, setError] = useState('');
  const [successMessage, setSuccessMessage] = useState(''); 
  const navigate = useNavigate();

  const handleInputChange = (field, value) => {
    setFormData({ ...formData, [field]: value });
  };

  const handleButtonClick = async () => {
    try {
      setError('');
      setSuccessMessage('');
      
      const response = await register(
        formData.name,
        formData.email,
        formData.password,
        formData.confirm_password
      );

      // 修正：response 是 axios response 對象，實際數據在 response.data 中
      if (response.data && response.data.success === true) { 
        setSuccessMessage('請至信箱完成帳號驗證');
        setTimeout(() => {
          navigate('/welcome');
        }, 3000); 
      } else {
        // 處理後端返回的錯誤（HTTP 200 但 success: false）
        setError(response.data?.error || '註冊失敗，請稍後再試');
      }
    } catch (err) {
      // 處理 HTTP 錯誤狀態（400, 500 等）
      setError(err.response?.data?.error || err.response?.data?.message || '發生未預期的錯誤，請稍後再試');
    }
  };

  return (
    <div className="register-page">
      <Header title="Register New Account" />
      {/*註冊表單*/}
      <InputField
        formName="Register"
        fields={[
            { label: 'Name 名字必須為 1 到 255 個字元，且只能包含字母、數字、空格、底線或連字符', 
              placeholder: 'Enter your name',
              value: formData.name,
              onChange: (value) => handleInputChange('name', value),
             },
            { label: 'Email Email 必須為有效的電子郵件地址，且長度不超過 255 個字元', 
              placeholder: 'Enter your email',
              value: formData.email,
              onChange: (value) => handleInputChange('email', value),
             },
            { label: 'Password 密碼必須至少包含 8 個字元，且包含大小寫字母、數字及特殊符號（如 ~?!@#$%^&*)', 
              placeholder: 'Enter your password',
              value: formData.password,
              onChange: (value) => handleInputChange('password', value),
             },
            { label: 'Password Confirmation', 
              placeholder: 'Enter your password again',
              value: formData.confirm_password,
              onChange: (value) => handleInputChange('confirm_password', value), },
          ]}
        onButtonClick={handleButtonClick}
      />
      {successMessage && <div className="success-message">{successMessage}</div>}
      {error && <div className="error-message">{error}</div>}
    </div>
  );
};

export default RegisterPage;
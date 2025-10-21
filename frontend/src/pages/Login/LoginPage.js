import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useDispatch } from 'react-redux'; 
import { login as loginAction } from '../../redux/authSlice';
import { login } from '../../services/AuthService';
import Header from '../../components/Header/Header.js';
import Button from '../../components/Button/Button';
import InputField from '../../components/InputField/InputField.js'; 
import './LoginPage.css'; 

const LoginPage = () => {
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const [error, setError] = useState('');
  const [email, setEmail] = useState(''); 
  const [password, setPassword] = useState('');

  const handleButtonClick  = async () => {
    try {
      const response = await login(email, password, dispatch);
      if (response['success'] === true) {
        // 後端返回: response.data = { access_token, token_type, expires_in, user }
        // token 已由 AuthService.login() 存儲到 sessionStorage
        const user = response['data']['user'];
        
        dispatch(loginAction({ 
          userId: user.id, 
          userName: user.name 
        }));
        
        navigate('/home');
      } else {
        // 顯示錯誤和剩餘嘗試次數
        const errorMsg = response['error'] || 'Login failed';
        const remainingAttempts = response['remaining_attempts'];
        
        if (remainingAttempts !== undefined) {
          setError(`${errorMsg}。剩餘嘗試次數：${remainingAttempts}`);
        } else {
          setError(errorMsg);
        }
      }
      
    } catch (err) {
      console.error('Login error:', err);
      setError('登入時發生錯誤，請稍後再試');
    }
  };

  const handleForgotPassword = () => {
    navigate('/forget-password');
  };

  const handleRegister = () => {
    navigate('/register');
  };

  return (
    <div className="login-page">
      <Header title="Login to Your Account" />
       {/* 登入表單 */}
       <InputField
        formName="登入"
        fields={[
          { label: 'Email', placeholder: 'Enter your email',value: email,onChange: (e) => setEmail(e.target.value)},
          { label: 'Password', placeholder: 'Enter your password',value: password,onChange: (e) => setPassword(e.target.value)}
        ]}
        onButtonClick={handleButtonClick} // 傳遞按鈕點擊事件
      />
      {error && <div className="error-message">{error}</div>}
      <div className="login-footer">
        <Button
          type="button"
          variant="link"
          className="forgot-password-button"
          onClick={handleForgotPassword}
        >
          Forgot Password?
        </Button>
        <Button
          type="button"
          variant="link"
          className="register-button"
          onClick={handleRegister}
        >
          Register
        </Button>
      </div> 
    </div>
  );
};
export default LoginPage;
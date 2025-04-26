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
      const response = await login(email, password);
      if (response['success'] === true) {
        const {userId, userName} = response['data'];
        dispatch(loginAction({ userId, userName }));
        navigate('/home');
      } else {
        setError(response['error']);
      }
      
    } catch (err) {
      setError('Invalid email or password. Please try again.');
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
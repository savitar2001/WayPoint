import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import store from './redux/store.js'; // 引入 Redux Store
import WelcomePage from './pages/WelcomePage.js';
import { Provider } from 'react-redux';
import ForgetPasswordPage from './pages/ForgetPassword/ForgetPasswordPage.js'; 
import ResetPasswordPage from './pages/ForgetPassword/ResetPasswordPage.js';
import HomePage from './pages/Home/HomePage.js';
import LoginPage from './pages/Login/LoginPage.js';
import RegisterPage from './pages/Register/RegisterPage.js';
import ValidationEmailPage from './pages/Register/ValidationEmailPage.js';
import SearchPage from './pages/Search/SearchPage.js';
import CreatePostPage from './pages/UserProfile/CreatePostPage.js';
import OtherUserProfilePage from './pages/UserProfile/OtherUserProfilePage.js';
import UserProfilePage from './pages/UserProfile/UserProfilePage.js';

const App = () => {
  return (
    <Provider store={store}>
      <Router>
        <Routes>
          <Route path="/" element={<WelcomePage />} />
          <Route path="/login" element={<LoginPage />} />
          <Route path="/register" element={<RegisterPage />} />
          <Route path="/forget-password" element={<ForgetPasswordPage />} />
          <Route path="/ResetPassword" element={<ResetPasswordPage />} />
          <Route path="/home" element={<HomePage />} />
          <Route path="/user-profile" element={<UserProfilePage />} />
          <Route path="/user/:id" element={<OtherUserProfilePage />} /> {/* 新增路由 */}
          <Route path="/ValidationEmail" element={<ValidationEmailPage />} />
          <Route path="/search" element={<SearchPage />} />
          <Route path="/create-post" element={<CreatePostPage />} />
        </Routes>
      </Router>
    </Provider>
  );
};

export default App;

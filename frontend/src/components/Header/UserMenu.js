import React, { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useDispatch, useSelector } from 'react-redux'; 
import { login, logout } from '../../redux/authSlice'; 
import './UserMenu.css';

const UserMenu = () => {
  const [menuOpen, setMenuOpen] = useState(false); // 控制下拉菜單的顯示
  const navigate = useNavigate();
  const dispatch = useDispatch();
  const isLoggedIn = useSelector((state) => state.auth.isLoggedIn); // 從 Redux 獲取登入狀態

  const handleLogout = () => {
    dispatch(logout()); // 發送登出操作
    navigate('/'); // 導航到首頁
  };

  const handleProfile = () => {
    navigate('/user-profile'); // 導航到個人資訊頁面
  };

  const toggleMenu = () => {
    setMenuOpen(!menuOpen); // 切換下拉菜單的顯示狀態
  };

  return (
    <div className="user-menu">
      {isLoggedIn ? (
        <div className="user-menu__logged-in">
          <img
            src="/path/to/avatar.jpg"
            alt="User Avatar"
            className="user-avatar"
            onClick={toggleMenu} // 點擊頭像切換下拉菜單
          />
          {menuOpen && (
            <div className="user-menu__dropdown">
              <button className="dropdown-item" onClick={handleProfile}>
                View Profile
              </button>
              <button className="dropdown-item" onClick={handleLogout}>
                Logout
              </button>
            </div>
          )}
        </div>
      ) : (
        <button className="login-button" onClick={() => navigate('/login')}>
          Login
        </button>
      )}
    </div>
  );
};

export default UserMenu;
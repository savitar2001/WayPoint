import React, { useState }from 'react';
import './Header.css';
import Logo from './Logo';
import UserMenu from './UserMenu';
import { useSelector } from 'react-redux'; 
import { Link } from 'react-router-dom'; 
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faHome, faSearch, faPen } from '@fortawesome/free-solid-svg-icons'; 

const Header = () => {
  const isLoggedIn = useSelector((state) => state.auth.isLoggedIn);

  return (
    <header className="header">
      {/* Logo 部分 */}
      <div className="header__logo">
        <Logo />
      </div>
      <div className="header__user-menu">
        {isLoggedIn && (
          <>
            <Link to="/search" className="header__button">
              <FontAwesomeIcon icon={faSearch} /> {/* 放大鏡圖標 */}
            </Link>
            <Link to="/home" className="header__button">
              <FontAwesomeIcon icon={faHome} /> {/* 房子圖標 */}
            </Link>
            <Link to="/create-post" className="header__button">
              <FontAwesomeIcon icon={faPen} /> {/* 筆圖標 */}
            </Link>
          </>
        )}
        <UserMenu isLoggedIn={isLoggedIn} />
      </div>
    </header>
  );
};

export default Header;
import React, { useState } from 'react';
import PropTypes from 'prop-types';
import './SearchBar.css';

const SearchBar = ({ onSearch }) => {
  const [searchType, setSearchType] = useState('user'); // 搜索類型：'user' 或 'post'
  const [searchValue, setSearchValue] = useState(''); // 搜索框的值

  // 切換搜索類型
  const toggleSearchType = () => {
    setSearchType((prevType) => (prevType === 'user' ? 'post' : 'user'));
  };

  // 處理搜索操作
  const handleSearch = () => {
    if (searchValue.trim() === '') return; // 防止空搜索
    onSearch(searchValue, searchType); // 傳遞搜索值和類型
  };

  return (
    <div className="search-bar">
      {/* 搜索框 */}
      <input
        type="text"
        className="search-input"
        placeholder={
          searchType === 'user' ? '請輸入用戶名稱' : '請輸入標籤'
        }
        value={searchValue}
        onChange={(e) => setSearchValue(e.target.value)}
        onKeyDown={(e) => e.key === 'Enter' && handleSearch()} // 按下回車鍵觸發搜索
      />

      {/* 切換按鈕 */}
      <button
        className={`toggle-button ${searchType}`}
        onClick={toggleSearchType}
      >
        🔄
      </button>

      {/* 搜索按鈕 */}
      <button className="search-button" onClick={handleSearch}>
        搜索
      </button>
    </div>
  );
};

SearchBar.propTypes = {
  onSearch: PropTypes.func.isRequired, // 搜索回調函數
};

export default SearchBar;
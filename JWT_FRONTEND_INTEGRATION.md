# JWT 前端整合指南

## 📦 安裝依賴

```bash
# 如果使用 Redux
npm install @reduxjs/toolkit react-redux

# 如果還沒安裝 axios
npm install axios
```

## 🔧 配置 Axios 攔截器

創建 `src/services/api.js` 或 `src/utils/axios.js`：

```javascript
import axios from 'axios';

// 創建 axios 實例
const api = axios.create({
  baseURL: process.env.REACT_APP_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  }
});

// 請求攔截器 - 自動添加 JWT token
api.interceptors.request.use(
  (config) => {
    const token = sessionStorage.getItem('access_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// 響應攔截器 - 處理 token 過期
api.interceptors.response.use(
  (response) => {
    return response;
  },
  async (error) => {
    const originalRequest = error.config;

    // Token 過期 (401)
    if (error.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;

      try {
        // 嘗試刷新 token
        const refreshToken = sessionStorage.getItem('refresh_token');
        if (refreshToken) {
          const response = await axios.post(
            `${process.env.REACT_APP_API_URL}/refresh`,
            {},
            {
              headers: {
                Authorization: `Bearer ${sessionStorage.getItem('access_token')}`
              }
            }
          );

          const { access_token } = response.data;
          sessionStorage.setItem('access_token', access_token);

          // 重試原始請求
          originalRequest.headers.Authorization = `Bearer ${access_token}`;
          return api(originalRequest);
        }
      } catch (refreshError) {
        // 刷新失敗，清除 token 並跳轉登入頁
        sessionStorage.removeItem('access_token');
        sessionStorage.removeItem('refresh_token');
        sessionStorage.removeItem('user');
        window.location.href = '/login';
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  }
);

export default api;
```

## 🔐 認證 Service

創建 `src/services/authService.js`：

```javascript
import api from './api';

const authService = {
  // 登入
  async login(email, password) {
    try {
      const response = await api.post('/login', { email, password });
      
      if (response.data.success) {
        const { access_token, user, expires_in } = response.data.data;
        
        // 存儲 token 和用戶信息
        sessionStorage.setItem('access_token', access_token);
        sessionStorage.setItem('user', JSON.stringify(user));
        sessionStorage.setItem('token_expires_at', Date.now() + expires_in * 1000);
        
        return response.data;
      }
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // 登出
  async logout() {
    try {
      await api.post('/logout');
    } catch (error) {
      console.error('Logout error:', error);
    } finally {
      // 無論是否成功，都清除本地數據
      sessionStorage.removeItem('access_token');
      sessionStorage.removeItem('user');
      sessionStorage.removeItem('token_expires_at');
      window.location.href = '/login';
    }
  },

  // 註冊
  async register(userData) {
    try {
      const response = await api.post('/register', userData);
      return response.data;
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // 獲取當前用戶
  async getCurrentUser() {
    try {
      const response = await api.get('/me');
      if (response.data.success) {
        sessionStorage.setItem('user', JSON.stringify(response.data.data));
        return response.data.data;
      }
    } catch (error) {
      throw error.response?.data || error;
    }
  },

  // 檢查是否已登入
  isAuthenticated() {
    const token = sessionStorage.getItem('access_token');
    const expiresAt = sessionStorage.getItem('token_expires_at');
    
    if (!token || !expiresAt) {
      return false;
    }
    
    // 檢查 token 是否過期
    if (Date.now() >= parseInt(expiresAt)) {
      this.logout();
      return false;
    }
    
    return true;
  },

  // 獲取存儲的用戶信息
  getUser() {
    const userStr = sessionStorage.getItem('user');
    return userStr ? JSON.parse(userStr) : null;
  }
};

export default authService;
```

## 🎯 使用範例

### 1. 登入頁面組件

```javascript
import React, { useState } from 'react';
import authService from '../services/authService';
import { useNavigate } from 'react-router-dom';

function Login() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);
  const navigate = useNavigate();

  const handleLogin = async (e) => {
    e.preventDefault();
    setError('');
    setLoading(true);

    try {
      const response = await authService.login(email, password);
      
      if (response.success) {
        // 登入成功，跳轉到首頁
        navigate('/');
      }
    } catch (error) {
      setError(error.error || '登入失敗');
      
      // 顯示剩餘嘗試次數
      if (error.remaining_attempts !== undefined) {
        setError(`密碼錯誤，剩餘嘗試次數：${error.remaining_attempts}`);
      }
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="login-container">
      <h2>登入</h2>
      <form onSubmit={handleLogin}>
        <input
          type="email"
          placeholder="Email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
        />
        <input
          type="password"
          placeholder="Password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
        />
        {error && <div className="error">{error}</div>}
        <button type="submit" disabled={loading}>
          {loading ? '登入中...' : '登入'}
        </button>
      </form>
    </div>
  );
}

export default Login;
```

### 2. 受保護的路由組件

創建 `src/components/ProtectedRoute.js`：

```javascript
import React from 'react';
import { Navigate } from 'react-router-dom';
import authService from '../services/authService';

function ProtectedRoute({ children }) {
  if (!authService.isAuthenticated()) {
    // 未登入，重定向到登入頁
    return <Navigate to="/login" replace />;
  }

  return children;
}

export default ProtectedRoute;
```

在 `App.js` 中使用：

```javascript
import { BrowserRouter, Routes, Route } from 'react-router-dom';
import ProtectedRoute from './components/ProtectedRoute';
import Login from './pages/Login';
import Home from './pages/Home';
import Profile from './pages/Profile';

function App() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<Login />} />
        
        {/* 需要認證的路由 */}
        <Route 
          path="/" 
          element={
            <ProtectedRoute>
              <Home />
            </ProtectedRoute>
          } 
        />
        <Route 
          path="/profile" 
          element={
            <ProtectedRoute>
              <Profile />
            </ProtectedRoute>
          } 
        />
      </Routes>
    </BrowserRouter>
  );
}

export default App;
```

### 3. 在組件中使用 API

```javascript
import React, { useEffect, useState } from 'react';
import api from '../services/api';

function PostList() {
  const [posts, setPosts] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchPosts();
  }, []);

  const fetchPosts = async () => {
    try {
      // 自動帶上 JWT token
      const response = await api.get('/getPost/1/1/all');
      if (response.data.success) {
        setPosts(response.data.data);
      }
    } catch (error) {
      console.error('Failed to fetch posts:', error);
    } finally {
      setLoading(false);
    }
  };

  const createPost = async (content) => {
    try {
      const response = await api.post('/createPost', { content });
      if (response.data.success) {
        fetchPosts(); // 重新載入貼文列表
      }
    } catch (error) {
      console.error('Failed to create post:', error);
    }
  };

  if (loading) return <div>Loading...</div>;

  return (
    <div>
      {posts.map(post => (
        <div key={post.id}>{post.content}</div>
      ))}
    </div>
  );
}

export default PostList;
```

### 4. 登出功能

```javascript
import React from 'react';
import authService from '../services/authService';

function Navbar() {
  const user = authService.getUser();

  const handleLogout = async () => {
    if (window.confirm('確定要登出嗎？')) {
      await authService.logout();
    }
  };

  return (
    <nav>
      <div>歡迎，{user?.name}</div>
      <button onClick={handleLogout}>登出</button>
    </nav>
  );
}

export default Navbar;
```

## 🔒 安全性建議

### Token 存儲選擇

1. **sessionStorage（推薦）**
   - ✅ 關閉瀏覽器即清除
   - ✅ 相對安全
   - ❌ 頁面刷新不會丟失

2. **localStorage**
   - ✅ 持久化存儲
   - ❌ 易受 XSS 攻擊
   - 不推薦用於敏感數據

3. **內存（最安全但不便）**
   - ✅ 最安全
   - ❌ 頁面刷新會丟失
   - 需要配合 refresh token

## 📝 環境變量

創建 `.env` 文件：

```bash
# 開發環境
REACT_APP_API_URL=http://localhost:8000/api

# 生產環境
# REACT_APP_API_URL=https://your-backend-domain.com/api
```

## ⚠️ 常見問題

### 1. 419 CSRF Token Mismatch
使用 JWT 後不應再出現此問題，因為 JWT 不依賴 CSRF token。

### 2. 401 Unauthorized
- 檢查 token 是否正確存儲
- 檢查 Authorization header 格式：`Bearer {token}`
- 檢查 token 是否過期

### 3. CORS 錯誤
確保後端 `config/cors.php` 已正確配置：
- `allowed_headers` 包含 `Authorization`
- `allowed_origins` 包含前端域名

## 🎉 完成！

現在你的前端已經完整整合 JWT 認證系統：
- ✅ 自動在請求中添加 token
- ✅ 自動處理 token 過期
- ✅ 受保護的路由
- ✅ 安全的登入/登出流程

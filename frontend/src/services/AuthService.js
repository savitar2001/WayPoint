import axios from 'axios';
import { initializeEcho as initEcho, disconnectEcho as discEcho, getEcho } from './echo'; 

const API_BASE_URL = `${process.env.REACT_APP_BACKEND_URL}/api`;

// Debug: 檢查環境變數
console.log('REACT_APP_BACKEND_URL:', process.env.REACT_APP_BACKEND_URL);
console.log('API_BASE_URL:', API_BASE_URL);

// ==================== JWT 配置 ====================

// 配置 axios 默認值
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['Content-Type'] = 'application/json';

// JWT Token 管理
const TOKEN_KEY = 'access_token';
const USER_KEY = 'user';

// 獲取存儲的 token
export const getToken = () => {
  return sessionStorage.getItem(TOKEN_KEY);
};

// 存儲 token
export const setToken = (token) => {
  sessionStorage.setItem(TOKEN_KEY, token);
};

// 移除 token
export const removeToken = () => {
  sessionStorage.removeItem(TOKEN_KEY);
  sessionStorage.removeItem(USER_KEY);
};

// 獲取存儲的用戶信息
export const getUser = () => {
  const userStr = sessionStorage.getItem(USER_KEY);
  return userStr ? JSON.parse(userStr) : null;
};

// 存儲用戶信息
export const setUser = (user) => {
  sessionStorage.setItem(USER_KEY, JSON.stringify(user));
};

// 請求攔截器 - 自動添加 JWT token
axios.interceptors.request.use(
  (config) => {
    const token = getToken();
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// 響應攔截器 - 處理 token 過期和錯誤
axios.interceptors.response.use(
  (response) => response,
  async (error) => {
    const originalRequest = error?.config || {};

    // Token 過期 (401) 處理
    if (error?.response?.status === 401 && !originalRequest._retry) {
      originalRequest._retry = true;

      try {
        // 嘗試刷新 token
        const refreshResponse = await axios.post(`${API_BASE_URL}/refresh`, {}, {
          headers: {
            Authorization: `Bearer ${getToken()}`
          }
        });

        if (refreshResponse.data.success && refreshResponse.data.access_token) {
          // 更新 token
          setToken(refreshResponse.data.access_token);
          
          // 重試原始請求
          originalRequest.headers.Authorization = `Bearer ${refreshResponse.data.access_token}`;
          return axios(originalRequest);
        }
      } catch (refreshError) {
        // 刷新失敗，清除登入狀態
        console.error('Token 刷新失敗，請重新登入', refreshError);
        removeToken();
        // 可以在這裡觸發重定向到登入頁
        window.location.href = '/login';
        return Promise.reject(refreshError);
      }
    }

    return Promise.reject(error);
  }
);

// ==================== 認證 API ====================

// 初始化（不再需要 CSRF，但保留函數以向後兼容）
export const initializeCsrfToken = async () => {
  console.log('JWT 模式：無需初始化 CSRF token');
  // 不需要做任何事情
};


// 登入 - 使用 JWT
export const login = async (email, password, dispatch) => {
  const response = await axios.post(`${API_BASE_URL}/login`, { email, password });
  
  if (response?.data?.success && response?.data?.data) {
    const { access_token, user } = response.data.data;
    
    // 存儲 token 和用戶信息
    setToken(access_token);
    setUser(user);
    
    // 初始化 Echo（WebSocket）使用 JWT
    if (user?.id) {
      initEcho(user.id, dispatch);
    }
  }
  
  return response.data;
};

// 註冊用戶
export const register = async (name, email, password, confirm_password) => {
  const response = await axios.post(`${API_BASE_URL}/register`, {
    name,
    email,
    password,
    confirm_password
  });
  return response;
};

// 帳號驗證
export const verifyAccount = async (requestId, hash, userId) => {
  const response = await axios.post(`${API_BASE_URL}/verify`, { requestId, hash, userId });
  return response;
};

// 登出 - 使用 JWT
export const logout = async () => {
  try {
    // 呼叫後端登出 API（將 token 加入黑名單）
    await axios.post(`${API_BASE_URL}/logout`);
  } catch (error) {
    console.error('登出 API 錯誤:', error);
  } finally {
    // 無論 API 成功與否，都清除本地數據
    removeToken();
    
    // 斷開 WebSocket
    if (getEcho()) {
      discEcho();
    }
  }
};

// 刪除帳戶 - 使用 JWT
export const deleteAccount = async () => {
  const response = await axios.delete(`${API_BASE_URL}/deleteAccount`);
  // 刪除成功後清除本地數據
  removeToken();
  return response;
};

// 密碼重置請求（公開端點，不需要認證）
export const passwordReset = async (email) => {
  return axios.post(`${API_BASE_URL}/passwordReset`, { email });
};

// 密碼重置驗證（公開端點，不需要認證）
export const passwordResetVerify = async (requestId, hash, userId, password, confirm_password) => {
  return axios.post(`${API_BASE_URL}/passwordResetVerify`, { 
    requestId, 
    hash, 
    userId, 
    password, 
    confirm_password 
  });
};

// 獲取當前用戶信息 - 新增
export const getCurrentUser = async () => {
  const response = await axios.get(`${API_BASE_URL}/me`);
  if (response.data.success && response.data.data) {
    setUser(response.data.data);
    return response.data.data;
  }
  return null;
};

// 刷新 Token - 新增
export const refreshToken = async () => {
  const response = await axios.post(`${API_BASE_URL}/refresh`);
  if (response.data.success && response.data.access_token) {
    setToken(response.data.access_token);
    return response.data.access_token;
  }
  return null;
};

// 檢查是否已登入
export const isAuthenticated = () => {
  return !!getToken();
};


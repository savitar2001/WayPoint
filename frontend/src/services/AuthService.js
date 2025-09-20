import axios from 'axios';
import { initializeEcho as initEcho, disconnectEcho as discEcho, getEcho } from './echo'; 

const API_BASE_URL = `${process.env.REACT_APP_BACKEND_URL}/api`;
const WEB_BASE_URL = process.env.REACT_APP_BACKEND_URL;

// Configure axios defaults
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;
// 明確指定 XSRF cookie 與 header 名稱（Axios 預設也如此，這裡顯式設定以避免偏差）
axios.defaults.xsrfCookieName = 'XSRF-TOKEN';
axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN';

// 初始化 CSRF（只需取得 Sanctum 的 CSRF cookie，切勿手動塞 header）
export const initializeCsrfToken = async () => {
  await axios.get(`${WEB_BASE_URL}/sanctum/csrf-cookie`, { withCredentials: true });
  console.log('CSRF session cookie 已初始化');
};

// 使用 Axios 攔截器來自動處理 CSRF token 過期問題
axios.interceptors.response.use(
  // 如果請求成功，直接回傳回應
  (response) => response,
  // 如果請求失敗，進行處理
  async (error) => {
    const originalRequest = error?.config || {};

    // 檢查是否為 419 錯誤 (Page Expired) 且不是重試請求
    if (error?.response?.status === 419 && !originalRequest._retry) {
      console.log('CSRF 可能已過期，嘗試重新取得 cookie 後重試...');
      originalRequest._retry = true; // 標記為重試請求，避免無限循環

      try {
        // 重新取得 Sanctum CSRF cookie
        await axios.get(`${WEB_BASE_URL}/sanctum/csrf-cookie`, { withCredentials: true });
        // 使用更新後的 cookie 重新發送原始請求
        return axios(originalRequest);
      } catch (refreshError) {
        console.error('重新取得 CSRF cookie 失敗:', refreshError);
        return Promise.reject(refreshError);
      }
    }

    // 對於其他非 419 的錯誤，直接拋出
    return Promise.reject(error);
  }
);


// 登入
export const login = async (email, password, dispatch) => {
  const response = await axios.post(`${WEB_BASE_URL}/login`, { email, password });
  if (response?.data?.data?.userId) {
    initEcho(response.data.data.userId, dispatch);
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

// 登出
export const logout = async () => {
  const response = await axios.post(`${WEB_BASE_URL}/logout`, {}, { withCredentials: true });
  if (getEcho()) {
    discEcho(); 
  }
  return response;
};

// 刪除帳戶
export const deleteAccount = async () => {
  return axios.delete(`${WEB_BASE_URL}/deleteAccount`, { withCredentials: true });
};

// 密碼重置請求
export const passwordReset = async (email) => {
  return axios.post(`${WEB_BASE_URL}/passwordReset`, { email }, { withCredentials: true });
};

// 密碼重置
export const passwordResetVerify = async (requestId, hash, userId, password, confirm_password) => {
  return axios.post(`${WEB_BASE_URL}/passwordResetVerify`, { requestId, hash, userId, password, confirm_password }, { withCredentials: true });
};


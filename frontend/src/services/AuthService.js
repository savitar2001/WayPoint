import axios from 'axios';
import { initializeEcho as initEcho, disconnectEcho as discEcho, getEcho } from './echo'; 

const API_BASE_URL = `${process.env.REACT_APP_BACKEND_URL}/api`;
const WEB_BASE_URL = process.env.REACT_APP_BACKEND_URL;

// Configure axios defaults
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;


// 初始化 CSRF Cookie
export const initializeCsrfToken = async () => {
    await axios.get(`${WEB_BASE_URL}/sanctum/csrf-cookie`);
    console.log('CSRF Cookie 已初始化');
    const tokenValue = document.cookie
    .split('; ')
    .find(row => row.startsWith('XSRF-TOKEN='))
    ?.split('=')[1];
    return tokenValue ? decodeURIComponent(tokenValue) : null;
};

// 登入
export const login = async (email, password,dispatch) => {
    let csrfToken = null;
    csrfToken = await initializeCsrfToken();
    const response = await axios.post(`${WEB_BASE_URL}/login`, { email, password }, 
    );
    if (response.data.data && response.data.data.userId) { 
        console.log('Echo initialized after login from AuthService');
        initEcho(response.data.data.userId, dispatch, csrfToken);
        console.log('Echo initialized after login from AuthService');
    }
    return response.data;
};


// 註冊用戶
export const register = async (name, email, password, confirm_password) => {
    const response = await axios.post(`${API_BASE_URL}/register`, {
      name,
      email,
      password,
      confirm_password // Laravel 需要確認密碼字段
    });
    return response; // 返回後端的響應數據
  };

//帳號驗證
export const verifyAccount = async (requestId, hash, userId) => {
    const response = await axios.post(`${API_BASE_URL}/verify`, {requestId, hash, userId});
    return response; // 返回後端的響應數據
};

//登出
export const logout = async () => {
    await initializeCsrfToken();
    const response = await axios.post(`${WEB_BASE_URL}/logout`);
    if (getEcho()) {
        discEcho(); 
        console.log('Echo disconnected after logout from AuthService');
    }
    return response; // 返回後端的響應數據
}

//刪除帳戶
export const deleteAccount = async () => {
    await initializeCsrfToken();
    const response = await axios.delete(`${WEB_BASE_URL}/deleteAccount`);
    return response; // 返回後端的響應數據
}

//密碼重置請求
export const passwordReset = async (email) => {
    await initializeCsrfToken();
    const response = await axios.post(`${WEB_BASE_URL}/passwordReset`, { email });
    return response; // 返回後端的響應數據
}

//密碼重置
export const passwordResetVerify = async (requestId, hash, userId,password, confirm_password) => {
    await initializeCsrfToken();
    const response = await axios.post(`${WEB_BASE_URL}/passwordResetVerify`, {requestId, hash, userId, password, confirm_password});
    return response; // 返回後端的響應數據
}


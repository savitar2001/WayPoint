import axios from 'axios';
import { initializeEcho as initEcho, disconnectEcho as discEcho, getEcho } from './echo'; 

const API_BASE_URL = `${process.env.REACT_APP_BACKEND_URL}/api`;
const WEB_BASE_URL = process.env.REACT_APP_BACKEND_URL;

// Configure axios defaults
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

// 初始化 CSRF Cookie 和 Token
export const initializeCsrfToken = async () => {
    await axios.get(`${WEB_BASE_URL}/sanctum/csrf-cookie`);
    console.log('CSRF session cookie 已初始化');

    // 步驟 2: 呼叫我們的新端點來取得 token 的值。
    // 注意 URL 的變化：不再有 /api 前綴
    const response = await axios.get(`${WEB_BASE_URL}/csrf-token`);
    const token = response.data.csrf_token;

    if (token) {
        console.log('成功取得 CSRF Token');
        axios.defaults.headers.common['X-XSRF-TOKEN'] = token;
    } else {
        console.error('無法取得 CSRF Token');
    }

};


// 登入
export const login = async (email, password, dispatch) => {
    await initializeCsrfToken();
    const response = await axios.post(`${WEB_BASE_URL}/login`, { email, password }, { withCredentials: true });
    if (response.data.data && response.data.data.userId) { 
        initEcho(response.data.data.userId, dispatch, axios.defaults.headers.common['X-XSRF-TOKEN']);
    }
    return response.data;
};

// 註冊用戶
export const register = async (name, email, password, confirm_password) => {
    await initializeCsrfToken();
    const response = await axios.post(`${API_BASE_URL}/register`, {
        name,
        email,
        password,
        confirm_password
    }, { withCredentials: true });
    return response;
};

// 帳號驗證
export const verifyAccount = async (requestId, hash, userId) => {
    await initializeCsrfToken();
    const response = await axios.post(`${API_BASE_URL}/verify`, { requestId, hash, userId }, { withCredentials: true });
    return response;
};

// 登出
export const logout = async () => {
    await initializeCsrfToken();
    const response = await axios.post(`${WEB_BASE_URL}/logout`, {}, { withCredentials: true });
    if (getEcho()) {
        discEcho(); 
    }
    return response;
};

// 刪除帳戶
export const deleteAccount = async () => {
    await initializeCsrfToken();
    return axios.delete(`${WEB_BASE_URL}/deleteAccount`, { withCredentials: true });
};

// 密碼重置請求
export const passwordReset = async (email) => {
    await initializeCsrfToken();
    return axios.post(`${WEB_BASE_URL}/passwordReset`, { email }, { withCredentials: true });
};

// 密碼重置
export const passwordResetVerify = async (requestId, hash, userId, password, confirm_password) => {
    await initializeCsrfToken();
    return axios.post(`${WEB_BASE_URL}/passwordResetVerify`, { requestId, hash, userId, password, confirm_password }, { withCredentials: true });
};


import axios from 'axios';
import { initializeEcho as initEcho, disconnectEcho as discEcho, getEcho } from './echo'; 

const API_BASE_URL = `${process.env.REACT_APP_BACKEND_URL}/api`;
const WEB_BASE_URL = process.env.REACT_APP_BACKEND_URL;

// Configure axios defaults
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;
axios.defaults.xsrfCookieName = 'XSRF-TOKEN';
axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN';


// 簡易取得 cookie
const getCookie = (name) => {
    return document.cookie
        .split('; ')
        .find(row => row.startsWith(name + '='))
        ?.split('=')[1] || null;
};

// 初始化 CSRF Cookie 並設定 header
export const initializeCsrfToken = async () => {
    await axios.get(`${WEB_BASE_URL}/sanctum/csrf-cookie`, { withCredentials: true });
    console.log('CSRF Cookie 已初始化');
    const raw = getCookie('XSRF-TOKEN');
    if (raw) {
        const decoded = decodeURIComponent(raw);
        axios.defaults.headers.common['X-XSRF-TOKEN'] = decoded; // 明確設定
        console.log('讀取到的 XSRF-TOKEN:', decoded);
        return decoded;
    }
    console.warn('未取得 XSRF-TOKEN cookie');
    return null;
};

// 登入
export const login = async (email, password, dispatch) => {
    const csrfToken = await initializeCsrfToken();
    const response = await axios.post(`${WEB_BASE_URL}/login`, { email, password }, { withCredentials: true });
    if (response.data.data && response.data.data.userId) { 
        initEcho(response.data.data.userId, dispatch, csrfToken);
    }
    return response.data;
};

// 註冊用戶
export const register = async (name, email, password, confirm_password) => {
    const csrfToken = await initializeCsrfToken();
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
    const csrfToken = await initializeCsrfToken();
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


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
    // 為了確保請求是乾淨的，在請求新 token 前先刪除可能存在的舊 token
    delete axios.defaults.headers.common['X-XSRF-TOKEN'];
    delete axios.defaults.headers.common['X-CSRF-TOKEN'];

    await axios.get(`${WEB_BASE_URL}/sanctum/csrf-cookie`);
    console.log('CSRF session cookie 已初始化');

    // 步驟 2: 呼叫我們的新端點來取得 token 的值。
    // 注意 URL 的變化：不再有 /api 前綴
    const response = await axios.get(`${WEB_BASE_URL}/csrf-token`);
    const token = response.data.csrf_token;

    if (token) {
        console.log('成功取得 CSRF Token');
        axios.defaults.headers.common['X-XSRF-TOKEN'] = token;
        axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    } else {
        console.error('無法取得 CSRF Token');
    }

};

// 使用 Axios 攔截器來自動處理 CSRF token 過期問題
axios.interceptors.response.use(
    // 如果請求成功，直接回傳回應
    response => response,
    // 如果請求失敗，進行處理
    async (error) => {
        const originalRequest = error.config;

        // 檢查是否為 419 錯誤 (Page Expired) 且不是重試請求
        if (error.response.status === 419 && !originalRequest._retry) {
            console.log('CSRF token 可能已過期，正在嘗試重新獲取...');
            originalRequest._retry = true; // 標記為重試請求，避免無限循環

            try {
                // 重新呼叫初始化函數以獲取新的 CSRF token
                await initializeCsrfToken();
                
                // 使用更新後的 axios 預設標頭重新發送原始請求
                console.log('成功獲取新 token，正在重試原始請求...');
                return axios(originalRequest);
            } catch (refreshError) {
                console.error('刷新 CSRF token 失敗:', refreshError);
                // 如果刷新 token 也失敗了，則將錯誤拋出
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
    if (response.data.data && response.data.data.userId) { 
        initEcho(response.data.data.userId, dispatch, axios.defaults.headers.common['X-XSRF-TOKEN']);
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


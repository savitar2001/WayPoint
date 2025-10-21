import { createSlice } from '@reduxjs/toolkit';

const initialState = {
  isLoggedIn: !!sessionStorage.getItem('access_token'), // 檢查 sessionStorage 是否有 JWT Token
  userId: sessionStorage.getItem('user') ? JSON.parse(sessionStorage.getItem('user')).id : null,
  userName: sessionStorage.getItem('user') ? JSON.parse(sessionStorage.getItem('user')).name : '',
};

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    login: (state, action) => {
      state.isLoggedIn = true; // 設置為已登入
      state.userId = action.payload.userId; // 設置用戶 ID
      state.userName = action.payload.userName; // 設置用戶名稱
      
      // JWT Token 由 AuthService 管理，這裡只更新 Redux 狀態
      // Token 已在 AuthService.login() 中存儲到 sessionStorage
    },
    logout: (state) => {
      state.isLoggedIn = false; // 設置為未登入
      state.userId = null; // 清空用戶 ID
      state.userName = ''; // 清空用戶名稱

      // Token 清除由 AuthService.logout() 處理
      // 這裡只清除 Redux 狀態
    },
  },
});


export const { login, logout } = authSlice.actions; // 導出 action
export default authSlice.reducer; // 導出 reducer
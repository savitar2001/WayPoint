import { createSlice } from '@reduxjs/toolkit';

const initialState = {
  isLoggedIn: !!localStorage.getItem('authToken'), // 檢查 localStorage 是否有 Token
  userId: localStorage.getItem('userId') || null,
  userName: localStorage.getItem('userName') || '',
};

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    login: (state, action) => {
      state.isLoggedIn = true; // 設置為已登入
      state.userId = action.payload.userId; // 設置用戶 ID
      state.userName = action.payload.userName; // 設置用戶名稱
      // 保存到 localStorage
      localStorage.setItem('authToken', action.payload.token);
      localStorage.setItem('userId', action.payload.userId);
      localStorage.setItem('userName', action.payload.userName);
    },
    logout: (state) => {
      state.isLoggedIn = false; // 設置為未登入
      state.userId = null; // 清空用戶 ID
      state.userName = ''; // 清空用戶名稱

      // 清除 localStorage
      localStorage.removeItem('authToken');
      localStorage.removeItem('userId');
      localStorage.removeItem('userName');
    },
  },
});


export const { login, logout } = authSlice.actions; // 導出 action
export default authSlice.reducer; // 導出 reducer
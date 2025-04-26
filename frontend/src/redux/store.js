import { configureStore } from '@reduxjs/toolkit';
import authReducer from './authSlice'; // 引入認證的 reducer

const store = configureStore({
  reducer: {
    auth: authReducer, // 將認證 reducer 添加到 store
  },
});

export default store;
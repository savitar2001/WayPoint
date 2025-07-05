import { configureStore } from '@reduxjs/toolkit';
import authReducer from './authSlice'; 
import marqueeReducer from './marqueeSlice';

const store = configureStore({
  reducer: {
    auth: authReducer, 
    marquee: marqueeReducer,
  },
});

export default store;
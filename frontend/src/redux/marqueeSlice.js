import { createSlice } from '@reduxjs/toolkit';

const initialState = {
  message: '',
  isVisible: false,
};

export const marqueeSlice = createSlice({
  name: 'marquee',
  initialState,
  reducers: {
    showMarqueeMessage: (state, action) => {
      state.message = action.payload;
      state.isVisible = true;
    },
    hideMarqueeMessage: (state) => {
      state.message = '';
      state.isVisible = false;
    },
  },
});

export const { showMarqueeMessage, hideMarqueeMessage } = marqueeSlice.actions;

export default marqueeSlice.reducer;
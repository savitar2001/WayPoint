import React from 'react';
import ReactDOM from 'react-dom/client';
import './index.css';
import App from './App';
import reportWebVitals from './reportWebVitals';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: process.env.REACT_APP_REVERB_APP_KEY,
    wsHost: process.env.REACT_APP_REVERB_HOST,
    wsPort: process.env.REACT_APP_REVERB_PORT ?? 80,
    wssPort: process.env.REACT_APP_REVERB_PORT ?? 443,
    forceTLS: (process.env.REACT_APP_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    // 如果 Reverb 伺服器沒有使用 TLS (即 scheme 是 http)，需要明確禁用 TLS
    // disableStats: true, // 根據需要取消註解
    // encrypted: false, // 根據需要取消註解，如果 forceTLS 為 false，這個通常也設為 false
});
const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(
  <React.StrictMode>
    <App />
  </React.StrictMode>
);

// If you want to start measuring performance in your app, pass a function
// to log results (for example: reportWebVitals(console.log))
// or send to an analytics endpoint. Learn more: https://bit.ly/CRA-vitals
reportWebVitals();

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import { showMarqueeMessage } from '../redux/marqueeSlice'; 
import axios from 'axios'; 

window.Pusher = Pusher;
let echoInstance = null;


const initializeEcho = (userId, dispatch) => {
    if (echoInstance) {
        disconnectEcho();
    }

    const reverbAppKey = process.env.REACT_APP_REVERB_APP_KEY; // 從 .env 獲取
    const reverbHost = process.env.REACT_APP_REVERB_HOST || window.location.hostname;
    const reverbPort = process.env.REACT_APP_REVERB_PORT || 8080; // Reverb 預設埠號
    const reverbScheme = process.env.REACT_APP_REVERB_SCHEME || 'ws'; 

    if (!reverbAppKey) {
        console.error('Reverb App Key is not defined in environment variables.');
        return null;
    }


    echoInstance = new Echo({
        broadcaster: 'reverb', 
        key: reverbAppKey,
        wsHost: reverbHost,
        wsPort: reverbPort,
        wssPort: reverbPort, //
        forceTLS: reverbScheme === 'wss',
        enabledTransports: [reverbScheme === 'wss' ? 'wss' : 'ws'], 
        authEndpoint: `${process.env.REACT_APP_BACKEND_URL}/broadcasting/auth`,
        authorizer: (channel, options) => {
            return {
                authorize: (socketId, callback) => {
                    axios.post(options.authEndpoint, {
                        socket_id: socketId,
                        channel_name: channel.name
                    }, {
                        withCredentials: true, // 關鍵：允許跨域請求攜帶 cookie
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                            // 不需要手動設定 CSRF 標頭；Axios 會依據 XSRF-TOKEN cookie 自動加上 X-XSRF-TOKEN
                        }
                    })
                    .then(response => {
                        callback(null, response.data);
                    })
                    .catch(error => {
                        console.error('Authorization error:', error?.response || error);
                        callback(error);
                    });
                }
            };
        }
    });

    if (userId && echoInstance && dispatch) {
        echoInstance.private(`user.${userId}`)
            .listen('.PostPublished', (e) => {
                console.log('新貼文發布 (來自 Reverb):', e);
                if (e) { 
                    const combinedMessage = `${e.authorName} ${e.message}`;
                    dispatch(showMarqueeMessage(combinedMessage)); 
                } 
            })
            .listenForWhisper('typing', (e) => {
                console.log('Whisper event:', e);
            });
        console.log(`Listening on private channel: user.${userId} via Reverb`);
    } else if (!userId) {
        console.warn('User ID not provided, cannot subscribe to private channel.');
    }

    // 新增：监听公共频道 'chat'
    if (echoInstance) {
        const publicChannelName = 'chat';
        const publicEventName = 'newMessage'; // 与后端 NewMessage.php 中 broadcastAs() 定义的一致

        echoInstance.channel(publicChannelName) // 使用 .channel() 监听公共频道
            .listen(publicEventName, (eventData) => {
                console.log(`[Public Channel: ${publicChannelName}] Event '${publicEventName}':`, eventData);
                if (eventData && eventData.message) {
                    dispatch(showMarqueeMessage(`公共消息: ${eventData.message}`));
                }
            });
        console.log(`Listening on public channel: ${publicChannelName} for event '${publicEventName}' via Reverb`);
    }

    return echoInstance;
};

const getEcho = () => {
    if (!echoInstance) {
        console.log('Echo created.');
    }
    return echoInstance;
};

const disconnectEcho = () => {
    if (echoInstance) {
        echoInstance.disconnect();
        echoInstance = null;
        console.log('Echo disconnected.');
    }
};

export { initializeEcho, getEcho, disconnectEcho };
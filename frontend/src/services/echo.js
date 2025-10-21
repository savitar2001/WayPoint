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

    // 獲取 JWT Token
    const getToken = () => {
        return sessionStorage.getItem('access_token');
    };

    echoInstance = new Echo({
        broadcaster: 'reverb', 
        key: reverbAppKey,
        wsHost: reverbHost,
        wsPort: reverbPort,
        wssPort: reverbPort,
        forceTLS: reverbScheme === 'wss',
        enabledTransports: [reverbScheme === 'wss' ? 'wss' : 'ws'], 
        authEndpoint: `${process.env.REACT_APP_BACKEND_URL}/api/broadcasting/auth`,
        auth: {
            headers: {
                Authorization: `Bearer ${getToken()}`,
                Accept: 'application/json',
            }
        },
        authorizer: (channel, options) => {
            return {
                authorize: (socketId, callback) => {
                    const token = getToken();
                    
                    axios.post(options.authEndpoint, {
                        socket_id: socketId,
                        channel_name: channel.name
                    }, {
                        headers: {
                            'Authorization': `Bearer ${token}`,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => {
                        callback(null, response.data);
                    })
                    .catch(error => {
                        console.error('Broadcasting Authorization error:', error?.response || error);
                        callback(error);
                    });
                }
            };
        }
    });

    if (userId && echoInstance && dispatch) {
        echoInstance.private(`user.${userId}`)
            .listen('.PostPublished', (e) => {
                console.log('=== 📢 收到廣播事件 ===');
                console.log('事件數據:', e);
                console.log('dispatch 函數類型:', typeof dispatch);
                console.log('showMarqueeMessage action:', showMarqueeMessage);
                
                if (e) { 
                    const combinedMessage = `${e.authorName} ${e.message}`;
                    console.log('✅ 準備 dispatch 的訊息:', combinedMessage);
                    
                    try {
                        dispatch(showMarqueeMessage(combinedMessage));
                        console.log('✅ dispatch 已成功執行');
                    } catch (error) {
                        console.error('❌ dispatch 執行失敗:', error);
                    }
                } else {
                    console.warn('⚠️ 事件數據為空');
                }
                console.log('=== 📢 廣播事件處理完畢 ===');
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
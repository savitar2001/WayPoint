import axios from 'axios';

const API_BASE_URL = 'http://new-project.local/api';

//追蹤其他用戶
export const addSubscriber = async (userId, userSubscriberId) => {
    try {
        const response = await axios.post(`${API_BASE_URL}/addSubscriber`,{userId, userSubscriberId});
        return response;
    } catch (error) {
        console.error('Error adding subscriber:', error);
        throw error;
    }
}

//創建頭像
export const createAvatar = async (userId,  base64Image) => { 
    try {
        const response = await axios.post(`${API_BASE_URL}/createAvatar`, { userId, base64Image });
        return response;
    } catch (error) {
        console.error('Error creating avatar:', error);
        throw error;
    }
}

//移除追蹤
export const removeSubscriber = async (followerId, subscriberId) => {
    try {
        const response = await axios.delete(`${API_BASE_URL}/removeSubscriber/${followerId}/${subscriberId}`);
        return response;
    }
    catch (error) {
        console.error('Error removing subscriber:', error);
        throw error;
    }
}

//取得粉絲資料
export const getFollower = async (userId) => {
    try {
        const response = await axios.get(`${API_BASE_URL}/getFollower/${userId}`);
        return response;
    } catch (error) {
        console.error('Error fetching followers:', error);
        throw error;
    }
}

//取得追蹤者資料
export const getSubscriber = async (userId) => {
    try {
        const response = await axios.get(`${API_BASE_URL}/getSubscriber/${userId}`);
        return response;
    } catch (error) {
        console.error('Error fetching subscribers:', error);
        throw error;
    }
}

//取得用戶資料
export const getUserInformation = async (userId) => {
    try {
        const response = await axios.get(`${API_BASE_URL}/getUserInformation/${userId}`);
        return response;
    } catch (error) {
        console.error('Error fetching user:', error);
        throw error;
    }
}

//透過搜尋名字尋找用戶
export const searchByName = async (name) => {
    try {
        const response = await axios.get(`${API_BASE_URL}/searchByName/${name}`);
        return response;
    } catch (error) {
        console.error('Error searching user:', error);
        throw error;
    }
}
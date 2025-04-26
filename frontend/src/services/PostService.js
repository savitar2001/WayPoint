import axios from 'axios';

const API_BASE_URL = 'http://new-project.local/api';

//創建貼文
export const createPost = async (userId, name, content, tag, base64) => {
    try {
        const response = await axios.post(`${API_BASE_URL}/createPost`, {userId, name, content, tag, base64});
        return response;
    } catch (error) {
        console.error('Error creating post:', error);
        throw error;
    }
}

//刪除貼文
export const deletePost = async (userId,postId) => {
    try {
        const response = await axios.delete(`${API_BASE_URL}/deletePost/${userId}/${postId}`);
        return response;
    } catch (error) {
        console.error('Error deleting post:', error);
        throw error;
    }
}

//對貼文評論
export const commentOnPost = async (userId, postId, comment) => {
    try {
        const response = await axios.post(`${API_BASE_URL}/commentOnPost`, {userId, postId, comment});
        return response;
    } catch (error) {
        console.error('Error commenting on post:', error);
        throw error;
    }
}

//刪除貼文評論
export const deletePostComment = async (userId, postId, commentId) => {
    try {
        const response = await axios.delete(`${API_BASE_URL}/deletePostComment/${userId}/${postId}/${commentId}`);
        return response;
    } catch (error) {
        console.error('Error deleting comment:', error);
        throw error;
    }
}

//對評論進行回覆
export const replyToComment = async (userId, commentId, comment) => {
    try {
        const response = await axios.post(`${API_BASE_URL}/replyToComment`, {userId, commentId, comment});
        return response;
    } catch (error) {
        console.error('Error replying to comment:', error);
        throw error;
    }
}

//刪除評論回覆
export const deleteReplyComment = async (userId, commentId, replyId) => {
    try {
        const response = await axios.delete(`${API_BASE_URL}/deleteReplyComment/${userId}/${commentId}/${replyId}`);
        return response;
    } catch (error) {
        console.error('Error deleting reply:', error);
        throw error;
    }
}

//獲取貼文資訊
export const getPost = async (userId,postId,tag) => {
    try {
        const response = await axios.get(`${API_BASE_URL}/getPost/${userId}/${postId}/${tag}`);
        return response;
    } catch (error) {
        console.error('Error getting post:', error);
        throw error;
    }
}

//對貼文按讚
export const likePost = async (userId, postId) => {
    try {
        const response = await axios.post(`${API_BASE_URL}/likePost`, {userId, postId});
        return response;
    } catch (error) {
        console.error('Error liking post:', error);
        throw error;
    }
}

//取得對貼文按讚用戶
export const getPostLike = async (postId) => {
    try {
        const response = await axios.get(`${API_BASE_URL}/getPostLike/${postId}`);
        return response;
    } catch (error) {
        console.error('Error getting post likes:', error);
        throw error;
    }
}

//取得貼文評論
export const getPostComment = async (postId) => {
    try {
        const response = await axios.get(`${API_BASE_URL}/getPostComment/${postId}`);
        return response;
    } catch (error) {
        console.error('Error getting post comments:', error);
        throw error;
    }
}

//取得評論回覆
export const getCommentReply = async (commentId) => {
    try {
        const response = await axios.get(`${API_BASE_URL}/getCommentReply/${commentId}`);
        return response;
    } catch (error) {
        console.error('Error getting comment replies:', error);
        throw error;
    }
}
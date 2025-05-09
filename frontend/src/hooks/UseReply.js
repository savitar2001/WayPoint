import { useState, useEffect } from 'react';
import { getCommentReply, replyToComment } from '../services/PostService';

const useReply = (userId) => {
    const [replies, setReplies] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [addingReply, setAddingReply] = useState(false); 

    const fetchReplies = async (commentId) => {
        setLoading(true);
        setError(null);
        try {
            const response = await getCommentReply(commentId);
            setReplies(response.data.data || []);
        } catch (err) {
            setError(err.message || 'Failed to fetch replies');
        } finally {
            setLoading(false);
        }
    };

    const addReply = async (commentId, comment) => {
        if (!commentId || !userId || !comment) return;
        try {
             await replyToComment(userId, commentId, comment);
             setReplies((prevReplies) => [...prevReplies, comment]);
        } catch (err) {
            setError(err.message || 'Failed to add reply');
        } finally {
            setAddingReply(false);
        }
    };

    return { replies, loading, error,addingReply,fetchReplies, addReply };
};

export default useReply;
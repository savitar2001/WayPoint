import { useState, useEffect } from 'react';
import { getPostLike, likePost } from '../services/PostService';

const useLike = (userId) => {
    const [likes, setLikes] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [isLiked, setIsLiked] = useState(false);

    const fetchLikes = async (postId) => {
        setLoading(true);
        setError(null);
        try {
            const response = await getPostLike(postId);
            if (response.data && response.data.success) {
                setLikes(response.data.data);
                setIsLiked(response.data.data.some((like) => Number(like.user_id) === Number(userId)));
            } else {
                setError(response.data.error || 'Failed to fetch likes');
            }
        } catch (err) {
            setError(err.message || 'An error occurred');
        } finally {
            setLoading(false);
        }
    };

    const toggleLike = async (postId) => {
        if (!postId || !userId) return;

        setLoading(true);
        setError(null);
        try {
            const response = await likePost(userId, postId);
            if (response.data && response.data.success) {
                await fetchLikes(postId);
            } else {
                setError(response.data.error || 'Failed to toggle like');
            }
        } catch (err) {
            setError(err.message || 'An error occurred');
        } finally {
            setLoading(false);
        }
    };

    return { likes, loading, error, fetchLikes,isLiked, toggleLike };
};

export default useLike;
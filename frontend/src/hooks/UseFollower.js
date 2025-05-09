import { useState, useEffect } from 'react';
import { getFollower, removeSubscriber } from '../services/UserService';

const useFollower = (userId) => {
    const [followers, setFollowers] = useState([]);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const fetchFollowers = async () => {
        setLoading(true);
        setError(null);
        try {
            const response = await getFollower(userId);
            const followersData = response.data.data || [];
            setFollowers(followersData);
            return followersData; 
        } catch (err) {
            console.error('Error fetching followers:', err);
            setError(err);
        } finally {
            setLoading(false);
        }
    };

    const removeFollower = async (followerId) => {
        setLoading(true);
        setError(null);
        try {
            await removeSubscriber(followerId, userId);
            setFollowers((prevFollowers) =>
                prevFollowers.filter((follower) => follower.id !== followerId)
            );
        } catch (err) {
            console.error('Error removing follower:', err);
            setError(err);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        if (userId) {
            fetchFollowers();
        }
    }, [userId]);

    return {
        followers,
        loading,
        error,
        fetchFollowers,
        removeFollower,
    };
};

export default useFollower;
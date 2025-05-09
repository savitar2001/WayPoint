import { useState } from 'react';
import { searchByName, getUserInformation, createAvatar } from '../services/UserService';

const useUser = () => {
    const [users, setUsers] = useState([]);
    const [userInfo, setUserInfo] = useState(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const fetchUsersByName = async (name) => {
        setLoading(true);
        setError(null);
        try {
            const response = await searchByName(name);
            if (response.data && response.data.success) {
                const userData = response.data.data;
                const formattedUsers = Array.isArray(userData)
                ? userData
                : [{
                    id: userData.id,
                    name: userData.name,
                    avatar_url: userData.avatarUrl, 
                }];
                setUsers(formattedUsers);
            } else {
                setError(response.data.error || 'Failed to fetch users');
            }
        } catch (err) {
            setError(err.message || 'An error occurred');
        } finally {
            setLoading(false);
        }
    };

    const fetchUserInformation = async (userId) => {
        setLoading(true);
        setError(null);
        try {
            const response = await getUserInformation(userId);
            if (response.data && response.data.success) {
                setUserInfo(response.data.data);
            } else {
                setError(response.data.error || 'Failed to fetch user information');
            }
        } catch (err) {
            setError(err.message || 'An error occurred');
        } finally {
            setLoading(false);
        }
    };

    const uploadAvatar = async (userId, base64Image) => {
        setLoading(true);
        setError(null);
        try {
            const response = await createAvatar(userId, base64Image);
            if (response.data && response.data.success) {
                return response.data; 
            } else {
                setError(response.data.error || 'Failed to upload avatar');
            }
        } catch (err) {
            setError(err.message || 'An error occurred');
        } finally {
            setLoading(false);
        }
    };

    return {
        users,
        userInfo,
        loading,
        error,
        fetchUsersByName,
        fetchUserInformation,
        uploadAvatar, 
    };
};

export default useUser;
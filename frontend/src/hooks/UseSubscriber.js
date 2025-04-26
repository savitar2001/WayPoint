import { useState, useEffect } from 'react';
import { getSubscriber, addSubscriber, removeSubscriber } from '../services/UserService';

const useSubscriber = (userId) => {
  const [subscribers, setSubscribers] = useState([]);
  const [error, setError] = useState(null);
  const fetchSubscribers = async () => {
    try {
      const response = await getSubscriber(userId);
      const subscriberData = response.data.data;
      setSubscribers(subscriberData);
    } catch (err) {
      setError('Failed to fetch subscribers');
      console.error(err);
    }
  };

  useEffect(() => {
    if (userId) {
      fetchSubscribers();
    }
  }, [userId]);

  const handleAddSubscriber = async (userSubscriberId) => {
    try {
      await addSubscriber(userSubscriberId,userId);
    } catch (err) {
      setError('Failed to add subscriber');
      console.error(err);
    }
  };

  const handleRemoveSubscriber = async (subscriberId) => {
    try {
      await removeSubscriber(subscriberId,userId);
    } catch (err) {
      setError('Failed to remove subscriber');
      console.error(err);
    }
  };

  return { subscribers, error, fetchSubscribers, handleAddSubscriber, handleRemoveSubscriber };
};

export default useSubscriber;
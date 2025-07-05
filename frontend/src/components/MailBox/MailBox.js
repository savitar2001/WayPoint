import React, { useState, useEffect, useCallback } from 'react';
import PropTypes from 'prop-types';
import Button from '../Button/Button.js';
import Avatar from '../Avatar/Avatar';
import Modal from '../Modal/Modal.js';
import './MailBox.css';
import { getEcho } from '../../services/echo.js'; 

// Placeholder for notification data structure:
// {
//   id: string,
//   type: string, // e.g., 'new_post', 'new_follower'
//   message: string,
//   actor: { name: string, avatarUrl?: string }, // User who caused the notification
//   timestamp: string, // ISO date string
//   isRead: boolean,
//   link?: string // URL to navigate to on click
// }

const NotificationItem = ({ notification, onMarkAsRead, onClick }) => {
  const handleItemClick = () => {
    onClick(notification);
    if (!notification.isRead) {
      onMarkAsRead(notification.id);
    }
  };

  return (
    <div
      className={`notification-item ${notification.isRead ? 'read' : 'unread'}`}
      onClick={handleItemClick}
      role="button"
      tabIndex={0}
      onKeyPress={(e) => e.key === 'Enter' && handleItemClick()}
    >
      {!notification.isRead && <span className="unread-dot" aria-label="unread"></span>}
      <div className="notification-content">
        {/* Example content - customize as needed */}
        <p><strong>{notification.actor?.name || 'System'}</strong> {notification.message}</p>
        <small>{new Date(notification.timestamp).toLocaleString()}</small>
      </div>
      {/* Optionally, a direct "Mark as read" button per item */}
      {/* {!notification.isRead && <Button onClick={(e) => { e.stopPropagation(); onMarkAsRead(notification.id); }}>Mark Read</Button>} */}
    </div>
  );
};

NotificationItem.propTypes = {
  notification: PropTypes.shape({
    id: PropTypes.string.isRequired,
    type: PropTypes.string.isRequired,
    message: PropTypes.string.isRequired,
    actor: PropTypes.shape({
      name: PropTypes.string,
      avatarUrl: PropTypes.string,
    }),
    timestamp: PropTypes.string.isRequired,
    isRead: PropTypes.bool.isRequired,
    link: PropTypes.string,
  }).isRequired,
  onMarkAsRead: PropTypes.func.isRequired,
  onClick: PropTypes.func.isRequired,
};


const MailBox = () => {
  const [isOpen, setIsOpen] = useState(false);
  const [notifications, setNotifications] = useState([]);
  const [isLoading, setIsLoading] = useState(false);
  // const [error, setError] = useState(null); // For error handling

  const unreadCount = notifications.filter(n => !n.isRead).length;

  // Fetch initial notifications
  const fetchNotifications = useCallback(async () => {
    setIsLoading(true);
    // setError(null);
    try {
      // --- API Call Placeholder ---
      // Example: const response = await api.get('/notifications');
      // setNotifications(response.data);
      // Simulate API call
      await new Promise(resolve => setTimeout(resolve, 1000));
      setNotifications([
        { id: '1', type: 'new_post', actor: { name: 'Alice' }, message: 'published a new post.', timestamp: new Date().toISOString(), isRead: false, link: '/posts/1' },
        { id: '2', type: 'new_follower', actor: { name: 'Bob' }, message: 'started following you.', timestamp: new Date(Date.now() - 3600000).toISOString(), isRead: true, link: '/user/bob' },
        { id: '3', type: 'mention', actor: { name: 'Charlie' }, message: 'mentioned you in a comment.', timestamp: new Date(Date.now() - 7200000).toISOString(), isRead: false, link: '/posts/1/comments/5' },
      ]);
      // --- End API Call Placeholder ---
    } catch (err) {
      console.error("Failed to fetch notifications:", err);
      // setError("Failed to load notifications.");
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    fetchNotifications();
  }, [fetchNotifications]);

  // Real-time updates with Echo
  useEffect(() => {
    const echo = getEcho();
    if (echo) {
      // Assuming your backend broadcasts on a 'NewNotification' event
      // And that the event data matches the notification structure
      const channel = echo.private(`user.${localStorage.getItem('userId')}`); // Or however you get current userId
      
      const handleNewNotification = (notificationData) => {
        console.log('New notification received via Echo:', notificationData);
        setNotifications(prevNotifications => [
          { ...notificationData, isRead: false, timestamp: new Date().toISOString() }, // Ensure new notifications are unread and have a timestamp
          ...prevNotifications
        ]);
      };
      
      channel.listen('NewNotification', handleNewNotification); // Adjust event name as needed
      console.log('MailBox listening for NewNotification event');

      return () => {
        channel.stopListening('NewNotification', handleNewNotification);
        console.log('MailBox stopped listening for NewNotification event');
      };
    } else {
      console.warn('Echo instance not available in MailBox. Real-time updates disabled.');
    }
  }, []); // Runs once on mount

  const toggleMailbox = () => setIsOpen(!isOpen);

  const handleMarkAsRead = useCallback((notificationId) => {
    setNotifications(prev =>
      prev.map(n => (n.id === notificationId ? { ...n, isRead: true } : n))
    );
    // --- API Call Placeholder: Mark as read on backend ---
    // api.post(`/notifications/${notificationId}/read`).catch(err => console.error("Failed to mark as read", err));
    // ---
  }, []);

  const handleMarkAllAsRead = () => {
    setNotifications(prev => prev.map(n => ({ ...n, isRead: true })));
    // --- API Call Placeholder: Mark all as read on backend ---
    // api.post('/notifications/mark-all-read').catch(err => console.error("Failed to mark all as read", err));
    // ---
  };

  const handleNotificationClick = (notification) => {
    console.log('Notification clicked:', notification);
    if (notification.link) {
      // Assuming you are using react-router or similar for navigation
      // import { useNavigate } from 'react-router-dom';
      // const navigate = useNavigate();
      // navigate(notification.link);
      alert(`Navigate to: ${notification.link}`); // Placeholder navigation
    }
    setIsOpen(false); // Close mailbox on click
  };

  const handleDeleteNotification = (notificationId) => {
    setNotifications(prev => prev.filter(n => n.id !== notificationId));
    // --- API Call Placeholder: Delete notification on backend ---
    // api.delete(`/notifications/${notificationId}`).catch(err => console.error("Failed to delete notification", err));
    // ---
  };
  
  const handleDeleteAllRead = () => {
    setNotifications(prev => prev.filter(n => !n.isRead));
    // --- API Call Placeholder: Delete all read notifications on backend ---
    // api.post('/notifications/delete-read').catch(err => console.error("Failed to delete read notifications", err));
    // ---
  }

  return (
    <div className="mailbox-container">
      <Button onClick={toggleMailbox} className="mailbox-toggle">
        Mailbox {unreadCount > 0 && <span className="unread-badge">{unreadCount}</span>}
      </Button>
      {isOpen && (
        <div className="mailbox-dropdown">
          <div className="mailbox-header">
            <h3>Notifications</h3>
            {notifications.length > 0 && unreadCount > 0 && (
              <Button onClick={handleMarkAllAsRead} size="small">Mark all as read</Button>
            )}
          </div>
          {isLoading ? (
            <p>Loading notifications...</p>
          // ) : error ? (
          //   <p className="error-message">{error}</p>
          ) : notifications.length === 0 ? (
            <p>Currently no new notifications.</p>
          ) : (
            <div className="notifications-list">
              {notifications.map(notification => (
                <NotificationItem
                  key={notification.id}
                  notification={notification}
                  onMarkAsRead={handleMarkAsRead}
                  onClick={handleNotificationClick}
                />
              ))}
            </div>
          )}
          {notifications.some(n => n.isRead) && (
             <div className="mailbox-footer">
                <Button onClick={handleDeleteAllRead} variant="danger" size="small">Delete all read</Button>
             </div>
          )}
        </div>
      )}
    </div>
  );
};

export default MailBox;
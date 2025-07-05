import React, { useEffect, useRef } from 'react'; 
import { useSelector, useDispatch } from 'react-redux';
import { hideMarqueeMessage } from '../../redux/marqueeSlice'; 
import './Marquee.css';

const Marquee = () => {
  const { message, isVisible } = useSelector((state) => state.marquee);
  const dispatch = useDispatch();
  const animationTimeoutRef = useRef(null);
  console.log('[Marquee 組件] isVisible:', isVisible, 'message:', message); // 檢查這個日誌的輸出

  useEffect(() => {
    if (isVisible && message) {
      const animationDuration = 15000; 

      if (animationTimeoutRef.current) {
        clearTimeout(animationTimeoutRef.current);
      }

      animationTimeoutRef.current = setTimeout(() => {
        dispatch(hideMarqueeMessage()); 
      }, animationDuration);
    }

    return () => {
      if (animationTimeoutRef.current) {
        clearTimeout(animationTimeoutRef.current);
      }
    };
  }, [isVisible, message, dispatch]); 

  if (!isVisible || !message) {
    return null;
  }

  return (
    <div className="marquee-container">
      <p key={message} className="marquee-text">
        {message}
      </p>
    </div>
  );
};

export default Marquee;
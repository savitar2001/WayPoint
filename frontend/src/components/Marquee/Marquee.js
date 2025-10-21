import React, { useEffect, useRef } from 'react'; 
import { useSelector, useDispatch } from 'react-redux';
import { hideMarqueeMessage } from '../../redux/marqueeSlice'; 
import './Marquee.css';

const Marquee = () => {
  const { message, isVisible } = useSelector((state) => state.marquee);
  const dispatch = useDispatch();
  const animationTimeoutRef = useRef(null);
  
  // 詳細日誌
  console.log('🎭 [Marquee 組件渲染]');
  console.log('  - isVisible:', isVisible);
  console.log('  - message:', message);
  console.log('  - 完整 state:', useSelector((state) => state.marquee));

  useEffect(() => {
    if (isVisible && message) {
      console.log('✅ [Marquee useEffect] 跑馬燈應該顯示！');
      const animationDuration = 15000; 

      if (animationTimeoutRef.current) {
        clearTimeout(animationTimeoutRef.current);
      }

      animationTimeoutRef.current = setTimeout(() => {
        console.log('⏰ [Marquee] 動畫完成，隱藏跑馬燈');
        dispatch(hideMarqueeMessage()); 
      }, animationDuration);
    } else {
      console.log('❌ [Marquee useEffect] 條件不滿足，不顯示跑馬燈');
      console.log('  - isVisible:', isVisible);
      console.log('  - message:', message);
    }

    return () => {
      if (animationTimeoutRef.current) {
        clearTimeout(animationTimeoutRef.current);
      }
    };
  }, [isVisible, message, dispatch]); 

  if (!isVisible || !message) {
    console.log('🚫 [Marquee render] 返回 null（不渲染）');
    return null;
  }

  console.log('🎉 [Marquee render] 渲染跑馬燈！');
  return (
    <div className="marquee-container">
      <p key={message} className="marquee-text">
        {message}
      </p>
    </div>
  );
};

export default Marquee;
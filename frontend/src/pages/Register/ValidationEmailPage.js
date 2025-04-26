import React, { useEffect, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { verifyAccount } from '../../services/AuthService';
import './ValidationEmailPage.css'; // Import the CSS file

const ValidationEmailPage = () => {
  const [searchParams] = useSearchParams();
  const [status, setStatus] = useState(null);

  const handleVerifyAccount = async () => {
    const requestId = searchParams.get('id');
    const hash = searchParams.get('hash');
    const userId = searchParams.get('user');

    if (!requestId || !hash || !userId) {
      setStatus('缺少必要的參數');
      return;
    }

    try {
      const response = await verifyAccount(requestId, hash, userId);
      if (response.data.success) {
        setStatus('驗證成功，請檢查您的電子郵件');
      } else {
        setStatus(`驗證失敗: ${response.data.error}`);
      }
    } catch (error) {
      setStatus('驗證過程中發生錯誤，請稍後再試');
    }
  };

  return (
    <div className="validation-container">
      <h1>驗證帳戶</h1>
      <button onClick={handleVerifyAccount}>驗證帳戶</button>
      {status && (
        <p className={status.includes('成功') ? 'success' : 'error'}>{status}</p>
      )}
    </div>
  );
};

export default ValidationEmailPage;
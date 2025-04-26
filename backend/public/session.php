<?php
use Illuminate\Support\Facades\Crypt;

// filepath: /Applications/XAMPP/xamppfiles/htdocs/side-project/new-project/session_status.php

// 檢查是否有 session 正在運行
if (session_status() == PHP_SESSION_NONE) {
    // 如果沒有 session，則啟動一個新的 session
    echo "No active session.";
}

// 顯示 session 狀態和 session ID
if (session_status() == PHP_SESSION_ACTIVE) {
    echo "Session is active.<br>";
    echo "Session ID: " . session_id();
} else {
    echo "No active session.";
}
?>
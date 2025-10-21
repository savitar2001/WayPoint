# 🧪 前端 JWT + Broadcast 測試指南

## 📝 測試前準備

### 1. 確保後端正常運行

```bash
cd backend

# 清除緩存
php artisan route:clear
php artisan config:clear

# 啟動 Laravel 服務
php artisan serve

# 在另一個終端啟動 Reverb（WebSocket）
php artisan reverb:start
```

### 2. 確保 Redis 運行

```bash
redis-cli ping
# 應返回: PONG
```

### 3. 啟動前端

```bash
cd frontend
npm start
```

---

## 🎯 測試步驟

### 測試 1: 登入流程 ✅

1. **打開瀏覽器開發者工具**
   - F12 或右鍵 → 檢查
   - 切換到 `Console` 標籤

2. **訪問登入頁**
   ```
   http://localhost:3000/login
   ```

3. **輸入登入資料並登入**
   
4. **檢查 Console 輸出**
   ```
   ✅ 應該看到: "JWT 模式：無需初始化 CSRF token"
   ✅ 應該看到: "Listening on private channel: user.{userId}"
   ```

5. **檢查 SessionStorage**
   - 開發者工具 → `Application` → `Session Storage`
   - 應該看到：
     - `access_token`: "eyJ0eXAiOiJKV1QiLCJhbGc..."
     - `user`: {"id": 1, "name": "...", ...}

6. **檢查 Network 請求**
   - 開發者工具 → `Network` → 找到 login 請求
   - Request Headers 應該包含:
     ```
     Content-Type: application/json
     Accept: application/json
     ```
   - Response 應該包含:
     ```json
     {
       "success": true,
       "data": {
         "access_token": "...",
         "token_type": "bearer",
         "expires_in": 3600,
         "user": {...}
       }
     }
     ```

---

### 測試 2: 認證請求 ✅

1. **登入後訪問需要認證的頁面**
   ```
   http://localhost:3000/home
   或
   http://localhost:3000/profile
   ```

2. **打開 Network 標籤**

3. **查看任何 API 請求**
   - Request Headers 應該包含:
     ```
     Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
     Accept: application/json
     Content-Type: application/json
     ```

4. **檢查是否成功**
   - 狀態碼應該是 200
   - 沒有 419 錯誤 ✅
   - 沒有 401 錯誤

---

### 測試 3: WebSocket (Broadcast) 連接 ✅

1. **登入後查看 Console**
   ```
   ✅ 應該看到: "Listening on private channel: user.{userId} via Reverb"
   ✅ 應該看到: "Listening on public channel: chat for event 'newMessage'"
   ```

2. **檢查 Network → WS (WebSocket)**
   - 應該看到 WebSocket 連接
   - Status: 101 Switching Protocols
   - Connection: Upgrade

3. **查看 WebSocket 消息**
   - 點擊 WebSocket 連接
   - 切換到 `Messages` 標籤
   - 應該看到心跳消息和訂閱確認

4. **測試頻道授權**
   - 查看 Console 是否有授權錯誤
   - 應該沒有 "Authorization error" 消息

---

### 測試 4: 發送 API 請求（創建貼文）✅

1. **訪問創建貼文頁面**

2. **創建一篇新貼文**

3. **查看 Network 標籤**
   - 找到 `createPost` 請求
   - Request Headers 應該包含:
     ```
     Authorization: Bearer {token}
     ```
   - 狀態碼應該是 200 或 201

4. **檢查其他用戶是否收到通知**
   - 如果有其他用戶登入，應該收到 WebSocket 通知
   - Console 應該顯示: "新貼文發布 (來自 Reverb): ..."

---

### 測試 5: Token 過期處理 ⏰

**注意：** Access Token 默認 60 分鐘過期，測試需要等待或修改配置

#### 快速測試方法（修改 JWT_TTL）

1. **臨時修改 JWT 有效期**
   ```bash
   # backend/.env
   JWT_TTL=1  # 改為 1 分鐘
   ```

2. **重啟 Laravel 服務**
   ```bash
   php artisan config:clear
   php artisan serve
   ```

3. **登入並等待 1 分鐘**

4. **發送任何 API 請求**

5. **檢查 Console 和 Network**
   - 第一個請求: 401 Unauthorized
   - 自動刷新: POST /api/refresh
   - 重試請求: 200 OK
   - Console 應該沒有錯誤

6. **恢復配置**
   ```bash
   # backend/.env
   JWT_TTL=60  # 恢復為 60 分鐘
   ```

---

### 測試 6: 登出流程 ✅

1. **點擊登出按鈕**

2. **檢查 Console**
   ```
   ✅ 應該看到: "Echo disconnected."
   ```

3. **檢查 SessionStorage**
   - 開發者工具 → `Application` → `Session Storage`
   - `access_token` 和 `user` 應該被清除

4. **檢查 Network**
   - 應該有 POST `/api/logout` 請求
   - Request Headers 包含:
     ```
     Authorization: Bearer {token}
     ```
   - 狀態碼: 200

5. **檢查 WebSocket**
   - 應該斷開連接

6. **嘗試訪問受保護頁面**
   - 應該自動跳轉到登入頁

---

### 測試 7: 未認證訪問 🚫

1. **清除 SessionStorage**
   - 開發者工具 → `Application` → `Session Storage`
   - 右鍵 → Clear

2. **訪問需要認證的頁面**
   ```
   http://localhost:3000/home
   ```

3. **應該發生**
   - 自動跳轉到登入頁
   - 或顯示 401 錯誤提示

---

### 測試 8: 頁面刷新 🔄

1. **登入成功**

2. **按 F5 刷新頁面**

3. **檢查**
   - ✅ Token 仍然存在（sessionStorage）
   - ✅ 仍然處於登入狀態
   - ✅ 可以正常訪問 API

---

### 測試 9: 關閉瀏覽器 🚪

1. **登入成功**

2. **完全關閉瀏覽器**（不是只關閉標籤）

3. **重新打開瀏覽器**

4. **訪問網站**

5. **檢查**
   - ✅ Token 應該被清除（sessionStorage 特性）
   - ✅ 需要重新登入

---

## 🐛 常見問題排查

### 問題 1: 登入後沒有 Token

**檢查：**
```javascript
// Console
sessionStorage.getItem('access_token');
// 如果返回 null，檢查登入響應
```

**可能原因：**
- 後端登入 API 沒有返回 token
- 前端 `setToken()` 沒有被調用

**解決：**
```javascript
// 在 AuthService.js 的 login 函數中添加 console.log
console.log('Login response:', response.data);
console.log('Token:', response.data.data.access_token);
```

---

### 問題 2: API 請求沒有 Authorization Header

**檢查：**
```javascript
// Console
console.log('Current token:', sessionStorage.getItem('access_token'));

// 檢查 Axios 攔截器
axios.interceptors.request.use((config) => {
  console.log('Request config:', config);
  console.log('Request headers:', config.headers);
  return config;
});
```

**可能原因：**
- Token 沒有存儲
- Axios 攔截器沒有正確配置

---

### 問題 3: WebSocket 無法連接

**檢查：**
1. **Reverb 是否運行**
   ```bash
   ps aux | grep reverb
   ```

2. **Console 錯誤消息**
   ```
   查找 "Authorization error" 或 "Connection refused"
   ```

3. **Token 是否有效**
   ```javascript
   console.log('Token for Echo:', sessionStorage.getItem('access_token'));
   ```

4. **authEndpoint 路徑**
   ```javascript
   // echo.js
   authEndpoint: `${process.env.REACT_APP_BACKEND_URL}/api/broadcasting/auth`
   // 注意：是 /api/broadcasting/auth 而不是 /broadcasting/auth
   ```

---

### 問題 4: 419 錯誤仍然出現

**這不應該發生！如果出現：**

1. **檢查是否有殘留的 CSRF 代碼**
   ```bash
   cd frontend/src
   grep -r "csrf" .
   grep -r "withCredentials" .
   grep -r "xsrf" .
   ```

2. **檢查後端路由**
   ```bash
   cd backend
   php artisan route:list | grep login
   # 應該是 /api/login 而不是 /login
   ```

3. **清除瀏覽器緩存**
   - Ctrl + Shift + Delete
   - 清除所有緩存

---

### 問題 5: Token 過期沒有自動刷新

**檢查：**
```javascript
// AuthService.js 的響應攔截器
axios.interceptors.response.use(
  (response) => {
    console.log('Response success:', response);
    return response;
  },
  async (error) => {
    console.log('Response error:', error.response?.status);
    // ... 刷新邏輯
  }
);
```

**可能原因：**
- 攔截器沒有正確配置
- `/api/refresh` 端點不可用

---

## ✅ 測試檢查清單

### 基本功能
- [ ] 登入成功並存儲 token
- [ ] 登入後可以訪問受保護的 API
- [ ] API 請求自動帶上 Authorization header
- [ ] 沒有 419 錯誤
- [ ] 沒有 CSRF 相關錯誤

### 進階功能
- [ ] Token 過期時自動刷新
- [ ] 刷新失敗時跳轉登入頁
- [ ] 登出清除所有數據
- [ ] WebSocket 連接成功
- [ ] WebSocket 使用 JWT 認證
- [ ] WebSocket 可以接收消息

### 存儲和狀態
- [ ] 頁面刷新後 token 仍有效
- [ ] 關閉瀏覽器後 token 清除
- [ ] 未認證時無法訪問受保護頁面

### 安全性
- [ ] Token 存儲在 sessionStorage
- [ ] Token 不會出現在 URL 中
- [ ] Authorization header 正確設置
- [ ] 無 CSRF token 依賴

---

## 📊 測試報告模板

```
測試日期: 2025-10-17
測試環境: 
- Frontend: http://localhost:3000
- Backend: http://localhost:8000
- Reverb: ws://localhost:8080

測試結果:
✅ 登入流程
✅ 認證請求
✅ WebSocket 連接
✅ 創建貼文
⏳ Token 過期處理（需等待 60 分鐘）
✅ 登出流程
✅ 未認證訪問
✅ 頁面刷新
✅ 關閉瀏覽器

問題記錄:
1. [描述問題]
   - 原因: [...]
   - 解決: [...]

結論: ✅ 所有測試通過，JWT 認證系統運作正常
```

---

## 🎉 測試完成

如果所有測試都通過：
- ✅ 前端完全使用 JWT 認證
- ✅ 不再有 CSRF 問題
- ✅ WebSocket 使用 JWT 認證
- ✅ 自動處理 token 過期

**恭喜！你的應用現在使用現代化的 JWT 認證系統！** 🚀

---

**需要幫助？** 查看 `FRONTEND_JWT_UPDATE_SUMMARY.md` 了解更多細節

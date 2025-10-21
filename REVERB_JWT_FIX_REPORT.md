# 🔊 Reverb 廣播 JWT 認證修復報告

**修復日期：** 2025年10月20日  
**問題：** Reverb 廣播在從 CSRF 改為 JWT 後失效

---

## ❌ 原問題

### 衝突的認證機制

**BroadcastServiceProvider.php：**
```php
// ❌ 使用 web middleware（Session + CSRF）
Broadcast::routes(['middleware' => ['web']]);
```

**routes/api.php：**
```php
// ✅ 使用 auth:api middleware（JWT）
Route::middleware('auth:api')->group(function () {
    Route::post('/broadcasting/auth', function (Request $request) {
        return Broadcast::auth($request);
    });
});
```

**結果：** 兩個認證端點衝突，導致廣播授權失敗！

---

## ✅ 解決方案

### 修改 1: BroadcastServiceProvider.php

**移除 `Broadcast::routes()` 的自動註冊：**

```php
// 修改前
public function boot(): void
{
    Broadcast::routes(['middleware' => ['web']]); // ❌ 使用 Session
    require base_path('routes/channels.php');
}

// 修改後
public function boot(): void
{
    // JWT 認證：不使用 Broadcast::routes()，改在 routes/api.php 中手動註冊
    // 這樣可以使用 'auth:api' middleware 而不是 'web' middleware
    \Illuminate\Support\Facades\Log::info('BroadcastServiceProvider boot method called (JWT mode).');
    
    require base_path('routes/channels.php');
}
```

### 修改 2: routes/api.php（已存在，無需修改）

```php
Route::middleware('auth:api')->group(function () {
    // Broadcasting 認證（WebSocket 頻道授權）- 使用 JWT
    Route::post('/broadcasting/auth', function (Request $request) {
        return Broadcast::auth($request);
    });
});
```

### 修改 3: 前端 echo.js（已正確配置）

```javascript
echoInstance = new Echo({
    broadcaster: 'reverb',
    key: reverbAppKey,
    wsHost: reverbHost,
    wsPort: reverbPort,
    authEndpoint: `${process.env.REACT_APP_BACKEND_URL}/api/broadcasting/auth`,
    auth: {
        headers: {
            Authorization: `Bearer ${getToken()}`, // ✅ JWT Token
            Accept: 'application/json',
        }
    },
    authorizer: (channel, options) => {
        return {
            authorize: (socketId, callback) => {
                const token = getToken();
                
                axios.post(options.authEndpoint, {
                    socket_id: socketId,
                    channel_name: channel.name
                }, {
                    headers: {
                        'Authorization': `Bearer ${token}`, // ✅ JWT Token
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => callback(null, response.data))
                .catch(error => {
                    console.error('Broadcasting Authorization error:', error);
                    callback(error);
                });
            }
        };
    }
});
```

---

## 🔄 完整的廣播認證流程（JWT）

```
【前端】
用戶登入成功
    ↓
存儲 JWT Token 到 sessionStorage
    ↓
初始化 Echo（傳入 userId）
    ↓
Echo 訂閱私有頻道 'private-user.{userId}'
    ↓
【WebSocket 認證請求】
POST /api/broadcasting/auth
Headers: Authorization: Bearer {jwt_token}
Body: { socket_id, channel_name }
    ↓
【後端】
routes/api.php → auth:api middleware
    ↓
JWT Token 驗證通過
    ↓
Broadcast::auth($request) → channels.php
    ↓
檢查 $user 是否為 User Model 實例 ✅
    ↓
檢查 $user->id === $id ✅
    ↓
返回授權成功
    ↓
【前端】
WebSocket 連接建立成功 ✅
開始監聽事件 ✅
```

---

## 🧪 測試步驟

### 1. 確認 Reverb 運行

```bash
docker exec my-backend-app ps aux | grep reverb
```

**應看到：**
```
php artisan reverb:start --host=0.0.0.0 --port=8080 --debug
```

### 2. 前端登入並檢查 Console

```javascript
// 1. 登入成功後，應看到
✅ "Listening on private channel: user.{userId} via Reverb"

// 2. 如果有授權錯誤，會看到
❌ "Broadcasting Authorization error: ..."
```

### 3. 檢查後端日誌

```bash
# 查看廣播授權日誌
docker exec my-backend-app tail -f /var/www/html/storage/logs/laravel-$(date +"%Y-%m-%d").log | grep Broadcasting
```

**成功的日誌應該是：**
```
[2025-10-20] local.INFO: --- Broadcasting Auth Attempt (JWT) ---
[2025-10-20] local.INFO: Requested Channel: private-user.1
[2025-10-20] local.INFO: Authenticated User ID (via JWT): 1
[2025-10-20] local.INFO: Channel User ID Parameter: 1
[2025-10-20] local.INFO: Authorization Result: AUTHORIZED
[2025-10-20] local.INFO: --- End Broadcasting Auth Attempt (JWT) ---
```

### 4. 測試廣播事件

**建議測試場景：**
1. **創建貼文**
   - 用戶 A 登入並創建貼文
   - 用戶 A 的訂閱者（用戶 B）應該收到通知
   - 檢查用戶 B 的前端 Console

2. **檢查 Console 輸出**
   ```javascript
   // 應該看到
   ✅ "新貼文發布 (來自 Reverb): ..."
   ✅ Marquee 跑馬燈顯示通知
   ```

---

## 🔍 故障排除

### 問題 1: "User is NOT authenticated via JWT"

**原因：** JWT Token 未正確傳遞

**檢查：**
```javascript
// 前端 echo.js
const getToken = () => {
    const token = sessionStorage.getItem('access_token');
    console.log('Token for broadcasting:', token); // 檢查 token 是否存在
    return token;
};
```

**解決：**
- 確保登入成功後 token 存在於 sessionStorage
- 確保 Echo 在登入後才初始化

---

### 問題 2: "Authorization Result: DENIED"

**原因：** 用戶 ID 不匹配

**檢查：**
```javascript
// 前端
console.log('Subscribing to channel:', `user.${userId}`);

// 後端日誌
// 應該看到 User ID === Channel ID
```

**解決：**
- 確保傳入正確的 userId
- 檢查 Redux 或 sessionStorage 中的用戶 ID

---

### 問題 3: Reverb 未運行

**症狀：** WebSocket 連接失敗

**檢查：**
```bash
docker exec my-backend-app ps aux | grep reverb
```

**解決：**
```bash
# 重啟容器
docker-compose restart backend

# 或手動啟動 Reverb
docker exec my-backend-app php artisan reverb:start --host=0.0.0.0 --port=8080 --debug &
```

---

### 問題 4: CORS 錯誤

**檢查 backend/config/cors.php：**
```php
'paths' => [
    'api/*',
    'broadcasting/auth', // ✅ 確保包含
],

'allowed_headers' => [
    'Authorization', // ✅ 必須包含
    'Content-Type',
    'Accept',
],
```

---

## 📊 認證方式對比

| 項目 | Session + CSRF | JWT |
|------|---------------|-----|
| **認證端點** | `/broadcasting/auth` (web) | `/api/broadcasting/auth` (api) |
| **Middleware** | `web` | `auth:api` |
| **認證方式** | Cookie + CSRF Token | Authorization: Bearer {token} |
| **前端配置** | withCredentials: true | Authorization header |
| **優點** | 自動處理 Cookie | 無狀態，跨域友好 |
| **缺點** | CSRF 問題，跨域複雜 | 需手動管理 Token |

---

## ✅ 檢查清單

### 後端
- [x] BroadcastServiceProvider 不使用 `Broadcast::routes()`
- [x] routes/api.php 有 `/api/broadcasting/auth` 路由
- [x] 路由使用 `auth:api` middleware
- [x] channels.php 正確檢查 User 實例
- [x] Reverb 正在運行
- [x] CORS 配置包含 Authorization header

### 前端
- [x] Echo 使用正確的 authEndpoint
- [x] 在 auth.headers 中包含 JWT Token
- [x] 在 authorizer 中包含 JWT Token
- [x] Token 從 sessionStorage 正確讀取
- [x] Echo 在登入後才初始化

---

## 🎯 總結

### 核心變更
1. **移除 `Broadcast::routes()` 自動註冊**
   - 避免與手動註冊的 JWT 路由衝突
   
2. **統一使用 JWT 認證**
   - 所有 API（包括 broadcasting/auth）都使用 `auth:api`
   
3. **前端正確配置 JWT Token**
   - Echo 配置中包含 Authorization header

### 關鍵點
- **Session-based**: 使用 `Broadcast::routes(['middleware' => ['web']])`
- **JWT-based**: 手動在 `routes/api.php` 中註冊並使用 `auth:api`
- **不能混用！** 必須選擇一種認證方式

---

**修復完成！Reverb 廣播現在應該可以正常使用 JWT 認證了！** 🎉

**下一步：** 測試創建貼文並確認訂閱者收到通知

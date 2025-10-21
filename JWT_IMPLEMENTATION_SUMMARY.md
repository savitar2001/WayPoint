# JWT 認證系統實施完成 ✅

## 📋 已完成的更改

### 1. ✅ 安裝 JWT 套件
- 安裝了 `tymon/jwt-auth` 套件
- 發布了配置文件到 `config/jwt.php`
- 生成了 JWT 密鑰（已保存在 `.env` 中的 `JWT_SECRET`）

### 2. ✅ 更新 User Model
- 文件：`app/Models/User.php`
- 實現了 `JWTSubject` interface
- 添加了 `getJWTIdentifier()` 方法
- 添加了 `getJWTCustomClaims()` 方法

### 3. ✅ 配置認證 Guard
- 文件：`config/auth.php`
- 默認 guard 改為 `api`
- 添加了 `api` guard，使用 `jwt` driver
- 保留了 `sanctum` 和 `session` guard 以向後兼容

### 4. ✅ 修改 LoginService
- 文件：`app/Services/Auth/LoginService.php`
- 添加了 `generateToken()` 方法生成 JWT
- 保留了 `startSession()` 方法以向後兼容
- 返回完整的用戶信息和 token 資訊

### 5. ✅ 更新 LoginController
- 文件：`app/Http/Controllers/Auth/LoginController.php`
- 改用 `generateToken()` 而非 `startSession()`
- 返回格式包含：
  - `access_token`: JWT token
  - `token_type`: "bearer"
  - `expires_in`: 過期時間（秒）
  - `user`: 用戶信息

### 6. ✅ 增強 LogoutController
- 文件：`app/Http/Controllers/Auth/LogoutController.php`
- 添加了 `logout()` 方法將 token 加入黑名單
- 添加了 `refresh()` 方法刷新 token
- 添加了 `me()` 方法獲取當前用戶信息

### 7. ✅ 重構 API Routes
- 文件：`routes/api.php`
- 分為公開路由和需要認證的路由
- 需要認證的路由使用 `auth:api` middleware
- 添加了新的認證路由：
  - `POST /api/login` - 登入
  - `POST /api/logout` - 登出
  - `POST /api/refresh` - 刷新 token
  - `GET /api/me` - 獲取當前用戶

### 8. ✅ 優化 CORS 配置
- 文件：`config/cors.php`
- 移除了 CSRF 相關路徑（不再需要）
- 確保 `Authorization` header 被允許
- 設置 `supports_credentials` 為 `false`
- 添加了 `exposed_headers` 包含 `Authorization`

## 🔧 環境變量

確保 `.env` 文件包含以下配置：

```env
# JWT 配置（已自動生成）
JWT_SECRET=W9PFIaKtOinEfgeYY1Tpj2ug7wW0rFLJZ6A0gyGaZ9AuJOP35zI7oA3hRvk5egaM
JWT_TTL=60                    # Access token 有效期（分鐘）
JWT_REFRESH_TTL=20160         # Refresh token 有效期（分鐘，14天）
JWT_ALGO=HS256                # 加密算法
JWT_BLACKLIST_ENABLED=true    # 啟用黑名單
JWT_BLACKLIST_GRACE_PERIOD=0  # 黑名單寬限期（秒）

# 認證配置
AUTH_GUARD=api                # 默認使用 JWT guard
```

## 📡 API 端點

### 公開端點（無需認證）

```
POST   /api/login                          # 登入
POST   /api/register                       # 註冊
POST   /api/verify                         # 驗證郵箱
GET    /api/getPost/{userId}/{postId}/{tag} # 獲取貼文
GET    /api/getUserInformation/{userId}    # 獲取用戶信息
GET    /api/searchByName/{name}            # 搜尋用戶
```

### 需要 JWT 認證的端點

```
POST   /api/logout                         # 登出
POST   /api/refresh                        # 刷新 token
GET    /api/me                             # 獲取當前用戶

# 貼文相關
POST   /api/createPost                     # 創建貼文
DELETE /api/deletePost/{userId}/{postId}   # 刪除貼文
POST   /api/commentOnPost                  # 評論貼文
POST   /api/likePost                       # 點讚貼文

# 用戶相關
POST   /api/addSubscriber                  # 關注用戶
POST   /api/createAvatar                   # 上傳頭像
DELETE /api/removeSubscriber/{followerId}/{subscriberId}

# 通知相關
GET    /api/getUnreadNotifications/{notifiableId}/{type}
POST   /api/markNotificationAsRead
POST   /api/markAllNotificationsAsRead
```

## 🧪 測試 API

### 1. 測試登入

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

成功響應：
```json
{
  "success": true,
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": {
      "id": 1,
      "name": "Test User",
      "email": "test@example.com",
      "avatar_url": null
    }
  }
}
```

### 2. 測試認證端點

```bash
# 使用獲取的 token
TOKEN="your_jwt_token_here"

curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### 3. 測試創建貼文

```bash
curl -X POST http://localhost:8000/api/createPost \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "content": "這是我的第一篇貼文！",
    "userId": 1
  }'
```

### 4. 測試登出

```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### 5. 測試刷新 Token

```bash
curl -X POST http://localhost:8000/api/refresh \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

## ⚠️ 重要注意事項

### 1. CSRF Token 已移除
- ✅ JWT 不需要 CSRF token
- ✅ 不會再出現 419 錯誤
- ✅ 簡化了前後端交互

### 2. Session 仍然保留
- `startSession()` 方法仍然存在
- 可以支持混合認證（如需要）
- 建議逐步遷移到 JWT

### 3. 登入嘗試限制
- ✅ 仍然有效
- ✅ 5次失敗後鎖定1小時
- ✅ 返回剩餘嘗試次數

### 4. Token 黑名單
- 登出時 token 會加入黑名單
- 黑名單的 token 無法使用
- Redis 用於存儲黑名單（需要確保 Redis 運行）

### 5. Token 過期處理
- Access token 默認 60 分鐘過期
- 前端需要實現自動刷新機制
- 或者在 token 過期前提醒用戶重新登入

## 🔍 故障排除

### 問題 1: "Token could not be parsed from the request"
**原因：** Authorization header 格式錯誤
**解決：** 確保使用 `Authorization: Bearer {token}` 格式

### 問題 2: "The token has been blacklisted"
**原因：** Token 已經登出
**解決：** 需要重新登入獲取新 token

### 問題 3: "Token has expired"
**原因：** Token 已過期
**解決：** 使用 `/api/refresh` 刷新 token，或重新登入

### 問題 4: CORS 錯誤
**原因：** 前端域名未在 CORS 配置中
**解決：** 在 `config/cors.php` 的 `allowed_origins` 中添加前端域名

### 問題 5: Redis 連接失敗
**原因：** Redis 未運行或配置錯誤
**解決：**
```bash
# 檢查 Redis 是否運行
redis-cli ping

# 如果未運行，啟動 Redis
redis-server
```

## 📚 相關文件

- [前端整合指南](./JWT_FRONTEND_INTEGRATION.md)
- [JWT 配置文件](./backend/config/jwt.php)
- [認證配置](./backend/config/auth.php)
- [CORS 配置](./backend/config/cors.php)

## 🎉 下一步

1. ✅ 後端 JWT 實施完成
2. 🔄 前端整合（參考 `JWT_FRONTEND_INTEGRATION.md`）
3. 🧪 完整測試登入/登出流程
4. 🚀 部署到生產環境

## 🔐 安全建議

1. **保護 JWT_SECRET**
   - 不要提交到版本控制
   - 使用強密碼生成器生成
   - 定期輪換密鑰

2. **設置合理的過期時間**
   - Access token: 15-60 分鐘
   - Refresh token: 7-30 天

3. **HTTPS**
   - 生產環境必須使用 HTTPS
   - 防止 token 被竊取

4. **Rate Limiting**
   - 限制登入 API 的請求頻率
   - 防止暴力破解

5. **監控和日誌**
   - 記錄所有認證失敗
   - 監控異常登入行為

---

**實施完成日期：** 2025年10月17日
**實施者：** GitHub Copilot
**版本：** 1.0.0

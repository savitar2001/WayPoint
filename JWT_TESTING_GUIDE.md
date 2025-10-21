# JWT 認證系統測試指南

## 🧪 測試前準備

### 1. 確保 Redis 正在運行（JWT 黑名單需要）

```bash
# 啟動 Redis（macOS）
brew services start redis

# 或手動啟動
redis-server

# 檢查 Redis 是否運行
redis-cli ping
# 應該返回 PONG
```

### 2. 清除緩存

```bash
cd backend
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### 3. 檢查 .env 配置

確保以下配置正確：

```env
# JWT 配置
JWT_SECRET=W9PFIaKtOinEfgeYY1Tpj2ug7wW0rFLJZ6A0gyGaZ9AuJOP35zI7oA3hRvk5egaM
JWT_TTL=60
JWT_REFRESH_TTL=20160
JWT_ALGO=HS256
JWT_BLACKLIST_ENABLED=true

# Redis 配置（JWT 黑名單需要）
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# 數據庫配置
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

## 📝 測試步驟

### 測試 1: 健康檢查

```bash
curl http://localhost:8000/api/health-check
```

期望響應：
```json
{
  "status": "ok",
  "timestamp": "2025-10-17 ...",
  "php_version": "8.x",
  "laravel_version": "12.x",
  "environment": "local",
  "database": "connected",
  "redis": "connected",
  "storage_writable": "yes",
  "cache_writable": "yes"
}
```

### 測試 2: 用戶註冊（如需要）

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name": "測試用戶",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'
```

### 測試 3: 用戶登入（獲取 JWT Token）✨

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }'
```

**期望成功響應：**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "token_type": "bearer",
    "expires_in": 3600,
    "user": {
      "id": 1,
      "name": "測試用戶",
      "email": "test@example.com",
      "avatar_url": null
    }
  }
}
```

**期望失敗響應（密碼錯誤）：**
```json
{
  "success": false,
  "error": "密碼錯誤",
  "remaining_attempts": 4
}
```

### 測試 4: 使用 Token 獲取用戶信息 ✨

```bash
# 將上一步獲得的 token 替換到 YOUR_TOKEN_HERE
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGc..."

curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**期望響應：**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "測試用戶",
    "email": "test@example.com",
    "avatar_url": null,
    "verified": 1
  }
}
```

### 測試 5: 創建貼文（需要認證）✨

```bash
curl -X POST http://localhost:8000/api/createPost \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "content": "這是我使用 JWT 認證創建的第一篇貼文！",
    "userId": 1
  }'
```

### 測試 6: 測試未認證訪問（應該返回 401）

```bash
curl -X POST http://localhost:8000/api/createPost \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "content": "沒有 token",
    "userId": 1
  }'
```

**期望響應：**
```json
{
  "message": "Unauthenticated."
}
```
狀態碼：401

### 測試 7: 刷新 Token ✨

```bash
curl -X POST http://localhost:8000/api/refresh \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**期望響應：**
```json
{
  "success": true,
  "access_token": "新的 token...",
  "token_type": "bearer",
  "expires_in": 3600
}
```

### 測試 8: 登出（Token 失效）✨

```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**期望響應：**
```json
{
  "success": true,
  "message": "登出成功"
}
```

### 測試 9: 使用失效的 Token

```bash
# 使用剛才登出的 token
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

**期望響應：**
```json
{
  "message": "The token has been blacklisted"
}
```
狀態碼：401

## 🔍 常見錯誤排查

### 錯誤 1: "Connection refused [tcp://127.0.0.1:6379]"
**原因：** Redis 未運行
**解決：**
```bash
brew services start redis
# 或
redis-server
```

### 錯誤 2: "Token could not be parsed from the request"
**原因：** Authorization header 格式錯誤
**解決：** 確保格式為 `Authorization: Bearer {token}`

### 錯誤 3: "Class 'Tymon\JWTAuth\Providers\LaravelServiceProvider' not found"
**原因：** JWT 套件未正確安裝
**解決：**
```bash
composer install
php artisan config:clear
```

### 錯誤 4: "The MAC is invalid"
**原因：** JWT_SECRET 可能改變或不匹配
**解決：**
```bash
php artisan jwt:secret --force
php artisan config:clear
```

### 錯誤 5: 419 CSRF Token Mismatch（不應出現）
**原因：** 可能還在使用舊的 session 認證
**解決：** 確保：
- 前端使用 `Authorization: Bearer {token}`
- 不要發送 CSRF token
- 檢查 routes 使用 `auth:api` 而非 `auth:sanctum`

## ✅ 測試檢查清單

- [ ] Redis 正在運行
- [ ] 健康檢查通過
- [ ] 登入成功並獲取 token
- [ ] Token 格式正確（3段用.分隔）
- [ ] 使用 token 可以訪問受保護的端點
- [ ] 不帶 token 返回 401
- [ ] 錯誤 token 返回 401
- [ ] 登出後 token 失效
- [ ] 刷新 token 成功
- [ ] 密碼錯誤時返回剩餘嘗試次數
- [ ] CORS 正常（檢查 Authorization header）

## 🎯 使用 Postman 測試

### 1. 創建新的 Collection

### 2. 設置環境變量
- `base_url`: http://localhost:8000/api
- `token`: (登入後會自動設置)

### 3. 登入請求配置

**Request:**
- Method: POST
- URL: `{{base_url}}/login`
- Headers:
  - `Content-Type`: application/json
  - `Accept`: application/json
- Body (raw JSON):
```json
{
  "email": "test@example.com",
  "password": "password123"
}
```

**Tests (自動保存 token):**
```javascript
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    if (jsonData.success && jsonData.data.access_token) {
        pm.environment.set("token", jsonData.data.access_token);
        console.log("Token saved:", jsonData.data.access_token);
    }
}
```

### 4. 認證請求配置

**Request:**
- Method: GET
- URL: `{{base_url}}/me`
- Headers:
  - `Authorization`: Bearer {{token}}
  - `Accept`: application/json

## 📊 性能測試

使用 Apache Bench 測試：

```bash
# 測試登入端點
ab -n 100 -c 10 -p login.json -T application/json http://localhost:8000/api/login

# login.json 內容：
# {"email":"test@example.com","password":"password123"}
```

## 🎉 測試成功標準

所有以下測試通過：
1. ✅ 登入獲取 token
2. ✅ 使用 token 訪問受保護端點
3. ✅ 刷新 token 成功
4. ✅ 登出使 token 失效
5. ✅ 未認證請求返回 401
6. ✅ 登入失敗返回錯誤和剩餘次數
7. ✅ CORS 配置正確

---

**測試準備日期：** 2025年10月17日
**預計測試時間：** 15-30 分鐘

# 🚀 JWT 認證快速開始指南

## 📖 閱讀順序

1. **本文件** - 快速開始（5 分鐘）
2. `JWT_COMPLETE_REPORT.md` - 完整實施報告（詳細了解所有變更）
3. `JWT_TESTING_GUIDE.md` - API 測試指南（測試後端）
4. `JWT_FRONTEND_INTEGRATION.md` - 前端整合指南（整合前端）

---

## ⚡ 5 分鐘快速開始

### 步驟 1: 啟動 Redis（必須）

```bash
# macOS
brew services start redis

# 或手動啟動
redis-server

# 驗證
redis-cli ping
# 應返回: PONG
```

### 步驟 2: 清除緩存

```bash
cd backend
php artisan route:clear
php artisan config:clear
php artisan cache:clear
```

### 步驟 3: 測試登入 API

```bash
# 使用現有的測試用戶登入
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "your_test_email@example.com",
    "password": "your_password"
  }'
```

**成功響應範例：**
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

### 步驟 4: 使用 Token 訪問 API

```bash
# 將上一步獲得的 token 替換到這裡
TOKEN="eyJ0eXAiOiJKV1QiLCJhbGc..."

# 獲取當前用戶信息
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

### 步驟 5: 測試登出

```bash
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

---

## ✅ 成功標準

- ✅ Redis 返回 PONG
- ✅ 登入返回 token
- ✅ 使用 token 成功訪問 /api/me
- ✅ 登出成功
- ✅ 登出後 token 失效

---

## 🎯 核心概念

### JWT 認證流程

```
1️⃣ 登入 (/api/login)
   ↓
2️⃣ 獲得 Token
   ↓
3️⃣ 存儲 Token (前端: sessionStorage)
   ↓
4️⃣ 每次請求帶上 Token (Header: Authorization: Bearer {token})
   ↓
5️⃣ 登出時失效 Token (/api/logout)
```

### 與之前的區別

| 之前（Session + CSRF） | 現在（JWT） |
|----------------------|-----------|
| ❌ 需要 CSRF Token | ✅ 不需要 |
| ❌ 419 錯誤 | ✅ 不會出現 |
| 有狀態（依賴 Session） | 無狀態 |
| Cookie 自動管理 | 需要手動管理 Token |

---

## 🔧 環境配置檢查

### 確認 .env 文件包含：

```env
# JWT 配置（應該已自動生成）
JWT_SECRET=W9PFIaKtOinEfgeYY1Tpj2ug7wW0rFLJZ6A0gyGaZ9AuJOP35zI7oA3hRvk5egaM
JWT_TTL=60
JWT_REFRESH_TTL=20160
JWT_ALGO=HS256
JWT_BLACKLIST_ENABLED=true

# 認證配置
AUTH_GUARD=api

# Redis 配置
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
```

---

## 📡 新增的 API 端點

### 認證相關

| 端點 | 方法 | 認證 | 說明 |
|------|-----|------|------|
| `/api/login` | POST | ❌ | 登入，獲取 token |
| `/api/logout` | POST | ✅ | 登出，token 失效 |
| `/api/refresh` | POST | ✅ | 刷新 token |
| `/api/me` | GET | ✅ | 獲取當前用戶 |

### 認證標記說明
- ❌ = 不需要認證（公開端點）
- ✅ = 需要認證（需要帶 Bearer Token）

---

## 🔥 常見問題快速解決

### 問題 1: "Connection refused [tcp://127.0.0.1:6379]"
```bash
# 解決：啟動 Redis
brew services start redis
```

### 問題 2: 仍然出現 419 錯誤
**檢查：**
- 前端是否使用 `Authorization: Bearer {token}` 而不是 CSRF token？
- 是否訪問的是 `/api/*` 路由？
- 路由是否使用 `auth:api` middleware？

### 問題 3: "Token could not be parsed"
**檢查：**
- Header 格式是否為 `Authorization: Bearer {token}`？
- Token 前面是否有 `Bearer ` 前綴（注意空格）？

### 問題 4: "Unauthenticated"
**可能原因：**
- Token 過期（默認 60 分鐘）
- Token 已登出（在黑名單中）
- Token 格式錯誤

**解決：**
- 重新登入獲取新 token
- 或使用 `/api/refresh` 刷新 token

---

## 📱 Postman 快速配置

### 1. 導入環境變量

創建新環境，添加變量：
- `base_url`: `http://localhost:8000/api`
- `token`: (登入後自動設置)

### 2. 登入請求

**POST** `{{base_url}}/login`

Body (raw JSON):
```json
{
  "email": "test@example.com",
  "password": "password123"
}
```

Tests 腳本（自動保存 token）:
```javascript
if (pm.response.code === 200) {
    var jsonData = pm.response.json();
    if (jsonData.success && jsonData.data.access_token) {
        pm.environment.set("token", jsonData.data.access_token);
    }
}
```

### 3. 認證請求

**GET** `{{base_url}}/me`

Headers:
- `Authorization`: `Bearer {{token}}`
- `Accept`: `application/json`

---

## 🎨 前端快速整合（React）

### 最小化實現

```javascript
// 1. 登入
const login = async (email, password) => {
  const res = await fetch('http://localhost:8000/api/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  const data = await res.json();
  sessionStorage.setItem('token', data.data.access_token);
};

// 2. 訪問受保護 API
const fetchProtectedData = async () => {
  const token = sessionStorage.getItem('token');
  const res = await fetch('http://localhost:8000/api/me', {
    headers: { 
      'Authorization': `Bearer ${token}`,
      'Accept': 'application/json'
    }
  });
  return await res.json();
};

// 3. 登出
const logout = async () => {
  const token = sessionStorage.getItem('token');
  await fetch('http://localhost:8000/api/logout', {
    method: 'POST',
    headers: { 'Authorization': `Bearer ${token}` }
  });
  sessionStorage.removeItem('token');
};
```

完整的前端整合請參考：`JWT_FRONTEND_INTEGRATION.md`

---

## 📚 延伸閱讀

- **完整實施報告**: `JWT_COMPLETE_REPORT.md`
  - 查看所有修改的文件和代碼
  - 了解技術細節和設計決策

- **API 測試指南**: `JWT_TESTING_GUIDE.md`
  - 完整的 API 測試步驟
  - curl 命令範例
  - 故障排除

- **前端整合指南**: `JWT_FRONTEND_INTEGRATION.md`
  - Axios 配置
  - React 組件範例
  - 受保護路由實現

---

## ✨ 你已經完成了什麼

- ✅ JWT 套件已安裝並配置
- ✅ User Model 已更新
- ✅ 認證 Guard 已配置為 JWT
- ✅ LoginService 已添加 generateToken()
- ✅ LogoutController 已實現登出、刷新、獲取用戶
- ✅ API Routes 已重構
- ✅ CORS 已優化
- ✅ 完整文檔已創建

## 🎯 下一步

1. 按照上面的步驟測試 API ✅
2. 整合前端（參考 `JWT_FRONTEND_INTEGRATION.md`）
3. 完整測試所有功能
4. 部署到生產環境

---

**需要幫助？** 參考 `JWT_TESTING_GUIDE.md` 中的故障排除章節

**🎉 祝你使用愉快！不再有 419 錯誤！**

# 🔧 CORS 問題修復指南

## 問題診斷

你遇到的錯誤：
- `Network Error` (Axios)
- `Status: 0` (請求未到達後端)
- `Access-Control-Allow-Origin` 錯誤
- 後端返回 `Status 500`

## 已完成的修復

### 1. ✅ CORS 配置優化 (`backend/config/cors.php`)
- 使用環境變數動態設定 allowed_origins
- 允許所有 HTTP 方法 (`*`)
- 允許所有 headers (`*`)
- 修正路徑配置，移除重複的路徑定義

### 2. ✅ 中間件配置修正 (`backend/bootstrap/app.php`)
- 合併重複的 `withMiddleware` 調用
- 添加 localhost 到 trusted hosts
- 正確設定 proxy headers for Render

### 3. ✅ 錯誤處理增強 (`LoginController.php`)
- 添加 try-catch 錯誤處理
- 增加詳細日誌記錄
- 返回更友好的錯誤訊息

### 4. ✅ 測試端點 (`/api/test-cors`)
- 添加專門的 CORS 測試端點
- 可用於快速驗證 CORS 配置

## 🚀 部署步驟

### 步驟 1: 提交代碼到 Git

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/side-project/new-project

git add .
git commit -m "fix: optimize CORS configuration and error handling"
git push origin main
```

### 步驟 2: 設定 Render 環境變數

在 Render Dashboard 的 **Environment Groups** → `WayPoint-env` 中添加/確認：

#### Backend 環境變數：
```bash
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_APP_KEY_HERE
APP_URL=https://waypoint-backend-122x.onrender.com

# CORS 設定
CORS_ALLOWED_ORIGINS=https://waypoint-frontend-zdei.onrender.com,http://localhost:3000

# 資料庫設定
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

# Redis 設定
REDIS_HOST=your-redis-host
REDIS_PASSWORD=your-redis-password
REDIS_PORT=6379

# JWT 設定
JWT_SECRET=your-jwt-secret
JWT_TTL=60

# 其他
LOG_CHANNEL=stack
LOG_LEVEL=error
```

#### Frontend 環境變數：
```bash
REACT_APP_BACKEND_URL=https://waypoint-backend-122x.onrender.com
REACT_APP_REVERB_HOST=waypoint-backend-122x.onrender.com
REACT_APP_REVERB_PORT=443
REACT_APP_REVERB_SCHEME=https
REACT_APP_REVERB_APP_KEY=your-reverb-key
```

### 步驟 3: 重新部署服務

1. **後端**：
   - 前往 Render Dashboard → WayPoint-backend
   - 點擊 "Manual Deploy" → "Clear build cache & deploy"
   - 等待部署完成（約 5-10 分鐘）

2. **前端**：
   - 前往 Render Dashboard → WayPoint-frontend
   - 點擊 "Manual Deploy" → "Clear build cache & deploy"
   - 等待部署完成（約 3-5 分鐘）

### 步驟 4: 驗證部署

#### 4.1 測試後端健康檢查
```bash
curl https://waypoint-backend-122x.onrender.com/api/health-check
```

應該返回：
```json
{
  "status": "ok",
  "timestamp": "...",
  "database": "connected",
  "redis": "connected"
}
```

#### 4.2 測試 CORS 端點
```bash
curl -X POST https://waypoint-backend-122x.onrender.com/api/test-cors \
  -H "Content-Type: application/json" \
  -H "Origin: https://waypoint-frontend-zdei.onrender.com" \
  -d '{"test":"data"}'
```

應該返回：
```json
{
  "success": true,
  "message": "CORS is working!",
  "received_data": {...}
}
```

#### 4.3 使用測試工具
打開 `test-cors.html` 文件並執行所有測試

### 步驟 5: 檢查日誌

如果還有問題：

1. **查看後端日誌**：
   - Render Dashboard → WayPoint-backend → Logs
   - 尋找 "Login attempt" 或錯誤訊息

2. **查看前端 Console**：
   - 打開 https://waypoint-frontend-zdei.onrender.com
   - F12 → Console
   - 查看 API_BASE_URL 是否正確

## 🐛 故障排除

### 問題 1: 仍然出現 Network Error

**可能原因**：
- 後端服務未啟動
- CORS 環境變數未正確設定

**解決方案**：
```bash
# 檢查後端是否運行
curl -I https://waypoint-backend-122x.onrender.com/api/health-check

# 如果返回 502/503，表示後端未啟動
# 檢查 Render logs 找出原因
```

### 問題 2: 403 Forbidden

**可能原因**：
- CSRF token 問題（但我們已經使用 JWT，不應該有這個問題）
- 路由未正確設定

**解決方案**：
檢查 `routes/api.php` 確保 `/login` 路由存在且未受保護

### 問題 3: 500 Internal Server Error

**可能原因**：
- 資料庫連接失敗
- JWT 配置錯誤
- 缺少必要的環境變數

**解決方案**：
```bash
# 檢查後端日誌
# 確保所有必要的環境變數都已設定
```

### 問題 4: CORS Preflight 失敗

**可能原因**：
- `allowed_origins` 未包含前端 URL
- `allowed_headers` 未包含必要的 header

**解決方案**：
確認 `config/cors.php` 中的設定正確

## 📋 最終檢查清單

部署後檢查：

- [ ] 後端健康檢查正常 (`/api/health-check`)
- [ ] CORS 測試端點正常 (`/api/test-cors`)
- [ ] 前端 Console 顯示正確的 API_BASE_URL
- [ ] 瀏覽器 Network 標籤中 OPTIONS 請求返回 200
- [ ] POST /api/login 請求能夠到達後端（即使失敗也應該有回應）
- [ ] 後端日誌沒有錯誤（除了預期的登入失敗）

## 📞 如果仍有問題

提供以下資訊：

1. **後端日誌**（從 Render Dashboard）
2. **瀏覽器 Console 錯誤**（完整訊息）
3. **Network 標籤截圖**（顯示 login 請求的詳情）
4. **test-cors.html 的測試結果**

這樣可以更精確地定位問題！

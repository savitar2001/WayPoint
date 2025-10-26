## 專案簡介
# WayPoint
WayPoint 是一個基於網頁的社群平台基礎架構，旨在提供一套完整的社群服務核心功能，讓開發者能快速建立類似社群媒體的應用程式。
[WayPoint前端服務](https://waypoint-frontend-zdei.onrender.com)
體驗帳號:
* 帳號：abcdefg@gmail.com
* 密碼：test123

## 功能與服務
* **用戶帳號管理服務**
    * 註冊、登入、忘記密碼、登出
    * 追蹤與取消追蹤其他用戶
    * 移除粉絲、檢視追蹤者與粉絲列表
* **貼文管理服務**
    * 創建、編輯與刪除貼文
    * 貼文按讚與取消按讚
    * 發布與回覆貼文留言
* **廣播服務**
    * 當追蹤的用戶發布新貼文時，粉絲將會收到及時通知

### **第三方服務**
* **Amazon S3 (AWS S3)**
    * 用來安全儲存與管理用戶上傳的圖片檔案

* **Amazon Simple Email Service (AWS SES)**
    * 用來發送註冊驗證信等通知信件


## 技術架構

### **架構概述**
本專案採用**三層式架構(Three-tier Architecture)**，將應用程式邏輯劃分為表現層、業務邏輯層、和資料存取層。
* **表現層(前端)**
    * 技術:使用React函式庫，依賴Node.js運行前端建構工具
    * 職責:透過API介面與後端溝通
* **業務邏輯層(後端)**
    * 技術:PHP搭配Laravel
    * 職責:**控制層 (Controller)** 接收來自前端的請求，並調用 **服務層 (Service)** 來執行業務流程，確保程式碼職責單一且易於維護
* **資料存取層**
    * 語法:原生MySQL語法
    * **服務層**所需的所有資料都透過此層向資料庫進行存取，同時內建防止SQL注入的機制，確保安全性。

### **技術選型考量**
* **三層式架構**
    * 將前端、後端、資料庫分離，更好進行維護和擴展
* **PHP/Laravel** 
    * 為開發網頁而生的程式語言，框架提供完整的ORM、路由及認證系統配置，能快速進行功能上的開發
* **MySQL**
    * 社群平台上的內容類型多樣，MySQL提供了一個基礎的資料庫，且與PHP協作良好
* **React**
    * 採用元件化的開發模式，構建可重複使用的UI介面
## **安裝與執行**
本專案的前端與後端需分開安裝與執行。請確保您的環境已安裝以下工具：
1. 環境需求
Node.js（建議版本：18 以上）
npm 或 yarn
PHP（建議版本：8.1 以上）
Composer
MySQL 或 SQLite
Docker（選用，若需使用 docker-compose 部署）

2. 安裝後端（Laravel）
cd backend
composer install         # 安裝 PHP 依賴套件
cp .env.example .env    # 複製環境設定檔
php artisan key:generate # 產生應用程式金鑰
# 設定 .env 檔案中的資料庫連線資訊
php artisan migrate     # 執行資料庫遷移

3. 啟動後端服務
php artisan serve
預設後端服務會在 http://localhost:8000 運行。

4. 安裝前端（React）
cd frontend
npm install 

5. 啟動前端服務
npm start
預設前端服務會在 http://localhost:3000 運行。

若需使用 Amazon S3/SES 等第三方服務，請於後端 .env 檔案中設定相關金鑰。
若需使用 Docker 部署，請參考 docker-compose.yml 檔案。







<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Post\CreatePostController;
use App\Http\Controllers\Post\DeletePostController;
use App\Http\Controllers\Post\CommentOnPostController;
use App\Http\Controllers\Post\DeletePostCommentController;
use App\Http\Controllers\Post\ReplyToCommentController;
use App\Http\Controllers\Post\DeleteReplyCommentController;
use App\Http\Controllers\Post\GetPostController;
use App\Http\Controllers\Post\LikePostController;
use App\Http\Controllers\Post\GetPostLikeController;
use App\Http\Controllers\Post\GetPostCommentController;
use App\Http\Controllers\User\AddSubscriberController;
use App\Http\Controllers\User\CreateAvatarController;
use App\Http\Controllers\User\RemoveSubscriberController;
use App\Http\Controllers\User\GetFollowerController;
use App\Http\Controllers\User\GetSubscriberController;
use App\Http\Controllers\User\GetUserProfileController;
use App\Http\Controllers\Notification\NotificationController;
use App\Events\NewMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

Route::get('/health-check', function () {
    try {
        $status = [
            'status' => 'ok',
            'timestamp' => now()->toDateTimeString(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
        ];

        // 測試資料庫連接（如果需要的話）
        try {
            \Illuminate\Support\Facades\DB::connection()->getPdo();
            $status['database'] = 'connected';
        } catch (\Exception $e) {
            $status['database'] = 'failed: ' . $e->getMessage();
            // 不要因為資料庫失敗就讓健康檢查失敗
        }

        // 測試 Redis 連接
        try {
            \Illuminate\Support\Facades\Redis::ping();
            $status['redis'] = 'connected';
        } catch (\Exception $e) {
            $status['redis'] = 'failed: ' . $e->getMessage();
            // 不要因為 Redis 失敗就讓健康檢查失敗
        }

        // 檢查關鍵目錄權限
        $status['storage_writable'] = is_writable(storage_path()) ? 'yes' : 'no';
        $status['cache_writable'] = is_writable(storage_path('framework/cache')) ? 'yes' : 'no';

        return response()->json($status, 200);
        
    } catch (\Exception $e) {
        \Log::error('Health check failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'timestamp' => now()->toDateTimeString(),
        ], 500);
    }
});

// ============ 公開路由（不需要認證）============
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/verify', [RegisterController::class, 'verify']);
Route::post('/login', [LoginController::class, 'login']);

// CORS 測試端點
Route::post('/test-cors', function (Request $request) {
    return response()->json([
        'success' => true,
        'message' => 'CORS is working!',
        'received_data' => $request->all(),
        'origin' => $request->header('Origin'),
        'method' => $request->method(),
    ]);
});

// 獲取貼文和用戶信息（公開）
Route::get('/getPost/{userId}/{postId}/{tag}', [GetPostController::class, 'getPost']);
Route::get('/getPostLike/{postId}', [GetPostLikeController::class, 'getPostLike']);
Route::get('/getPostComment/{postId}', [GetPostCommentController::class, 'getPostComment']);
Route::get('/getCommentReply/{commentId}', [GetPostCommentController::class, 'getCommentReply']);
Route::get('/getUserInformation/{userId}', [GetUserProfileController::class, 'getUserInformation']);
Route::get('/searchByName/{name}', [GetUserProfileController::class, 'searchByName']);
Route::get('/getFollower/{userId}', [GetFollowerController::class, 'getFollower']);
Route::get('/getSubscriber/{userId}', [GetSubscriberController::class, 'getSubscriber']);

// ============ 需要 JWT 認證的路由 ============
Route::middleware('auth:api')->group(function () {
    // 認證相關
    Route::post('/logout', [LogoutController::class, 'logout']);
    Route::post('/refresh', [LogoutController::class, 'refresh']);
    Route::get('/me', [LogoutController::class, 'me']);
    
    // 貼文相關
    Route::post('/createPost', [CreatePostController::class, 'createPost']);
    Route::delete("/deletePost/{userId}/{postId}", [DeletePostController::class, 'deletePost']);
    Route::post('/commentOnPost', [CommentOnPostController::class, 'commentOnPost']);
    Route::delete("/deletePostComment/{userId}/{postId}/{commentId}", [DeletePostCommentController::class, 'deletePostComment']);
    Route::post('/replyToComment', [ReplyToCommentController::class, 'replyToComment']);
    Route::delete('/deleteReplyComment/{userId}/{commentId}/{replyId}', [DeleteReplyCommentController::class, 'deleteReplyComment']);
    Route::post('/likePost', [LikePostController::class, 'likePost']);
    
    // 用戶相關
    Route::post('/addSubscriber', [AddSubscriberController::class, 'addSubscriber']);
    Route::post('/createAvatar', [CreateAvatarController::class, 'createAvatar']);
    Route::delete('/removeSubscriber/{followerId}/{subscriberId}', [RemoveSubscriberController::class, 'removeSubscriber']);
    
    // 通知相關
    Route::get('/getUnreadNotifications/{notifiableId}/{type}', [NotificationController::class, 'getUnreadNotifications']);
    Route::post('/markNotificationAsRead', [NotificationController::class, 'markNotificationAsRead']);
    Route::post('/markAllNotificationsAsRead', [NotificationController::class, 'markAllNotificationsAsRead']);
    
    // Broadcasting 認證（WebSocket 頻道授權）- 使用 JWT
    Route::post('/broadcasting/auth', function (Request $request) {
        return Broadcast::auth($request);
    });
});

// 保留向後兼容（Sanctum）
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

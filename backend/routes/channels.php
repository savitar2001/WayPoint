<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use Illuminate\Support\Facades\Log; // 引入 Log facade

/*
|--------------------------------------------------------------------------
| Broadcast Channels - 使用 JWT 認證
|--------------------------------------------------------------------------
|
| 這裡定義的所有 channel 都會使用 JWT 認證
| 前端需要在 Authorization header 中帶上 Bearer token
|
*/

Broadcast::channel('user.{id}', function ($user, $id) {
    Log::info('--- Broadcasting Auth Attempt (JWT) ---');
    Log::info('Requested Channel: private-user.' . $id);
    Log::info('Requested Socket ID: ' . request()->input('socket_id'));

    // 檢查用戶是否已通過 JWT 認證
    if ($user instanceof \App\Models\User) {
        Log::info('Authenticated User ID (via JWT): ' . $user->id);
    } else {
        Log::warning('User is NOT authenticated via JWT or not an instance of App\Models\User.');
        Log::debug('Type of $user: ' . gettype($user));
        if (is_object($user)) {
            Log::debug('Class of $user: ' . get_class($user));
        } else {
            Log::debug('Value of $user: ' . print_r($user, true));
        }
        return false; // 用戶未通過 JWT 認證，拒絕訪問
    }

    Log::info('Channel User ID Parameter: ' . $id);

    // 核心授權邏輯：只允許用戶訂閱自己的私有頻道
    $isAuthorized = (int) $user->id === (int) $id;

    Log::info('Authorization Result for private-user.' . $id . ' (User ' . $user->id . ' vs Channel ' . $id . '): ' . ($isAuthorized ? 'AUTHORIZED' : 'DENIED'));
    Log::info('--- End Broadcasting Auth Attempt (JWT) ---');

    return $isAuthorized;
});

// 公開頻道範例（不需要認證）
Broadcast::channel('chat', function () {
    return true; // 所有人都可以訂閱公開頻道
});


<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\User;
use Illuminate\Support\Facades\Log; // 引入 Log facade

Broadcast::channel('user.{id}', function ($user, $id) { // 將 User $authenticatedUser 改為 $user 以允許 null
    Log::info('--- Broadcasting Auth Attempt ---');
    Log::info('Requested Channel: private-user.' . $id);
    Log::info('Requested Socket ID: ' . request()->input('socket_id')); // 記錄 socket_id

    if ($user instanceof \App\Models\User) { // 檢查 $user 是否是 User 模型的實例
        Log::info('Authenticated User ID: ' . $user->id);
    } else {
        Log::warning('User is NOT authenticated or not an instance of App\Models\User.');
        // 如果 $user 不是預期的 User 實例，可以記錄 $user 的實際類型或值
        Log::debug('Type of $user: ' . gettype($user));
        if (is_object($user)) {
            Log::debug('Class of $user: ' . get_class($user));
        } else {
            Log::debug('Value of $user: ' . print_r($user, true));
        }
        return false; // 如果用戶未認證或類型不對，直接拒絕
    }

    Log::info('Channel User ID Parameter: ' . $id);

    // 核心授權邏輯
    $isAuthorized = (int) $user->id === (int) $id;

    Log::info('Authorization Result for private-user.' . $id . ' (User ' . $user->id . ' vs Channel ' . $id . '): ' . ($isAuthorized ? 'AUTHORIZED' : 'DENIED'));
    Log::info('--- End Broadcasting Auth Attempt ---');

    return $isAuthorized;
});


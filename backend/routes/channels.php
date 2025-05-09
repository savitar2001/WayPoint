<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;
use App\Models\Post;
use App\Models\User;


Broadcast::channel('user.{userId}', function (User $user, int $userId) {
    // 驗證目前登入的使用者 ID 是否等於頻道中的 userId
    return $user->id === $userId;
});


<?php

namespace App\Services\Broadcast;

use App\Events\PostPublished;

class CreatePostBroadcastService
{
    /**
     * 分派發布新貼文的事件。
     *
     * @param int $userId
     * @return void
     */
    public function dispatchPostPublishedEvent(int $userId):void
    {
        PostPublished::dispatch($userId);
    }
}
<?php

namespace App\Events;

use App\Models\Post;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostPublished implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $post;

    /**
     * Create a new event instance.
     */
    public function __construct(Post $post) // <--- 接收 $order
    {
        $this->post = $post; // <--- 將傳入的 $order 賦值給類別的 $order 屬性
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $userFollowerModel = new \App\Models\UserFollower();
        $authorId = $this->post->user_id;
        $followerIds = $userFollowerModel->getUserFollowerIds($authorId); // 使用新方法

        $channels = [];
        foreach ($followerIds as $followerId) {
            // 為每個粉絲建立一個私有頻道 'user.{粉絲ID}'
            $channels[] = new PrivateChannel('user.' . $followerId);
        }
        return $channels;
    }
}

<?php

namespace App\Events;

use App\Models\User;
use App\Models\UserFollower;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log; 

class PostPublished implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected ?User $user; // 允許 User 為 null，以處理找不到用戶的情況

    public function __construct(int $userId)
    {
        $this->userId = $userId; 
        $this->user = User::find($userId);
        if (!$this->user) {
            Log::warning("[PostPublished Event] User not found for ID: " . $userId . ". Event will not broadcast correctly.");
        }

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array {
        if (!$this->user) {
            Log::info("[PostPublished Event] User with ID " . $this->userId . " not found. No channels to broadcast to.");
            return [];
        }
        $userFollower = new UserFollower();
        $followerIds = $userFollower->getUserFollowerIds($this->user->id);
        if (empty($followerIds)) {
            Log::info("[PostPublished Event] Author ID " . $this->user->id . " has no followers. No channels to broadcast to.");
        } else {
            Log::info("[PostPublished Event] Author ID " . $this->user->id . " has followers with IDs: " . implode(', ', $followerIds));
        }
        $channels = [];
        foreach ($followerIds as $followerId) {
            $channels[] = new PrivateChannel('user.' . $followerId);
            Log::info('發現' . $followerId);
        }
        return $channels;
    }

    public function broadcastWith(): array {
        return [
            'message' => '發布了新貼文快來看吧',
            'authorName' => $this->user ? $this->user->name : 'Someone',
        ];
    }

    public function broadcastAs(): string 
    {
        return 'PostPublished';
    }
}
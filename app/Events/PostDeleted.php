<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class PostDeleted implements ShouldBroadcastNow 
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $postId;

    public function __construct($postId)
    {
        $this->postId = $postId;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('posts-channel'),
        ];
    }

    public function broadcastAs()
    {
        return 'post-deleted';
    }

    public function broadcastWith(): array
    {
        return [
            'message' => "Post deleted",
            'postId' => $this->postId
        ];
    }
}
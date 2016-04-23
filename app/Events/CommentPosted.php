<?php

namespace App\Events;

use App\Models\Comment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CommentPosted extends Event
{
    use SerializesModels;

    public $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }


    public function broadcastOn()
    {
        return [];
    }
}

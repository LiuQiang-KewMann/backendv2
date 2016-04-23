<?php

namespace App\Events;

use App\Models\TaskHistory;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class CommentDeleted extends Event
{
    use SerializesModels;

    public $taskHistory;

    public function __construct(TaskHistory $taskHistory)
    {
        $this->taskHistory = $taskHistory;
    }


    public function broadcastOn()
    {
        return [];
    }
}
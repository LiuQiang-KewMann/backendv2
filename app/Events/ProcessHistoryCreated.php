<?php

namespace App\Events;

use App\Events\Event;
use App\Models\ProcessHistory;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ProcessHistoryCreated extends Event
{
    use SerializesModels;

    public $processHistory;


    public function __construct(ProcessHistory $processHistory)
    {
        $this->processHistory = $processHistory;
    }


    public function broadcastOn()
    {
        return [];
    }
}

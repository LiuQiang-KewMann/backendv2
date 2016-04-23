<?php namespace App\Events;

use App\Models\ProcessHistory;
use Illuminate\Queue\SerializesModels;

class NewTriggersAvailable extends Event
{
    use SerializesModels;

    public $processHistory;
    public $triggers;


    public function __construct(ProcessHistory $processHistory, $triggers)
    {
        $this->processHistory = $processHistory;
        $this->triggers = $triggers;
    }


    public function broadcastOn()
    {
        return [];
    }
}

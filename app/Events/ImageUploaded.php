<?php namespace App\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ImageUploaded extends Event
{
    use SerializesModels;

    public $fullcode;
    public $relPath;

    public function __construct($fullcode, $relPath)
    {
        $this->fullcode = $fullcode;
        $this->relPath = $relPath;
    }

    public function broadcastOn()
    {
        return [];
    }
}

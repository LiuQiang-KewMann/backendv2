<?php

namespace App\Events;

use App\Events\Event;
use App\Models\PasswordReset;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class PasswordResetTokenCreated extends Event
{
    use SerializesModels;

    public $passwordReset;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(PasswordReset $passwordReset)
    {
        $this->passwordReset = $passwordReset;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}

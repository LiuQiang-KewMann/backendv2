<?php namespace App\Events;

use App\Models\Game;
use App\User;
use Illuminate\Queue\SerializesModels;

class PlayerReset extends Event
{
    use SerializesModels;


    public $game;
    public $user;

    public function __construct(User $user, Game $game)
    {
        $this->user = $user;
        $this->game = $game;
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

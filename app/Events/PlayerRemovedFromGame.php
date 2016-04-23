<?php namespace App\Events;

use App\Models\Game;
use App\User;
use Illuminate\Queue\SerializesModels;

class PlayerRemovedFromGame extends Event
{
    use SerializesModels;

    public $game;
    public $user;

    public function __construct(User $user, Game $game)
    {
        $this->user = $user;
        $this->game = $game;
    }

    public function broadcastOn()
    {
        return [];
    }
}

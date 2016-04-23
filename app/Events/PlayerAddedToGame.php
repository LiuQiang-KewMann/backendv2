<?php namespace App\Events;

use App\Models\Game;
use App\User;
use Illuminate\Queue\SerializesModels;

class PlayerAddedToGame extends Event
{
    use SerializesModels;

    public $game;
    public $user;

    public function __construct(User $user, Game $game)
    {
        $this->game = $game;
        $this->user = $user;
    }

    public function broadcastOn()
    {
        return [];
    }
}

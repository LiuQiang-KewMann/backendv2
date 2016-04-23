<?php namespace App\Listeners;

use App\Events\PlayerReset;

class ResetPlayerInEngine
{

    public function __construct()
    {
        //
    }


    public function handle(PlayerReset $event)
    {
        $user = $event->user;
        $game = $event->game;

        // reset player in engine
        $game->resetPlayerInEngine($user);
    }
}

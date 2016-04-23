<?php namespace App\Listeners;

use App\Events\PlayerRemovedFromGame;

class RemovePlayerInEngine
{
    public function __construct()
    {
        //
    }

    public function handle(PlayerRemovedFromGame $playerRemovedFromGame)
    {
        $user = $playerRemovedFromGame->user;
        $game = $playerRemovedFromGame->game;

        $game->removePlayerInEngine($user);
    }
}

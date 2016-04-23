<?php namespace App\Traits;

use App\Models\Game;
use Playlyfe\Sdk\Playlyfe;

trait PlaylyfeTrait
{
    static public function connByGame(Game $game)
    {
        return new Playlyfe([
            'client_id' => $game->jsonGet('client_id'),
            'client_secret' => $game->jsonGet('client_secret'),
            'type' => 'client',
            'version' => 'v2'
        ]);
    }


    public function conn()
    {
        return self::connByGame($this->game);
    }
}
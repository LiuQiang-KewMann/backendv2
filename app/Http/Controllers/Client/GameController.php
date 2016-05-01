<?php namespace App\Http\Controllers\Client;

use App\Models\Game;
use App\Models\GameUser;
use App\Http\Controllers\Controller;

class GameController extends Controller
{
    public function getList()
    {
        $gameUsers = GameUser::where([
            'user_id' => $this->user->id,
            'role' => GameUser::ROLE_PLAYER
        ])->get()->toArray();
        $gameIds = array_column($gameUsers, 'game_id');

        $items = Game::whereIn('id', $gameIds)->get();

        return ['items' => $items];
    }
}
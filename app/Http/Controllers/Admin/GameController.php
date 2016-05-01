<?php namespace App\Http\Controllers\Admin;

use App\Models\Game;
use Response;
use Request;
use App\Http\Controllers\Controller;
use App\Models\GameUser;

class GameController extends Controller
{
    public function getList()
    {
        $gameUsers = GameUser::where([
            'user_id' => $this->user->id,
            'role' => GameUser::ROLE_ADMIN
        ])->get()->toArray();
        $gameIds = array_column($gameUsers, 'game_id');

        $items = Game::whereIn('id', $gameIds)->get();

        return ['items' => $items];
    }
}
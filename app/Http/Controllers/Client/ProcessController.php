<?php namespace App\Http\Controllers\Client;

use JWTAuth;
use Response;
use Request;
use App\Models\Game;
use App\Models\Process;
use App\Http\Controllers\Controller;

class ProcessController extends Controller
{
    public function getList($gameId)
    {
        $user = JWTAuth::parseToken()->toUser();
        $game = Game::findOrNew($gameId);

        $items = [];
        foreach ($game->processes as $process) {
            array_push($items, array_merge($process->detail(), [
                'status' => $process->statusByGameAndUser($game, $user)
            ]));
        }

        return Response::json(['items' => $items]);
    }


    public function getDetail($id)
    {
        $item = Process::findOrNew($id)->detail();

        return Response::json(['item' => $item]);
    }
}
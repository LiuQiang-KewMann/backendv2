<?php namespace App\Http\Controllers\Client;

use App\User;
use JWTAuth;
use Response;
use Request;
use App\Models\Game;
use App\Http\Controllers\Controller;

class GameController extends Controller
{
    public function getList()
    {
        $user = JWTAuth::parseToken()->toUser();
        $games = User::playableGames($user);

        $result =[];
        foreach ($games as $game) {
            array_push($result, $game->detail());
        }

        return Response::json($result);
    }


    public function getDetail($gameId)
    {
        $game = Game::findOrNew($gameId);
        $user = JWTAuth::parseToken()->toUser();

        if($user->hasGameRole($gameId, 'player')){
            // 1. user is player
            return Response::json($game->detail());

        } else if(strtolower($game->access) == 'public') {
            // 2. user is not player & game is public
            // add as player
            $game->createPlayer($user);

            return Response::json($game->detail());
        }

        // 3. user is not player & game is non-public
        return Response::json(['msg'=>'You are not a player of this non-public game.'], 403);
    }


    public function getScore($gameId)
    {
        $user = JWTAuth::parseToken()->toUser();
        $game = Game::findOrNew($gameId);

        // END
        return Response::json(array_merge($game->getPlayerScore($user), [
            'gameCode' => $game->code,
            'name' => $user->jsonGet('first_name')
        ]));
    }
}
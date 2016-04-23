<?php namespace App\Http\Controllers\Admin;

use App\Events\PlayerAddedToGame;
use App\Events\PlayerRemovedFromGame;
use App\Events\PlayerReset;
use App\Models\GameUser;
use Illuminate\Support\Facades\Event;
use JWTAuth;
use Response;
use Request;
use App\User;
use App\Models\Game;
use App\Http\Controllers\Controller;

class PlayerController extends Controller
{
    public function getList($gameId)
    {
        $items = GameUser::where([
            'game_id' => $gameId,
            'role' => User::ROLE_PLAYER
        ])->get();

        return ['items' => $items];
    }


    public function getDetail($gameUserId)
    {
        $gameUser = GameUser::find($gameUserId);

        if (!$gameUser->exists) {
            return Response::json(['msg' => 'player_not_in_game'], 406);
        }

        return Response::json([
            'item' => $gameUser->detail(),
        ]);
    }


    public function getScores($gameUserId)
    {
        $gameUser = GameUser::find($gameUserId);

        if (!$gameUser->exists) {
            return Response::json(['msg' => 'player_not_in_game'], 406);
        }

        return Response::json([
            'item' => $gameUser->scores(),
        ]);
    }


    public function postInvite($gameId)
    {
        $email = Request::get('email');
        $user = User::firstOrNew(['email' => $email]);

        // user not exist, create unregistered user
        if (!$user->exists) {
            $user = User::create([
                'email' => $email,
                'status' => User::STATUS_UNREGISTERED
            ]);
        }

        $gameUserQuery = GameUser::where([
            'game_id' => $gameId,
            'user_id' => $user->id,
            'role' => User::ROLE_PLAYER
        ]);

        if ($gameUserQuery->exists()) {
            return Response::json(['msg' => 'player_exist_already'], 406);

        } else {
            $gameUser = GameUser::create([
                'game_id' => $gameId,
                'user_id' => $user->id,
                'role' => User::ROLE_PLAYER
            ]);

            return Response::json([
                'msg' => 'player_added',
                'item' => $gameUser->detail()
            ]);
        }
    }


    public function postRemove($gameUserId)
    {
        $gameUser = GameUser::firstOrNew(['id' => $gameUserId]);

        if (!$gameUser->exists) {
            return Response::json(['msg' => 'player_not_in_game'], 406);
        }

        $game = $gameUser->game;
        $user = $gameUser->user;

        $gameUser->delete();

        // fire event
        Event::fire(new PlayerRemovedFromGame($user, $game));

        return Response::json(['msg' => 'player_removed']);
    }


    public function postReset($gameUserId)
    {
        $gameUser = GameUser::firstOrNew(['id' => $gameUserId]);

        if (!$gameUser->exists) {
            return Response::json(['msg' => 'player_not_in_game'], 406);
        }

        $game = $gameUser->game;
        $user = $gameUser->user;

        $gameUser->update(['json_remote' => '{}']);

        // fire event
        Event::fire(new PlayerReset($user, $game));

        return Response::json(['msg' => 'player_reset']);
    }
}
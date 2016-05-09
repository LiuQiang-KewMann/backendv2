<?php namespace App\Http\Controllers\Admin;

use App\Models\GameUser;
use Response;
use Request;
use App\User;
use App\Http\Controllers\Controller;

class PlayerController extends Controller
{
    public function getList($gameId)
    {
        $items = GameUser::where([
            'game_id' => $gameId,
            'role' => GameUser::ROLE_PLAYER
        ])->get();

        return ['items' => $items];
    }


    public function getDetail($id)
    {
        $gameUser = GameUser::findOrNew($id);

        if (!$gameUser->exists) {
            return Response::json(['msg' => 'player_not_in_game'], 406);
        }

        return ['item' => $gameUser->detail()];
    }


    public function getScores($id)
    {
        $gameUser = GameUser::findOrNew($id);

        if (!$gameUser->exists) {
            return Response::json(['msg' => 'player_not_in_game'], 406);
        }

        return ['item' => $gameUser->scores()];
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
            'role' => GameUser::ROLE_PLAYER
        ]);

        if ($gameUserQuery->exists()) {
            return Response::json(['msg' => 'player_exist_already'], 406);

        } else {
            $gameUser = GameUser::create([
                'game_id' => $gameId,
                'user_id' => $user->id,
                'role' => GameUser::ROLE_PLAYER
            ]);

            return Response::json([
                'msg' => 'player_added',
                'item' => $gameUser->detail()
            ]);
        }
    }


    public function postRemove($id)
    {
        $gameUser = GameUser::findOrNew($id);

        if (!$gameUser->exists) {
            return Response::json(['msg' => 'player_not_in_game'], 406);
        }

        $gameUser->delete();


        return ['msg' => 'player_removed'];
    }


    public function postReset($id)
    {
        $gameUser = GameUser::findOrNew($id);

        if (!$gameUser->exists) {
            return Response::json(['msg' => 'player_not_in_game'], 406);
        }

        $gameUser->update(['json_remote' => '{}']);

        return ['msg' => 'player_reset'];
    }
}
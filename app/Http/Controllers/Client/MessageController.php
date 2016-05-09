<?php namespace App\Http\Controllers\Client;

use App\Models\GameUser;
use App\Models\Message;
use Request;
use Response;
use App\Http\Controllers\Controller;

class MessageController extends Controller
{
    public function getAnyUnread($gameId)
    {
        $gameUser = GameUser::firstOrNew([
            'game_id' => $gameId,
            'user_id' => $this->user->id,
            'role' => GameUser::ROLE_PLAYER
        ]);

        $unreadMessageCount = Message::where([
            'game_id' => $gameId,
            'to' => $gameUser->id
        ])->count();

        $result = ($unreadMessageCount > 0) ? true : false;

        return ['result' => $result];
    }
}
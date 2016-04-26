<?php namespace App\Http\Controllers\Design;

use App\Models\Game;
use App\Models\JsonSchema;
use App\Models\GameUser;
use App\User;
use Response;
use Request;
use DB;
use App\Http\Controllers\Controller;

class GameController extends Controller
{
    public function getList()
    {
        $gameUsers = GameUser::where([
            'user_id' => $this->user->id,
            'role' => User::ROLE_DESIGNER
        ])->get()->toArray();
        $gameIds = array_column($gameUsers, 'game_id');

        $items = Game::whereIn('id', $gameIds)->get();

        return ['items' => $items];
    }


    public function getDetail($id)
    {
        $item = Game::find($id);

        return [
            'item' => $item->detail(),
            'schema_edit' => JsonSchema::items('game', 'edit'),
            'schema_config' => JsonSchema::items('game', 'config')
        ];
    }


    public function postUpdate($id)
    {
        $updateArray = Request::only(JsonSchema::names('game', 'edit'));

        $item = Game::find($id);
        $item->jsonUpdate($updateArray);

        return [
            'item' => $item->detail(),
            'msg' => 'game_updated'
        ];
    }


    public function getSyncDesign($id)
    {
        $item = Game::find($id);
        $item->syncDesign();

        return [
            'item' => $item->detail(),
            'msg' => 'game_synced'
        ];
    }
}
<?php namespace App\Http\Controllers\Design;

use App\Models\Game;
use App\Models\JsonSchema;
use App\Models\Leaderboard;
use Response;
use Request;
use App\Http\Controllers\Controller;

class LeaderboardController extends Controller
{
    public function getList($gameId)
    {
        $game = Game::find($gameId);

        return [
            'items' => $game->leaderboards,
            'parent' => $game
        ];
    }


    public function getDetail($id)
    {
        $item = Leaderboard::find($id);

        return [
            'item' => $item->detail(),
            'schema_edit' => JsonSchema::items('leaderboard', 'edit'),
            'schema_config' => JsonSchema::items('leaderboard', 'config')
        ];
    }


    public function getSyncList($gameId)
    {
        $game = Game::find($gameId);

        Leaderboard::syncList($game);

        return [
            'items' => $game->leaderboards,
            'parent' => $game
        ];

    }


    public function getSync($id)
    {
        $item = Leaderboard::find($id);
        $item->syncDesign();

        return [
            'item' => $item->detail(),
            'msg' => 'leaderboard_synced'
        ];
    }


    public function postUpdate($id)
    {
        $updateArray = Request::only(JsonSchema::names('leaderboard', 'edit'));

        $item = Leaderboard::find($id);
        $item->jsonUpdate($updateArray);

        return [
            'item' => $item->detail(),
            'msg' => 'leaderboard_updated'
        ];
    }
}
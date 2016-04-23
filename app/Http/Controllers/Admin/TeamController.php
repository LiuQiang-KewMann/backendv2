<?php namespace App\Http\Controllers\Admin;

use App\Models\Game;
use App\Models\JsonSchema;
use App\Models\Team;
use Response;
use Request;
use App\Http\Controllers\Controller;

/*
 * Kindly take note that, team in KGB is actually team instance in Playlyfe
 *
 */

class TeamController extends Controller
{
    public function getList($gameId)
    {
        $game = Game::find($gameId);

        return [
            'items' => $game->teams,
            'parent' => $game,
            'schema_create' => JsonSchema::items('team', 'create')
        ];
    }


    public function getDetail($id)
    {
        $item = Team::find($id);

        return ['item' => $item->detail()];
    }


    public function getSyncList($gameId)
    {
        $game = Game::find($gameId);

        Team::sync($game);

        return [
            'items' => $game->teams,
            'msg' => 'teams_synced'
        ];
    }


    public function postCreate($gameId)
    {
        $name = Request::get('name');
        $definition = Request::get('definition');

        $item = Team::create([
            'game_id' => $gameId,
            'json' => json_encode([
                'name' => $name,
                'definition' => $definition
            ])
        ]);

        return [
            'item' => $item->detail(),
            'msg' => 'team_created'
        ];
    }


    public function postDelete($id)
    {
        Team::find($id)->delete();

        return ['msg' => 'team_deleted'];
    }
}
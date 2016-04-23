<?php namespace App\Http\Controllers\Design;

use App\Models\Game;
use Request;
use Response;
use App\Models\Challenge;
use App\Models\ChallengeSet;
use App\Models\JsonSchema;

use App\Http\Controllers\Controller;

class ChallengeController extends Controller
{
    public function getList($gameId)
    {
        $game = Game::find($gameId);

        return [
            'items' => $game->challenges,
            'parent' => $game,
            'schema_create' => JsonSchema::items('challenge', 'create'),
            'instance_create' => array_merge(JsonSchema::defaultValues('challenge', 'create'), [
                'number' => Challenge::proposedNumber($gameId)
            ])
        ];
    }


    public function getDetail($id)
    {
        $item = Challenge::find($id);

        return [
            'item' => $item->detail(),
            'schema_edit' => JsonSchema::items('challenge', 'edit'),
            'schema_config' => JsonSchema::items('challenge', 'config')
        ];
    }


    public function getRuntimeDetail($id)
    {
        $item = Challenge::find($id);

        return [
            'item' => array_merge($item->detail(), ['components' => $item->components]),
            'instance' => $item->instance()
        ];
    }


    public function postCreate($gameId)
    {
        $updateArray = Request::only(JsonSchema::names('challenge', 'create'));

        $identifierArray = [
            'game_id' => $gameId,
            'number' => array_get($updateArray, 'number')
        ];

        if (Challenge::where($identifierArray)->exists()) {
            return Response::json(['msg' => 'duplicate_number'], 406);
        }

        // number should not be updated in JSON
        array_forget($updateArray, 'number');

        $item = Challenge::create($identifierArray);
        $item->jsonUpdate($updateArray);

        return [
            'item' => $item->detail(),
            'instance_create' => array_merge(JsonSchema::defaultValues('challenge', 'create'), [
                'number' => Challenge::proposedNumber($gameId)
            ])
        ];
    }


    public function postDelete($id)
    {
        $item = Challenge::find($id);
        $gameId = $item->game_id;

        $item->delete();

        return [
            'msg' => 'challenge_deleted',
            'instance_create' => array_merge(JsonSchema::defaultValues('challenge', 'create'), [
                'number' => Challenge::proposedNumber($gameId)
            ])
        ];
    }


    public function postUpdate($id)
    {
        $item = Challenge::find($id);
        $updateArray = Request::only(JsonSchema::names('challenge', 'edit'));

        // update
        $item->jsonUpdate($updateArray);

        // END
        return [
            'item' => $item->detail(),
            'msg' => 'challenge_updated'
        ];
    }
}
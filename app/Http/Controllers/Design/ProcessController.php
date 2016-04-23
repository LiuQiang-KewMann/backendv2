<?php namespace App\Http\Controllers\Design;

use App\Models\JsonSchema;
use Response;
use Request;
use App\Models\Game;
use App\Models\Process;
use App\Http\Controllers\Controller;

class ProcessController extends Controller
{
    public function getList($gameId)
    {
        $game = Game::find($gameId);

        return [
            'items' => $game->processes,
            'parent' => $game
        ];
    }


    public function getDetail($id)
    {
        $item = Process::find($id);

        return [
            'item' => $item->detail(),
            'schema_edit' => JsonSchema::items('process', 'edit'),
            'schema_config' => JsonSchema::items('process', 'config')
        ];
    }


    public function getSyncList($gameId)
    {
        $game = Game::find($gameId);
        Process::syncList($game);

        return [
            'items' => $game->processes,
            'parent' => $game
        ];
    }


    public function getSync($id)
    {
        $item = Process::find($id);
        $item->syncDesign();

        return [
            'item' => $item->detail(),
            'msg' => 'process_synced',
        ];
    }


    public function postUpdate($id)
    {
        $updateArray = Request::only(JsonSchema::names('process', 'edit'));

        $item = Process::find($id);
        $item->jsonUpdate($updateArray);

        return [
            'item' => $item->detail(),
            'msg' => 'process_updated',
        ];
    }
}
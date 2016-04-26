<?php namespace App\Http\Controllers\Design;

use App\Models\JsonSchema;
use App\Models\Task;
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
        // make sure unhide_if_task_complete & unlock_if_task_complete is properly entered, by remote_id
        $unhideIfTaskComplete = Request::get('unhide_if_task_complete');
        if ($unhideIfTaskComplete) {
            if (!Task::where('remote_id', $unhideIfTaskComplete)->exists()) {
                return Response::json(['msg' => 'unhide_if_task_complete_set_wrongly'], 406);
            }
        }        

        $unlockIfTaskComplete = Request::get('unlock_if_task_complete');
        if ($unlockIfTaskComplete) {
            if (!Task::where('remote_id', $unlockIfTaskComplete)->exists()) {
                return Response::json(['msg' => 'unlock_if_task_complete_set_wrongly'], 406);
            }
        }

        $item = Process::find($id);
        $updateArray = Request::only(JsonSchema::names('process', 'edit'));
        $item->jsonUpdate($updateArray);

        return [
            'item' => $item->detail(),
            'msg' => 'process_updated',
        ];
    }
}
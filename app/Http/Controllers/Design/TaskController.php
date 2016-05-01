<?php namespace App\Http\Controllers\Design;

use App\Models\Challenge;
use App\Models\JsonSchema;
use App\Models\Process;
use App\Models\Task;
use App\Http\Controllers\Controller;
use Response;
use Request;

class TaskController extends Controller
{
    public function getList($processId)
    {
        $process = Process::find($processId);

        return [
            'items' => $process->tasks,
            'parent' => $process,
            'schema_create' => JsonSchema::items('task', 'create'),
            'schema_config' => JsonSchema::items('task', 'config'),
        ];
    }


    public function getDetail($id)
    {
        $item = Task::find($id);

        return [
            'item' => $item->detail(),
            'schema_edit' => JsonSchema::items('task', 'edit'),
            'schema_config' => JsonSchema::items('task', 'config')
        ];
    }


    public function postUpdate($id)
    {
        $item = Task::find($id);
        $updateArray = Request::only(JsonSchema::names('task', 'edit'));

        // map challenge if provided
        $challengeNumber = array_get($updateArray, 'challenge_number');
        if (is_numeric($challengeNumber)) {
            $challenge = Challenge::firstOrNew([
                'game_id' => $item->process->game_id,
                'number' => $challengeNumber
            ]);

            // if challenge does not exist
            if (!$challenge->exists) {
                $item->update(['challenge_number' => null]);

                return Response::json([
                    'item' => $item->detail(),
                    'msg' => 'invalid_challenge_number'
                ], 406);
            }

            $item->update(['challenge_number' => $challengeNumber]);
            array_forget($updateArray, 'challenge_number');

        } else {
            $item->update(['challenge_number' => null]);
        }

        $item->jsonUpdate($updateArray);

        return [
            'item' => $item->detail(),
            'msg' => 'task_updated'
        ];
    }
}
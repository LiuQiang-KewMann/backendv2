<?php namespace App\Http\Controllers\Client;

use App\Models\Challenge;
use App\Models\GameUser;
use App\Models\Process;
use App\Models\ProcessHistory;
use App\Models\TaskHistory;
use App\Http\Controllers\Controller;
use Response;

class TaskHistoryController extends Controller
{
    /*
     * get list by a given process
     */
    public function getList($processId)
    {
        $process = Process::find($processId);

        $gameUser = GameUser::firstOrNew([
            'game_id' => $process->game_id,
            'user_id' => $this->user->id,
            'role' => GameUser::ROLE_PLAYER
        ]);

        $processHistory = ProcessHistory::firstOrCreate([
            'process_id' => $processId,
            'game_user_id' => $gameUser->id
        ]);

        $taskHistoryArray = $processHistory->taskHistories->toArray();

        $totalTaskNbr = sizeof($process->tasks);
        $releasedTaskNbr = sizeof($taskHistoryArray);

        $unreleasedTaskArray = [];
        for ($i = $releasedTaskNbr; $i < $totalTaskNbr; $i++) {
            array_push($unreleasedTaskArray, ['status' => TaskHistory::STATUS_UNRELEASED]);
        }

        $items = array_merge($taskHistoryArray, $unreleasedTaskArray);

        return ['items' => $items];
    }


    /*
     * get detail of a particular history
     */
    public function getDetail($id)
    {
        $taskHistory = TaskHistory::find($id);

        // check expiration
        $taskHistory->checkExpiration();

        return ['item' => $taskHistory->detail()];
    }


    /*
     * play one task
     */
    public function postPlay($id)
    {
        $taskHistory = TaskHistory::findOrNew($id);

        // if taskHistory not found
        if (!$taskHistory->exists) {
            return Response::json(['message' => 'taskHistory_not_found.'], 406);
        }

        // if taskHistory is completed
        if ($taskHistory->status == 'completed') {
            return Response::json(['message' => 'taskHistory_completed_already.'], 406);
        }

        // save submission
        $taskHistory->saveSubmissions();

        // default msg
        $msg = array_get([
            Challenge::RESULT_PASS => 'Pass',
            Challenge::RESULT_FAIL => 'Fail',
        ], $taskHistory->result);

        // END
        return [
            'msg' => $msg
        ];
    }
}
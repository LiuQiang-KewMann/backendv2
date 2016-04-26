<?php namespace App\Http\Controllers\Client;

use App\Models\Challenge;
use App\Models\GameUser;
use App\Models\ProcessHistory;
use App\Models\TaskHistory;
use App\Http\Controllers\Controller;
use App\User;
use Response;

class TaskHistoryController extends Controller
{
    public function getList($gameId, $processId)
    {
        $gameUser = GameUser::firstOrNew([
            'game_id' => $gameId,
            'user_id' => $this->user->id,
            'role' => User::ROLE_PLAYER
        ]);

        $processHistory = ProcessHistory::firstOrCreate([
            'process_id' => $processId,
            'game_user_id' => $gameUser->id,
        ]);

        $processHistory->checkExpiration();

        $items = $processHistory->taskHistories;

        $totalNbr = sizeof($processHistory->process->tasks);
        $nbrOfReleased = sizeof($items);
        $unreleased = [];
        for ($i = $nbrOfReleased; $i < $totalNbr; $i++) {
            array_push($unreleased, [
                'status' => 'unreleased'
            ]);
        }

        $items = array_merge($items->toArray(), $unreleased);

        return ['items' => $items];
    }


    public function getDetail($taskHistoryId)
    {
        $item = TaskHistory::find($taskHistoryId);

        return ['item' => $item->detail()];
    }


    public function postPlay($id)
    {
        $taskHistory = TaskHistory::findOrNew($id);

        // if taskHistory not found
        if (!$taskHistory->exists) {
            return Response::json(['message' => 'taskHistory_not_found.'], 406);
        }

        // if task completed already then return with error msg
        if ($taskHistory->status == 'completed') {
            return Response::json(['message' => 'taskHistory_completed_already.'], 406);
        }

        // save submission
        $taskHistory->saveSubmissions();

        // give out rewards
        $receiverRewards = $taskHistory->giveOutRewards();
        $myRewards = array_get($receiverRewards, $this->user->id);

        // default msg
        $msg = array_get([
            Challenge::RESULT_PASS => 'Pass',
            Challenge::RESULT_FAIL => 'Fail',
        ], $taskHistory->result);

        // END
        return [
            'msg' => $msg,
            'rewards' => $myRewards
        ];
    }
}
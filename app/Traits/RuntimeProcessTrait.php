<?php namespace App\Traits;

use App\Events\ProcessHistoryCreated;
use App\Models\Game;
use App\Models\GameUser;
use App\Models\Process;
use App\Models\ProcessHistory;
use Event;
use App\User;
use App\Models\Task;

trait RuntimeProcessTrait
{
    public static function fillinStatus($processes, GameUser $gameUser)
    {
        foreach ($processes as $key => $process) {
            $status = self::getStatus($process['db_id'], $gameUser);

            if ($status == self::STATUS_HIDDEN) {
                array_forget($processes, $key);

            } else {
                array_set($processes, "$key.status", $status);
            }
        }

        return $processes;
    }

    public static function getStatus($processId, GameUser $gameUser)
    {
        $process = Process::find($processId);

        // check flag - hidden
        if ($process->jsonGet('hidden')) {
            return 'hidden';
        }

//        // check flag - unhide_if_task_complete
//        if ($taskFullcode = $process->jsonGet('unhide_if_task_complete')) {
//            $task = Task::firstOrNew(['fullcode' => $taskFullcode]);
//            $taskHistory = $task->historyByGameAndUser($game, $user);
//
//            if (!$taskHistory->exists || ($taskHistory->status != 'completed')) {
//                return 'hidden';
//            }
//        }
//
//        // check flag - unlock_if_task_complete
//        if ($taskFullcode = $process->jsonGet('unlock_if_task_complete')) {
//            $task = Task::firstOrNew(['fullcode' => $taskFullcode]);
//            $taskHistory = $task->historyByGameAndUser($game, $user);
//
//            if (!$taskHistory->exists || ($taskHistory->status != 'completed')) {
//                return 'locked';
//            }
//        }

        // if all flags are off, then process should be active
        return 'active';
    }


    public function historyByGameAndUser(Game $game, User $user)
    {
        $finder = [
            'game_id' => $game->id,
            'process_id' => $this->id,
            'user_id' => $user->id
        ];

        // try to find existing processHistory
        $processHistory = ProcessHistory::firstOrNew($finder);

        // if history does not exist
        if (!$processHistory->exists) {
            // create remote instance
            $res = $this->connByGame($game)->post('/runtime/processes', [
                'player_id' => $user->playerId
            ], [
                'definition' => $this->code
            ]);

            // create local history
            $processHistory = ProcessHistory::create(array_merge(
                $finder,
                [
                    'id_in_engine' => $res['id'],
                    'json_remote' => json_encode($res)
                ],
                $this->getAttributesOnly(['game_code', 'code', 'fullcode'])));

            // fire the event - ProcessHistoryCreated
            Event::fire(new ProcessHistoryCreated($processHistory));
        }

        return $processHistory;
    }
}
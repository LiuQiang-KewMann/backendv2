<?php namespace App\Traits;


use App\Models\GameUser;
use App\Models\Process;
use App\Models\Task;
use App\Models\TaskHistory;

trait RuntimeProcessTrait
{
    /**
     * @param $processesArray
     * @param GameUser $gameUser
     */
    public static function updateRuntimeStatus(&$processesArray, GameUser $gameUser)
    {
        foreach ($processesArray as $key => $process) {
            $status = self::getRuntimeStatus($process['db_id'], $gameUser);

            if ($status == self::STATUS_HIDDEN) {
                array_forget($processesArray, $key);

            } else {
                array_set($processesArray, "$key.status", $status);
            }
        }
    }


    public static function getRuntimeStatus($processId, GameUser $gameUser)
    {
        $process = Process::find($processId);

        // check flag - hidden
        if ($process->jsonGet('hidden')) {
            return Process::STATUS_HIDDEN;
        }

        // check flag - unhide_if_task_complete
        if ($taskRemoteId = $process->jsonGet('unhide_if_task_complete')) {
            $task = Task::firstOrNew(['remote_id' => $taskRemoteId]);
            $taskHistory = TaskHistory::firstOrNew([
                'task_id' => $task->id,
                'game_user_id' => $gameUser->id
            ]);

            if (!$taskHistory->exists || ($taskHistory->status != TaskHistory::STATUS_COMPLETED)) {
                // if taskHistory does not exist OR status is not completed
                return Process::STATUS_HIDDEN;
            }
        }

        // check flag - unlock_if_task_complete
        if ($taskRemoteId = $process->jsonGet('unlock_if_task_complete')) {
            $task = Task::firstOrNew(['remote_id' => $taskRemoteId]);
            $taskHistory = TaskHistory::firstOrNew([
                'task_id' => $task->id,
                'game_user_id' => $gameUser->id
            ]);

            if (!$taskHistory->exists || ($taskHistory->status != TaskHistory::STATUS_COMPLETED)) {
                // if taskHistory does not exist OR status is not completed
                return Process::STATUS_LOCKED;
            }
        }

        // if all flags are off, then process should be active
        return Process::STATUS_ACTIVE;
    }
}
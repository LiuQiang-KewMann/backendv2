<?php namespace App\Traits;

use App\Models\GameUser;
use App\Models\GameUserTeamRole;
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
        $status = Process::STATUS_ACTIVE;

        // 1. check flag - hidden
        if ($process->jsonGet('hidden')) {
            $status = Process::STATUS_HIDDEN;
        }

        // 2. check flag - open_to_roles_only
        if ($roleArrayString = $process->jsonGet('open_to_roles_only')) {
            $roleArray = explode(';', $roleArrayString);
            self::checkOpenToRolesOnly($status, $gameUser, $roleArray);
        }

        // 3. check flag - unhide_if_task_complete
        if ($taskRemoteId = $process->jsonGet('unhide_if_task_complete')) {
            self::checkUnhideIfTaskComplete($status, $taskRemoteId, $gameUser);
        }

        // 4. check flag - unlock_if_task_complete
        if ($taskRemoteId = $process->jsonGet('unlock_if_task_complete')) {
            self::checkUnlockIfTaskComplete($status, $taskRemoteId, $gameUser);
        }

        return $status;
    }


    private static function checkOpenToRolesOnly(&$status, GameUser $gameUser, $roles = [])
    {
        $gameUserTeamRoleArray = GameUserTeamRole::where([
            'game_user_id' => $gameUser->id
        ])->get()->toArray();

        $gameUserRoles = array_unique(array_column($gameUserTeamRoleArray, 'role'));

        $passed = false;
        foreach ($roles as $role) {
            if (in_array($role, $gameUserRoles)) {
                $passed = true;
                break;
            }
        }

        // if not pass
        if (!$passed) {
            $status = Process::STATUS_HIDDEN;
        }
    }


    private static function checkUnhideIfTaskComplete(&$status, $taskRemoteId, GameUser $gameUser)
    {
        $task = Task::firstOrNew(['remote_id' => $taskRemoteId]);
        $taskHistory = TaskHistory::firstOrNew([
            'task_id' => $task->id,
            'game_user_id' => $gameUser->id
        ]);

        if (!$taskHistory->exists || ($taskHistory->status != TaskHistory::STATUS_COMPLETED)) {
            // if taskHistory does not exist
            // OR status is not completed
            $status = Process::STATUS_HIDDEN;
        }
    }


    private static function checkUnlockIfTaskComplete(&$status, $taskRemoteId, GameUser $gameUser)
    {
        $task = Task::firstOrNew(['remote_id' => $taskRemoteId]);
        $taskHistory = TaskHistory::firstOrNew([
            'task_id' => $task->id,
            'game_user_id' => $gameUser->id
        ]);

        if (!$taskHistory->exists || ($taskHistory->status != TaskHistory::STATUS_COMPLETED)) {
            // if taskHistory does not exist
            // OR status is not completed
            $status = Process::STATUS_LOCKED;
        }
    }
}
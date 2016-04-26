<?php namespace App\Traits;


use App\Models\Task;
use App\Models\TaskHistory;
use Carbon\Carbon;

trait RuntimeProcessHistoryTrait
{
    /*
     * create remote process instance based on local record
     *
     */
    public function createRemoteInstance()
    {
        $this->game_id = $this->gameUser->game_id;
        $this->user_id = $this->gameUser->user_id;

        $remoteInstance = $this->conn()
            ->post('/runtime/processes', [
                'player_id' => $this->user->playerId
            ], [
                'definition' => $this->process->remote_id
            ]);

        $this->remote_id = $remoteInstance['id'];
        $this->json = json_encode($remoteInstance);
    }


    /*
     * sync remote triggers to local taskHistory
     *
     */
    public function syncTriggers()
    {
        $triggers = $this->conn()
            ->get('/runtime/processes/' . $this->remote_id . '/triggers', [
                'player_id' => $this->user->playerId
            ]);

        foreach ($triggers as $trigger) {
            $task_remote_id = $trigger['trigger'];

            $task = Task::firstOrNew([
                'process_id' => $this->process_id,
                'remote_id' => $task_remote_id
            ]);

            // if task does not exist then jump out and continue next iteration
            if (!$task->exists) continue;

            TaskHistory::firstOrCreate([
                'process_history_id' => $this->id,
                'task_id' => $task->id,
                'game_user_id' => $this->game_user_id
            ])->jsonUpdate($trigger);
        }
    }


    /*
     * check all uncompleted taskHistories, and update status = completed if expires
     *
     */
    public function checkExpiration()
    {
        // find all uncompleted taskHistories
        $taskHistories = TaskHistory::where('process_history_id', $this->id)
            ->where('status', '<>', TaskHistory::STATUS_COMPLETED)
            ->get();

        foreach ($taskHistories as $taskHistory) {
            // if corresponding task expiration is set already
            if ($expiration = $taskHistory->task->jsonGet('expiration')) {
                $expiration = Carbon::createFromFormat('Y-m-d H:i:s', $expiration);
                $diffInSeconds = $expiration->diffInSeconds(null, false);

                if ($diffInSeconds >= 0) {
                    $taskHistory->update(['status' => TaskHistory::STATUS_COMPLETED]);
                }

            }
        }
    }
}
<?php namespace App\Traits;

use App\Models\Challenge;
use App\Models\Component;
use App\Models\FileManager;
use App\Models\Submission;
use App\Models\Task;
use App\Models\TaskHistory;
use Carbon\Carbon;
use Request;

trait RuntimeTaskHistoryTrait
{
    /*
     * save user submissions and update result
     */
    public function saveSubmissions()
    {
        foreach ($this->task->challenge->components as $component) {
            if (Request::hasFile($component->id)) {
                // if submission is file
                $file = Request::file($component->id);
                $submission = FileManager::put($component->challenge->game->jsonGet('code'), FileManager::FOLDER_USER, $file);

            } else {
                // if submission is normal input
                $submission = Request::get($component->id);
            }

            // save submission
            Submission::create([
                'component_id' => $component->id,
                'task_history_id' => $this->id,
                'submission' => $submission,
            ]);
        }

        // get result
        $totalScore = Submission::where('task_history_id', $this->id)
            ->where('operator', '<>', Component::OPERATOR_FREE)
            ->where('result', 1)
            ->where('loop_count', $this->loop_count)
            ->where('attempt', $this->attempt)
            ->count();
        $passScore = $this->task->challenge->jsonGet('pass_score', 0);
        $result = ($totalScore >= $passScore) ? Challenge::RESULT_PASS : Challenge::RESULT_FAIL;

        // update result
        $this->result = $result;

        // perform post result update
        $this->postResultUpdated();

        // save
        $this->save();
    }


    /*
     * this is performed after result change, and be called
     */
    public function postResultUpdated()
    {
        if ($this->status == TaskHistory::STATUS_LOOPING) {
            $this->postResultUpdatedForLooping();

        } else if ($this->status == TaskHistory::STATUS_NON_LOOPING) {
            $this->postResultUpdatedForNonLooping();
        }
    }


    /*
     * this is performed after result change for looping task
     */
    public function postResultUpdatedForLooping()
    {
        $maxLoopCount = $this->task->jsonGet('max_loop_count');

        // check isBlocking
        $isBlocking = $this->task->jsonGet('is_blocking', false);

        if ($isBlocking) {
            // blocking: check the result
            if ($this->result == Challenge::RESULT_PASS) {
                // result pass
                if ($this->loop_count >= $maxLoopCount) {
                    // if reach maxLoopCount: end the looping
                    $this->status = TaskHistory::STATUS_COMPLETED;

                } else {
                    // if not reach maxLoopCount: go to next loop
                    $this->loop_count++;
                    $this->attempt = 1;
                }

            } else {
                // result NOT pass, continue in current loop
                $this->attempt++;
            }

        } else {
            // non-blocking: do not check the result
            if ($this->loop_count >= $maxLoopCount) {
                // if reach maxLoopCount: end the looping
                $this->status = TaskHistory::STATUS_COMPLETED;

            } else {
                // if not reach maxLoopCount: go to next loop
                $this->loop_count++;
                $this->attempt = 1;
            }
        }
    }


    /*
    * this is performed after result change for non-looping task
    */
    public function postResultUpdatedForNonLooping()
    {
        // check isBlocking
        $isBlocking = $this->task->jsonGet('is_blocking', false);

        if ($isBlocking) {
            // blocking
            if ($this->result == Challenge::RESULT_PASS) {
                // PASS result will change the status to completed
                $this->status = TaskHistory::STATUS_COMPLETED;

            } else {
                // other result will increase attempt by 1
                $this->attempt++;
            }

        } else {
            // non-blocing, any result will make status to be completed
            $this->status = TaskHistory::STATUS_COMPLETED;
        }
    }


    /*
     * play corresponding trigger in Playlyfe
     *
     */
    public function doRemoteCompletion()
    {
        // if remote completed already, stop and return
        if ($this->remote_completed == 1) return;

        $res = $this->conn()->post('/runtime/processes/' . $this->processHistory->remote_id . '/play', [
            'player_id' => $this->user->playerId
        ], [
            'trigger' => $this->jsonGet('trigger')
        ]);


        // generate new triggers
        foreach ($res['triggers'] as $trigger) {
            $task_remote_id = $trigger['trigger'];

            $task = Task::firstOrNew([
                'process_id' => $this->processHistory->process_id,
                'remote_id' => $task_remote_id
            ]);

            // if task does not exist then jump out and continue next iteration
            if (!$task->exists) continue;

            // for parallel gateway, some triggers might be created already
            TaskHistory::firstOrCreate([
                'process_history_id' => $this->process_history_id,
                'task_id' => $task->id,
                'game_user_id' => $this->game_user_id
            ])->jsonUpdate($trigger);
        }

        // set flag of remote_completed to be 1
        $this->remote_completed = 1;
    }


    /*
     * to be called in taskHistory.detail() so that the
     */
    public function checkExpiration()
    {
        // if status is completed or expired, stop and return
        if ($this->status == self::STATUS_COMPLETED || $this->status == self::STATUS_EXPIRED) return;

        // if task got expiration json attribute
        if ($expiration = $this->task->jsonGet('expiration')) {
            try {
                $expiration = Carbon::createFromFormat('Y-m-d H:i:s', $expiration);
                $diffInSeconds = $expiration->diffInSeconds(null, false);

                if ($diffInSeconds >= 0) {
                    $this->update(['status' => TaskHistory::STATUS_EXPIRED]);
                }

            } catch (\InvalidArgumentException $e) {
                // do nothing ...
                // exception might be due to invalid expiration date
            }
        }
    }
}
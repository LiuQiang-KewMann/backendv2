<?php namespace App\Traits;

use App\Models\Challenge;
use App\Models\Component;
use App\Models\FileManager;
use App\Models\GameUser;
use App\Models\Reward;
use App\Models\Submission;
use App\Models\Task;
use App\Models\TaskHistory;
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

        if ($isBlocking && ($this->result != Challenge::RESULT_PASS)) {
            // blocking and result is NOT PASS, continue in current loop
            // only increase attempt
            $this->attempt++;

        } else {
            // non-blocking
            if ($this->loop_count >= $maxLoopCount) {
                // end the looping
                $this->status = TaskHistory::STATUS_COMPLETED;

            } else {
                // go to next loop
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

            TaskHistory::firstOrCreate([
                'process_history_id' => $this->process_history_id,
                'task_id' => $task->id,
                'game_user_id' => $this->game_user_id
            ])->jsonUpdate($trigger);
        }
    }


    /*
     * give out rewards
     *
     */
    public function giveOutRewards()
    {
        $definedRewards = Reward::where([
            'belongs_to_class' => Task::class,
            'belongs_to_id' => $this->task->id
        ])->get();


        $receiverRewards = [];
        foreach ($definedRewards as $reward) {
            $dispatcher = $reward->dispatcher;

            $expression = $dispatcher->jsonGet('condition');
            $expression = '$result' . " = ($expression);";
            $result = false;
            eval($expression);

            $expression = $dispatcher->jsonGet('receiver');
            $expression = '$receiver' . " = ($expression);";
            $receiver = null;
            eval($expression);

            if ($result) {
                if (!array_has($receiverRewards, $receiver->id)) {
                    array_set($receiverRewards, $receiver->id, []);
                }

                array_push($receiverRewards[$receiver->id], $reward->jsonGet('reward'));
            };
        }

        foreach ($receiverRewards as $receiverId => $rewards) {
            $gameUser = GameUser::firstOrNew([
                'game_id' => $this->game->id,
                'user_id' => $receiverId
            ]);

            if ($gameUser->exists) {
                $res = $this->conn()->patch('/admin/players/' . $gameUser->user->playerId . '/scores', [], [
                    'rewards' => $rewards
                ]);

                $gameUser->jsonUpdate($res);
            }
        }
        
        return $receiverRewards;
    }
}
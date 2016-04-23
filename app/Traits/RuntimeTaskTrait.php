<?php namespace App\Traits;

use App\Events\NewTriggersAvailable;
use App\Events\TriggerPlayed;
use App\Models\Action;
use App\Models\FileManager;
use Event;
use Request;
use App\Models\Challenge;
use App\Models\Submission;
use App\Models\Task;
use App\Models\TaskHistory;
use App\Models\ChallengeHistory;
use App\Models\ProcessHistory;

trait RuntimeTaskTrait
{
    static public function refreshTriggers(ProcessHistory $processHistory)
    {
        $triggers = Task::connByGame($processHistory->game)
            ->get('/runtime/processes/' . $processHistory->id_in_engine . '/triggers', [
                'player_id' => $processHistory->user->playerId
            ]);

        // fire event
        Event::fire(new NewTriggersAvailable($processHistory, $triggers));
    }


    static public function saveTriggers(ProcessHistory $processHistory, $triggers = [])
    {
        // put all non-completed taskHistory to be 'completed' first
        TaskHistory::where('process_history_id', $processHistory->id)
            ->where('status', '<>', Task::STATUS_COMPLETED)
            ->update([
                'status' => Task::STATUS_COMPLETED
            ]);

        // then re-active or create taskHistory
        foreach ($triggers as $trigger) {
            // trigger format: Name:Gate
            $code = explode(':', array_get($trigger, 'trigger'))[0];

            // choose corresponding status
            $status = (array_get($trigger, 'loop.total') == 'Infinity') ? Task::STATUS_LOOPING : Task::STATUS_ACTIVE;

            // try to get existing taskHistory or create a new one
            $taskHistory = TaskHistory::firstOrNew([
                'process_history_id' => $processHistory->id,
                'code' => $code
            ]);

            if ($taskHistory->exists) {
                // update taskHistory if exists
                $taskHistory->update([
                    'trigger' => $trigger['trigger'],
                    'status' => $status,
                ]);

            } else {
                // create history if does not exist
                // find task
                $task = Task::firstOrNew([
                    'process_id' => $processHistory->process->id,
                    'code' => $code
                ]);

                // create corresponding history
                TaskHistory::create(array_merge(
                    [
                        'game_id' => $processHistory->game_id,
                        'process_history_id' => $processHistory->id,
                        'user_id' => $processHistory->user_id,
                        'task_id' => $task->id,
                        'trigger' => $trigger['trigger'],
                        'status' => $status,
                        'json_remote' => json_encode($trigger)
                    ],
                    $task->getAttributesOnly(['game_code', 'process_code', 'code', 'fullcode'])));
            }
        }
    }


    static public function saveSubmissions(TaskHistory $taskHistory)
    {
        // increase attempt by 1
        $attempt = $taskHistory->maxAttempt + 1;

        // create challenge history
        foreach ($taskHistory->task->challenges as $challenge) {
            if (Request::hasFile($challenge->id)) {
                // .................................
                // if current submit is file
                // .................................
                $file = Request::file($challenge->id);
                $extension = FileManager::getExtension($file);

                $path = FileManager::generateRelPath($challenge->gameCode(), FileManager::FOLDER_USER, $extension);
                FileManager::put($path, $file);
                $submission = $path;

            } else {
                // .................................
                // if current submit is normal input
                // .................................
                $submission = Request::get($challenge->id);
            }

            // if no operator set for challenge, default will be FREE
            $operator = $challenge->jsonGet('operator', Challenge::OPERATOR_FREE);

            // save to DB
            Submission::create([
                'game_id' => $taskHistory->game->id,
                'challenge_id' => $challenge->id,
                'task_history_id' => $taskHistory->id,
                'user_id' => $taskHistory->user_id,
                'attempt' => $attempt,
                'submission' => $submission,
                'label' => $challenge->jsonGet('label'),
                'operator' => $operator,
                'solution' => $challenge->jsonGet('solution'),
                'result' => self::marking($submission, $operator, $challenge->jsonGet('solution'))
            ]);
        }
    }


    static public function marking($submission, $operator, $solution)
    {
        if (($operator != Challenge::OPERATOR_FREE) && is_null($solution)) {
            // skip marking if operator is not free AND solution is null
            return null;
        }

        // check submission with solution by operator
        switch ($operator) {
            case Challenge::OPERATOR_GT:
                // greater than
                $result = ($submission > $solution);
                break;

            case Challenge::OPERATOR_LT:
                // less than
                $result = ($submission < $solution);
                break;

            case Challenge::OPERATOR_NLT:
                // no less than
                $result = ($submission >= $solution);
                break;

            case Challenge::OPERATOR_NGT:
                // no greater than
                $result = ($submission <= $solution);
                break;

            case Challenge::OPERATOR_EQ:
                // equal
                $result = (strtoupper($submission) == strtoupper($solution));
                break;

            default:
                // default is: free
                $result = 1;
        }

        return $result;
    }


    static public function result(TaskHistory $taskHistory, $attempt = null)
    {
        // if no attempt give, then assume latest one
        $attempt = $attempt ?: $taskHistory->maxAttempt;

        // if there is any submission with result = null
        if (Submission::where('task_history_id', $taskHistory->id)
            ->where('attempt', $attempt)
            ->whereNull('result')
            ->exists()
        ) {
            return Task::RESULT_PENDING;
        }

        // get total count of correct submissions
        $score = Submission::where('task_history_id', $taskHistory->id)
            ->where('result', 1)
            ->where('attempt', $attempt)
            ->count();

        // get pass score
        $passScore = $taskHistory->task->challengeSet->jsonGet('pass_score', 0);

        // END
        return ($score >= $passScore) ? Task::RESULT_PASS : Task::RESULT_FAIL;
    }



    static public function playTrigger(TaskHistory $taskHistory)
    {
        $processHistory = $taskHistory->processHistory;
        $game = $processHistory->game;

        // play trigger in Playlyfe and get response
        $response = self::connByGame($game)
            ->post('/runtime/processes/' . $processHistory->id_in_engine . '/play', [
                'player_id' => $taskHistory->user->playerId
            ], [
                'trigger' => $taskHistory->trigger
            ]);

        // fire event
        $triggers = array_get($response, 'triggers');
        Event::fire(new NewTriggersAvailable($processHistory, $triggers));

        // update task history status
        if ($taskHistory->status == Task::STATUS_LOOPING) {
            // if current taskHistory is looping
            // update loop_count++

        } else {
            // if current taskHistory is not looping
            // update status = completed
            $taskHistory->update(['status' => Task::STATUS_COMPLETED]);
        }

        // process events
        $events = array_merge(
            array_get($response, 'events.local'),
            array_get($response, 'events.global')
        );

        // fire event
        Event::fire(new TriggerPlayed($taskHistory));

        // END
        return array_pluck($events, 'changes.0');
    }



    static public function processReward(TaskHistory $taskHistory, $result)
    {
        $task = $taskHistory->task;

        // 1a. reward
        $actionListString = $task->jsonGet('reward', '');


        if ($result == Task::RESULT_PASS) {
        // 1b. reward_pass
            $actionListString .= (';' . $task->jsonGet('reward_pass', ''));


        } else if ($result == Task::RESULT_FAIL) {
        // 1c. reward_fail
            $actionListString .= (';' . $task->jsonGet('reward_fail', ''));
        }

        // 2. play actions
        return Action::playActions($taskHistory, $actionListString);
    }
}
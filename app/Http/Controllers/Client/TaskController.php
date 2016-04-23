<?php namespace App\Http\Controllers\Client;

use App\Models\TaskHistory;
use JWTAuth;
use Response;
use Request;
use App\Models\Task;
use App\Models\Process;
use App\Models\Game;
use App\Models\Metric;
use App\Http\Controllers\Controller;

class TaskController extends Controller
{
    public function getList($gameId, $processId)
    {
        $user = JWTAuth::parseToken()->toUser();
        $game = Game::findOrNew($gameId);
        $process = Process::findOrNew($processId);

        // create history if does not exist yet
        $process->historyByGameAndUser($game, $user);

        $items = [];
        foreach ($process->playerTasks as $task) {
            $taskHistory = $task->historyByGameAndUser($game, $user);

            array_push($items, array_merge($task->outline(), [
                'status' => $taskHistory->exists ? $taskHistory->runtimeStatus : 'locked',
                'history' => $taskHistory->exists ? $taskHistory->outline() : null
            ]));
        }

        return Response::json([
            'items' => $items,
            'parent' => $process->detail()
        ]);
    }


    public function getDetail($gameId, $taskId)
    {
        $user = JWTAuth::parseToken()->toUser();
        $task = Task::findOrNew($taskId);
        $game = Game::findOrNew($gameId);
        $taskHistory = $task->historyByGameAndUser($game, $user);

        // END
        return Response::json([
            'item' => array_merge($task->detail(), [
                'history' => $taskHistory->detail()
            ])
        ]);
    }


    public function getAllActivities($gameId, $taskId)
    {
        $user = JWTAuth::parseToken()->toUser();

        $taskHistories = TaskHistory::where(['game_id' => $gameId, 'task_id' => $taskId,])
            ->where('user_id', '<>', $user->id)
            ->orderBy('created_at', 'DESC')
            ->get();

        $jsonArray = [];
        foreach ($taskHistories as $taskHistory) {
            array_push($jsonArray, $taskHistory->detail());
        }

        // END
        return Response::json([
            'items' => $jsonArray
        ]);
    }


    public function postPlay($gameId, $taskId)
    {
        $user = JWTAuth::parseToken()->toUser();
        $game = Game::findOrNew($gameId);
        $task = Task::findOrNew($taskId);
        $taskHistory = $task->historyByGameAndUser($game, $user);

        // default msg
        $msg = [
            Task::RESULT_PASS => 'Pass',
            Task::RESULT_FAIL => 'Fail',
            Task::RESULT_PENDING => 'Pending',
        ];

        // if task completed already then return with error msg
        if ($taskHistory->status == 'completed') {
            return Response::json(['message' => 'Task completed already.'], 406);
        }

        // 1. save submissions and meanwhile do marking
        // Facade Request will be used in the function
        Task::saveSubmissions($taskHistory);


        // 2a. get result
        $result = Task::result($taskHistory);

        // 2b. processReward
        Task::processReward($taskHistory, $result);

        // 2c. get is_blocking
        $isBlocking = $task->jsonGet('is_blocking', false);

        // 3. play remote trigger if result = pass or isBlocking = false
        $changes = [];
        if ($result == Task::RESULT_PASS || !$isBlocking) {
            $changes = Task::playTrigger($taskHistory);
        }

        // 4. consolidate process changes
        array_walk($changes, function (&$value, $key, $game) {
            $metricFullCode = $game->code . '.' . array_get($value, 'metric.id');
            $metric = Metric::firstOrNew(['fullcode' => $metricFullCode]);

            array_set($value, 'metric.name', $metric->jsonGet('name'));
            array_set($value, 'metric.fullcode', $metric->jsonGet('fullcode'));
            array_set($value, 'metric.description', $metric->jsonGet('description'));
        }, $game);


        // END
        return Response::json([
            'msg' => $task->challengeSet->jsonGet($result . '_msg', $msg[$result]),
            'changes' => $changes,
        ]);
    }
}
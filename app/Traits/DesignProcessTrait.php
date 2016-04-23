<?php namespace App\Traits;

use App\Models\JsonSchema;
use App\Models\Process;
use App\Models\Task;

trait DesignProcessTrait
{
    static public function syncList($game)
    {
        // get headers
        $headers = Process::connByGame($game)->get('/design/versions/latest/processes');

        // delete those not exist
        Process::where('game_id', $game->id)
            ->whereNotIn('remote_id', array_column($headers, 'id'))
            ->delete();

        // first or create
        foreach ($headers as $header) {
            Process::firstOrCreate([
                'game_id' => $game->id,
                'remote_id' => $header['id']
            ]);
        }
    }


    public function syncDesign()
    {
        $res = $this->conn()->get('/design/versions/latest/processes/' . $this->remote_id);

        // 1 .===============================
        // Process
        // remove local attributes and update
        $processUpdateJson = array_except($res, JsonSchema::names('process', 'edit'));
        $this->jsonUpdate($processUpdateJson);


        // 2 .===============================
        // Task
        $items = array_get($res, 'activities');
        Task::where('process_id', $this->id)
            ->whereNotIn('remote_id', array_column($items, 'id'))
            ->delete();

        // UpdateOrCreate tasks
        foreach ($items as $order => $item) {
            $task = Task::firstOrCreate([
                'process_id' => $this->id,
                'remote_id' => $item['id']
            ]);

            // remove local attributes and update
            $taskUpdateJson = array_except($item, JsonSchema::names('task', 'edit'));
            $task->jsonUpdate($taskUpdateJson)->update(['sequence' => ($order + 1)]);
        }

        return $this;
    }
}
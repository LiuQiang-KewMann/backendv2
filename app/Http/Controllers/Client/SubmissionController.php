<?php namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Submission;

class SubmissionController extends Controller
{
    /*
     * get list by a given taskHistory
     */
    public function getList($taskHistoryId)
    {
        $submissions = Submission::where([
            'task_history_id' => $taskHistoryId
        ])->get();

        return ['items' => $submissions];
    }
}
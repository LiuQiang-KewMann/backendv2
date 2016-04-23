<?php namespace App\Listeners;

use App\Events\Event;
use App\Models\FileManager;
use App\Models\Process;
use App\Models\ProcessHistory;
use App\Models\Submission;

class ClearGameHistory
{
    public function __construct()
    {
        //
    }

    public function handle(Event $event)
    {
        $game = $event->game;
        $user = $event->user;

        // delete file uploaded in submission
        $submissionsWithUpload = Submission::where('user_id', $user->id)
            ->where('game_id', $game->id)
            ->where('submission', 'LIKE', 'data/%')
            ->get();

        foreach ($submissionsWithUpload as $submission) {
            $path = $submission->submission;
            FileManager::delete($path);
        }

        // get current open process instances
        $openProcessInstances = Process::openProcessInstances($game, $user);
        $openProcessInstanceIds = array_column($openProcessInstances['data'], 'id');

        // delete processHistory in engine if still open
        $openProcessHistories = ProcessHistory::whereIn('id_in_engine', $openProcessInstanceIds)->get();
        foreach ($openProcessHistories as $openProcessHistory) {
            Process::deleteInstanceInEngine($openProcessHistory);
        }

        // delete processHistory will delete taskHistory and submissions
        ProcessHistory::where('user_id', $user->id)->where('game_id', $game->id)->delete();
    }
}
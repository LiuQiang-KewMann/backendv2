<?php

namespace App\Listeners;

use App\Events\ProcessHistoryCreated;
use App\Models\Task;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class RefreshTriggers
{

    public function __construct()
    {
        //
    }


    public function handle(ProcessHistoryCreated $event)
    {
        $processHistory = $event->processHistory;
        Task::refreshTriggers($processHistory);
    }
}

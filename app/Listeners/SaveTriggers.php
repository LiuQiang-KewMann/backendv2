<?php

namespace App\Listeners;

use App\Events\NewTriggersAvailable;
use App\Models\Task;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SaveTriggers
{

    public function __construct()
    {
        //
    }


    public function handle(NewTriggersAvailable $event)
    {
        $processHistory = $event->processHistory;
        $triggers = $event->triggers;

        Task::saveTriggers($processHistory, $triggers);
    }
}
<?php

namespace App\Listeners;

use App\Events\TriggerPlayed;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class RefreshProfile
{
    public function __construct()
    {
        //
    }


    public function handle(TriggerPlayed $event)
    {
        $taskHistory = $event->taskHistory;
        User::refreshProfile($taskHistory->game, $taskHistory->user);
    }
}
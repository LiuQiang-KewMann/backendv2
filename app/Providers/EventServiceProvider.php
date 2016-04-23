<?php namespace App\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\ProcessHistoryCreated' => [
            'App\Listeners\RefreshTriggers',
        ],
        'App\Events\NewTriggersAvailable' => [
            'App\Listeners\SaveTriggers',
        ],
        'App\Events\TriggerPlayed' => [
            'App\Listeners\RefreshProfile',
        ],
        'App\Events\ImageUploaded' => [
            'App\Listeners\UpdateImageRelPathAttribute'
        ],
        'App\Events\PlayerRemovedFromGame' => [
            'App\Listeners\RemovePlayerInEngine',
            'App\Listeners\ClearGameHistory'
        ],
        'App\Events\PlayerReset' => [
            'App\Listeners\ResetPlayerInEngine',
            'App\Listeners\ClearGameHistory'
        ],
        'App\Events\PasswordResetTokenCreated' => [
            'App\Listeners\SendPasswordResetEmail',
        ]
    ];


    /**
     * The subscriber classes to register
     *
     * @var array
     */
    protected $subscribe = [
        'App\Listeners\UpdateCommentCount'
    ];


    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot(DispatcherContract $events)
    {
        parent::boot($events);
    }
}

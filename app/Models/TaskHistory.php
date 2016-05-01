<?php namespace App\Models;

use App\Traits\PlaylyfeTrait;
use App\Traits\RuntimeRewardTrait;
use App\Traits\RuntimeTaskHistoryTrait;
use App\Traits\JsonTrait;

class TaskHistory extends BaseModel
{
    use JsonTrait;
    use PlaylyfeTrait;
    use RuntimeTaskHistoryTrait;
    use RuntimeRewardTrait;

    public $timestamps = true;

    const STATUS_NON_LOOPING = 'non_looping';
    const STATUS_LOOPING = 'looping';
    const STATUS_COMPLETED = 'completed';
    const STATUS_EXPIRED = 'expired';
    const STATUS_UNRELEASED = 'unreleased';

    public static function boot()
    {
        parent::boot();

        TaskHistory::creating(function (TaskHistory $taskHistory) {
            $maxLoopCount = $taskHistory->task->jsonGet('max_loop_count');

            $taskHistory->loop_count = 1;
            $taskHistory->attempt = 1;
            $taskHistory->status = $maxLoopCount ? TaskHistory::STATUS_LOOPING : TaskHistory::STATUS_NON_LOOPING;
            $taskHistory->game_id = $taskHistory->gameUser->game_id;
            $taskHistory->user_id = $taskHistory->gameUser->user_id;
        });

        TaskHistory::saving(function (TaskHistory $taskHistory) {
            // get all changed attributes
            $updatedAttributes = array_keys($taskHistory->getDirty());

            if (in_array('status', $updatedAttributes)) {
                // if status changed
                if (in_array($taskHistory->status, [TaskHistory::STATUS_COMPLETED, TaskHistory::STATUS_EXPIRED])) {
                    // if status changed to completed or expired
                    // do remote completion
                    $taskHistory->doRemoteCompletion();

                    // and give out rewards
                    $taskHistory->giveOutRewards();
                }
            }
        });
    }


    public function toArray()
    {
        return parent::brief([
            'title',
            'status',
            'image',
            'image_thumb',
            'db_id'
        ]);
    }


    public function detail($additionalAttributes = [])
    {
        $array = parent::detail(['status']);

        $array = array_merge($array, $this->task->brief([
            'title',
            'image',
            'image_thumb',
            'challenge_db_id'
        ]));

        return $array;
    }


    public function gameUser()
    {
        return $this->belongsTo('App\Models\GameUser');
    }

    public function game()
    {
        return $this->belongsTo('App\Models\Game');
    }

    public function task()
    {
        return $this->belongsTo('App\Models\Task');
    }


    public function user()
    {
        return $this->belongsTo('App\User');
    }


    public function processHistory()
    {
        return $this->belongsTo('App\Models\ProcessHistory');
    }


    public function getRewardsAttribute()
    {
        return Reward::where([
            'belongs_to_class' => Task::class,
            'belongs_to_id' => $this->task->id
        ])->orderBy('reward_dispatcher_id')->get();
    }
}
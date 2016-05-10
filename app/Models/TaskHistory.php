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
            $taskHistory->remote_completed = 0;
            $taskHistory->status = $maxLoopCount ? TaskHistory::STATUS_LOOPING : TaskHistory::STATUS_NON_LOOPING;
            $taskHistory->game_id = $taskHistory->gameUser->game_id;
            $taskHistory->user_id = $taskHistory->gameUser->user_id;
        });

        TaskHistory::saving(function (TaskHistory $taskHistory) {
            // if status is completed or expired and remote_completed == 0
            if (($taskHistory->status == self::STATUS_COMPLETED || $taskHistory->status == self::STATUS_EXPIRED) &&
                $taskHistory->remote_completed == 0
            ) {
                // do remote completion
                $taskHistory->doRemoteCompletion();

                // and give out rewards
                $rewards = $taskHistory->giveOutRewards();

                // add rewards to json array if got any rewards
                if (sizeof($rewards)) {
                    $jsonArray = $taskHistory->jsonArray();
                    array_set($jsonArray, 'rewards', $rewards);

                    $taskHistory->json = json_encode($jsonArray);
                }
            }
        });
    }


    public function detail($additionalAttributes = [])
    {
        $array = parent::detail(['status']);

        $array = array_merge($array, $this->task->brief([
            'title',
            'image',
            'image_thumb',
            'challenge_db_id',
            'social_disabled'
        ]));

        return $array;
    }


    public function toArray()
    {
        return parent::brief([
            'title',
            'status',
            'image',
            'image_thumb',
            'db_id',
            'social_count',
            'social_disabled'
        ]);
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


    public function getCommentsAttribute()
    {
        return Comment::where([
            'task_history_id' => $this->id,
            'type' => Comment::TYPE_COMMENT
        ])->orderBy('updated_at', 'DESC')->get();
    }


    public function getLikesAttribute()
    {
        return Comment::where([
            'task_history_id' => $this->id,
            'type' => Comment::TYPE_LIKE
        ])->orderBy('updated_at', 'DESC')->get();
    }
}
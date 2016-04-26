<?php namespace App\Models;

use App\Traits\PlaylyfeTrait;
use App\Traits\RuntimeTaskHistoryTrait;
use App\Traits\JsonTrait;
use App\Traits\ProcessTrait;

class TaskHistory extends BaseModel
{
    use JsonTrait;
    use PlaylyfeTrait;
    use RuntimeTaskHistoryTrait;

    public $timestamps = true;

    const STATUS_NON_LOOPING = 'non_looping';
    const STATUS_LOOPING = 'looping';
    const STATUS_COMPLETED = 'completed';

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

        TaskHistory::saved(function (TaskHistory $taskHistory) {
            $updatedAttributes = array_keys($taskHistory->getDirty());
            
            // do remote completion
            if (in_array('status', $updatedAttributes) && $taskHistory->status == TaskHistory::STATUS_COMPLETED) {
                $taskHistory->doRemoteCompletion();
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














//    public function submissions($attempt = null, $unmarkedOnly = false)
//    {
//        $query = Submission::where([
//            'task_history_id' => $this->id,
//            'attempt' => $attempt ?: $this->maxAttempt,
//        ]);
//
//        return $unmarkedOnly ?
//            $query->whereNull('result')->get() :
//            $query->get();
//    }
//
//    public function lastSubmission($attempt = null)
//    {
//        return Submission::where([
//            'task_history_id' => $this->id,
//            'attempt' => $attempt ?: $this->maxAttempt,
//        ])->orderBy('updated_at', 'DESC')->first();
//    }
//
//
//
//    public function getRuntimeStatusAttribute()
//    {
//        $status = $this->status;
//        $maxAttempt = $this->maxAttempt;
//
//        // if current taskHistory is still in looping
//        if ($status == Task::STATUS_LOOPING) {
//            $timeBound = $this->task->jsonGet('time_bound');
//            $countBound = $this->task->jsonGet('count_bound');
//
//            $countBoundReached = $countBound ? ($maxAttempt >= $countBound) : false;
//            $timeBoundReached = false;
//
//            if ($timeBound) {
//                try {
//                    $deadline = Carbon::createFromFormat('Y-m-d H:i:s', $timeBound);
//                    $diffInSeconds = $deadline->diffInSeconds(null, false);
//
//                    // if diffInSeconds >= 0, which means timeBound reached
//                    $timeBoundReached = ($diffInSeconds >= 0);
//
//                } catch (Exception $e) {
//                    // do nothing...
//                }
//            }
//
//            if ($countBoundReached || $timeBoundReached) {
//                return Task::STATUS_COMPLETED;
//            }
//        }
//
//        // else return db status
//        return $status;
//    }
}
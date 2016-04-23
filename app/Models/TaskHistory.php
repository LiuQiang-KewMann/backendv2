<?php namespace App\Models;

use Carbon\Carbon;
use App\Traits\JsonTrait;
use App\Traits\ProcessTrait;
use Mockery\CountValidator\Exception;

class TaskHistory extends BaseModel
{
    use JsonTrait;


    protected $guarded = ['id'];


    public function detail($mixedOnly = true, $toBeMerged = [])
    {
        $currentYear = Carbon::now()->year;
        $dateFormat = ($this->updated_at->year == $currentYear)? 'j M H:i': 'j M Y H:i';

        $lastSubmission = $this->lastSubmission();
        $detail = parent::detail($mixedOnly, array_merge($toBeMerged, [
            'date' => $lastSubmission? $lastSubmission->updated_at->format($dateFormat) : null
        ]));

        // get player info
        $playerInfo = $this->user->detail();
        array_set($detail, 'player', $playerInfo);

        // submissions
        $submissions = [];
        $lastSubmissionDate = null;
        foreach($this->submissions() as $submission) {
            array_push($submissions, $submission->detail());
        }
        array_set($detail, 'latest_submissions', $submissions);

        return $detail;
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


    public function game()
    {
        return $this->belongsTo('App\Models\Game');
    }


    public function getRuntimeStatusAttribute()
    {
        $status = $this->status;
        $maxAttempt = $this->maxAttempt;

        // if current taskHistory is still in looping
        if($status == Task::STATUS_LOOPING) {
            $timeBound = $this->task->jsonGet('time_bound');
            $countBound = $this->task->jsonGet('count_bound');

            $countBoundReached = $countBound? ($maxAttempt >= $countBound): false;
            $timeBoundReached = false;

            if($timeBound) {
                try {
                    $deadline = Carbon::createFromFormat('Y-m-d H:i:s', $timeBound);
                    $diffInSeconds = $deadline->diffInSeconds(null, false);

                    // if diffInSeconds >= 0, which means timeBound reached
                    $timeBoundReached = ($diffInSeconds >= 0);

                } catch (Exception $e) {
                    // do nothing...
                }
            }

            if($countBoundReached || $timeBoundReached) {
                return Task::STATUS_COMPLETED;
            }
        }

        // else return db status
        return $status;
    }


    public function getMaxAttemptAttribute()
    {
        $query = Submission::where('task_history_id', $this->id);
        return $query->exists() ? $query->max('attempt') : 0;
    }


    public function submissions($attempt = null, $unmarkedOnly = false)
    {
        $query = Submission::where([
            'task_history_id' => $this->id,
            'attempt' => $attempt ?: $this->maxAttempt,
        ]);

        return $unmarkedOnly ?
            $query->whereNull('result')->get() :
            $query->get();
    }

    public function lastSubmission($attempt = null)
    {
        return Submission::where([
            'task_history_id' => $this->id,
            'attempt' => $attempt ?: $this->maxAttempt,
        ])->orderBy('updated_at', 'DESC')->first();
    }
}
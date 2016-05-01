<?php namespace App\Models;

use App\Traits\JsonTrait;
use App\Traits\RuntimeSubmissionTrait;

class Submission extends BaseModel
{
    use RuntimeSubmissionTrait;
    use JsonTrait;

    public $timestamps = true;

    public static function boot()
    {
        parent::boot();

        Submission::creating(function (Submission $submission) {
            $submission->game_user_id = $submission->taskHistory->game_user_id;
            $submission->game_id = $submission->gameUser->game_id;
            $submission->user_id = $submission->gameUser->user_id;
            $submission->loop_count = $submission->taskHistory->loop_count;
            $submission->attempt = $submission->taskHistory->attempt;
            $submission->operator = $submission->component->jsonGet('operator', Component::OPERATOR_FREE);

            // copy component json to submission
            $submission->json = $submission->component->json;

            // do marking
            $submission->result = $submission->marking();
        });
    }


    public function taskHistory()
    {
        return $this->belongsTo('App\Models\TaskHistory');
    }


    public function component()
    {
        return $this->belongsTo('App\Models\Component');
    }


    public function gameUser()
    {
        return $this->belongsTo('App\Models\GameUser');
    }


    public function game()
    {
        return $this->belongsTo('App\Models\Game');
    }


    public function user()
    {
        return $this->belongsTo('App\User');
    }


    public function detail($additionalAttributes = [])
    {
        $array = parent::detail([
            'label',
            'submission'
        ]);

        // if submission is boolean, cast string to PHP boolean type
        if (array_get($array, 'submission') === 'true') {
            array_set($array, 'submission', true);

        } else if (array_get($array, 'submission') === 'false') {
            array_set($array, 'submission', true);
        }

        return $array;
    }


    public function toArray()
    {
        return parent::brief([], [
            'solution'
        ]);
    }
}
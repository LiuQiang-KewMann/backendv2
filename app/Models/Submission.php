<?php namespace App\Models;

use App\Traits\RuntimeSubmissionTrait;
use Carbon\Carbon;

class Submission extends BaseModel
{
    use RuntimeSubmissionTrait;

    public $timestamps = true;

    public static function boot()
    {
        parent::boot();

        Submission::creating(function (Submission $submission) {
            $submission->game_user_id = $submission->taskHistory->game_user_id;
            $submission->game_id = $submission->gameUser->game_id;
            $submission->user_id = $submission->gameUser->user_id;
            $submission->label = $submission->component->jsonGet('label');
            $submission->operator = $submission->component->jsonGet('operator', Component::OPERATOR_FREE);
            $submission->solution = $submission->component->jsonGet('solution');
            $submission->loop_count = $submission->taskHistory->loop_count;
            $submission->attempt = $submission->taskHistory->attempt;

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


//    public function detail($mixedOnly = true, $toBeMerged = [])
//    {
//        $currentYear = Carbon::now()->year;
//        $dateFormat = ($this->updated_at->year == $currentYear) ? 'j M H:i' : 'j M Y H:i';
//
//        $submission = array_merge([
//            'id' => $this->id,
//            'label' => $this->label,
//            'attempt' => $this->attempt,
//            'submission' => $this->submission,
//            'result' => $this->result,
//            'datetime' => $this->updated_at,
//            'datetime_string' => $this->updated_at->format($dateFormat)
//        ], [
//            'mixed' => array_except($this->challenge->detail()['mixed'], ['solution'])
//        ]);
//
//        // cast submission for toggle type
//        if (in_array(array_get($submission, 'mixed.type'), ['toggle'])) {
//            array_set($submission, 'submission', (bool)$this->submission);
//        }
//
//        return $submission;
//    }
}
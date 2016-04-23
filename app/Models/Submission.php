<?php namespace App\Models;

use Carbon\Carbon;

class Submission extends BaseModel
{
    protected $guarded = ['id'];


    public function taskHistory()
    {
        return $this->belongsTo('App\Models\TaskHistory');
    }


    public function challenge()
    {
        return $this->belongsTo('App\Models\Challenge');
    }


    public function detail($mixedOnly = true, $toBeMerged = [])
    {
        $currentYear = Carbon::now()->year;
        $dateFormat = ($this->updated_at->year == $currentYear) ? 'j M H:i' : 'j M Y H:i';

        $submission = array_merge([
            'id' => $this->id,
            'label' => $this->label,
            'attempt' => $this->attempt,
            'submission' => $this->submission,
            'result' => $this->result,
            'datetime' => $this->updated_at,
            'datetime_string' => $this->updated_at->format($dateFormat)
        ], [
            'mixed' => array_except($this->challenge->detail()['mixed'], ['solution'])
        ]);

        // cast submission for toggle type
        if (in_array(array_get($submission, 'mixed.type'), ['toggle'])) {
            array_set($submission, 'submission', (bool)$this->submission);
        }

        return $submission;
    }
}
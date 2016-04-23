<?php namespace App\Models;

use App\Traits\JsonTrait;
use App\Traits\RuntimeCommentTrait;
use Carbon\Carbon;

class Comment extends BaseModel
{
    use JsonTrait;
    use RuntimeCommentTrait;

    const TYPE_LIKE = 'like';
    const TYPE_COMMENT = 'comment';

    protected $guarded = ['id'];


    public function outline($toBeMerged = [], $arrayOnly = [])
    {
        $currentYear = Carbon::now()->year;
        $dateFormat = ($this->updated_at->year == $currentYear)? 'j M H:i': 'j M Y H:i';

        $toBeMerged = array_merge([
            'actor_id' => $this->actor_id,
            'actor_name' => $this->actor->jsonGet('first_name'),
            'date' => $this->updated_at->format($dateFormat)
        ], $toBeMerged);

        $outline = parent::outline($toBeMerged, $arrayOnly);

        return $outline;
    }


    public function taskHistory()
    {
        return $this->belongsTo('App\Models\TaskHistory');
    }


    public function actor()
    {
        return $this->belongsTo('App\User');
    }
}
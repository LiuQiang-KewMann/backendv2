<?php namespace App\Models;

use App\Traits\JsonTrait;
use App\Traits\RuntimeCommentTrait;
use Carbon\Carbon;

class Comment extends BaseModel
{
    use JsonTrait;
    use RuntimeCommentTrait;

    public $timestamps = true;

    const TYPE_LIKE = 'like';
    const TYPE_COMMENT = 'comment';

    public static function boot()
    {
        parent::boot();

        Comment::creating(function (Comment $comment) {
            // fill in additional attributes when creating
            $comment->actor_game_id = $comment->actor->game_id;
            $comment->actor_user_id = $comment->actor->user_id;
        });


        Comment::created(function (Comment $comment) {
            // calculate count for this taskHistory
            $count = Comment::where([
                'task_history_id' => $comment->task_history_id,
                'type' => $comment->type
            ])->count();

            // taskHistory json for social is social_count[like/comment]
            $comment->taskHistory->jsonSet('social_count.' . $comment->type, $count);
        });


        Comment::deleted(function (Comment $comment) {
            // calculate count for this taskHistory
            $count = Comment::where([
                'task_history_id' => $comment->task_history_id,
                'type' => $comment->type
            ])->count();

            // taskHistory json for social is social_count[like/comment]
            $comment->taskHistory->jsonSet('social_count.' . $comment->type, $count);
        });
    }

    public function detail($additionalAttributes = [])
    {
        $currentYear = Carbon::now()->year;
        $dateFormat = ($this->updated_at->year == $currentYear) ? 'j M H:i' : 'j M Y H:i';

        $array = parent::detail($additionalAttributes);

        $array = array_merge($array, [
            'actor' => $this->actor->toArray(),
            'date' => $this->updated_at->format($dateFormat)
        ]);

        return $array;
    }


    public function toArray()
    {
        return parent::brief([
            'actor',
            'date',
            'comment',
            'db_id'
        ]);
    }


    public function taskHistory()
    {
        return $this->belongsTo('App\Models\TaskHistory');
    }


    public function actor()
    {
        return $this->belongsTo('App\Models\GameUser', 'actor_game_user_id');
    }
}
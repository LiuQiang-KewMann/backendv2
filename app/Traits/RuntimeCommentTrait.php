<?php namespace App\Traits;

use App\Models\Comment;
use App\Models\GameUser;
use App\Models\TaskHistory;

trait RuntimeCommentTrait
{
    static public function like(TaskHistory $taskHistory, GameUser $actor)
    {
        return Comment::firstOrCreate([
            'task_history_id' => $taskHistory->id,
            'type' => Comment::TYPE_LIKE,
            'actor_game_user_id' => $actor->id
        ]);
    }


    static public function unlike(TaskHistory $taskHistory, GameUser $actor)
    {
        Comment::firstOrNew([
            'task_history_id' => $taskHistory->id,
            'type' => Comment::TYPE_LIKE,
            'actor_game_user_id' => $actor->id
        ])->delete();
    }


    static public function post(TaskHistory $taskHistory, GameUser $actor, $comment)
    {
        return Comment::create([
            'task_history_id' => $taskHistory->id,
            'type' => Comment::TYPE_COMMENT,
            'actor_game_user_id' => $actor->id,
            'json' => json_encode(['comment' => $comment])
        ]);
    }
}
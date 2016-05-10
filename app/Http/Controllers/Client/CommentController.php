<?php namespace App\Http\Controllers\Client;

use App\Models\Comment;
use App\Models\GameUser;
use App\Models\TaskHistory;
use Response;
use Request;
use App\Http\Controllers\Controller;

class CommentController extends Controller
{
    // like
    public function postLike($taskHistoryId)
    {
        $taskHistory = TaskHistory::find($taskHistoryId);

        $gameUser = GameUser::firstOrNew([
            'game_id' => $taskHistory->game_id,
            'user_id' => $this->user->id,
            'role' => GameUser::ROLE_PLAYER,
        ]);

        Comment::like($taskHistory, $gameUser);

        return ['items' => $taskHistory->likes];
    }


    // unlike
    public function postUnlike($taskHistoryId)
    {
        $taskHistory = TaskHistory::find($taskHistoryId);

        $gameUser = GameUser::firstOrNew([
            'game_id' => $taskHistory->game_id,
            'user_id' => $this->user->id,
            'role' => GameUser::ROLE_PLAYER,
        ]);

        Comment::unlike($taskHistory, $gameUser);

        return ['items' => $taskHistory->likes];
    }


    // post comment
    public function postPost($taskHistoryId)
    {
        $taskHistory = TaskHistory::findOrNew($taskHistoryId);
        $comment = Request::get('comment');

        $gameUser = GameUser::firstOrNew([
            'game_id' => $taskHistory->game_id,
            'user_id' => $this->user->id,
            'role' => GameUser::ROLE_PLAYER,
        ]);

        Comment::post($taskHistory, $gameUser, $comment);

        return ['items' => $taskHistory->comments];
    }


    // delete comment
    public function postDelete($id)
    {
        $comment = Comment::find($id);
        $comment->delete();

        return [
            'msg' => 'comment_deleted',
            'items' => $comment->taskHistory->comments
        ];
    }


    // likes
    public function getLikes($taskHistoryId)
    {
        $taskHistory = TaskHistory::find($taskHistoryId);

        return ['items' => $taskHistory->likes];
    }


    // comments
    public function getComments($taskHistoryId)
    {
        $taskHistory = TaskHistory::find($taskHistoryId);

        return ['items' => $taskHistory->comments];

    }


    // is liked by user already or not
    public function getLiked($taskHistoryId)
    {
        $taskHistory = TaskHistory::find($taskHistoryId);

        $result = Comment::where([
            'task_history_id' => $taskHistoryId,
            'type' => Comment::TYPE_LIKE,
            'actor_game_id' => $taskHistory->game_id,
            'actor_user_id' => $this->user->id
        ])->exists();

        return ['result' => $result];
    }
}
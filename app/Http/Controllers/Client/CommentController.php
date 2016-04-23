<?php namespace App\Http\Controllers\Client;

use App\Models\Comment;
use App\Models\TaskHistory;
use JWTAuth;
use Response;
use Request;
use App\Http\Controllers\Controller;

class CommentController extends Controller
{
    // like
    public function getLike($taskHistoryId)
    {
        $user = JWTAuth::parseToken()->toUser();
        $taskHistory = TaskHistory::findOrNew($taskHistoryId);

        Comment::like($taskHistory, $user);

        return $this->getList(Comment::TYPE_LIKE, $taskHistoryId);
    }


    // unlike
    public function getUnlike($taskHistoryId)
    {
        $user = JWTAuth::parseToken()->toUser();
        $taskHistory = TaskHistory::findOrNew($taskHistoryId);

        Comment::unlike($taskHistory, $user);

        return $this->getList(Comment::TYPE_LIKE, $taskHistoryId);
    }


    // post comment
    public function postPost($taskHistoryId)
    {
        $user = JWTAuth::parseToken()->toUser();
        $taskHistory = TaskHistory::findOrNew($taskHistoryId);
        $comment = Request::get('comment');

        Comment::post($taskHistory, $user, $comment);

        return $this->getList(Comment::TYPE_COMMENT, $taskHistoryId);
    }


    // delete comment
    public function postDelete($commentId)
    {
        $comment = Comment::findOrNew($commentId);
        $taskHistoryId = $comment->task_history_id;

        Comment::deleteComment($comment);

        return $this->getList(Comment::TYPE_COMMENT, $taskHistoryId);
    }


    // list
    public function getList($type, $taskHistoryId)
    {
        if (!in_array($type, [Comment::TYPE_COMMENT, Comment::TYPE_LIKE])) {
            return Response::json(['msg' => 'Invalid type'], 406);
        }

        $user = JWTAuth::parseToken()->toUser();
        $taskHistory = TaskHistory::findOrNew($taskHistoryId);
        $commentList = Comment::listAll($taskHistory, $type);

        $arrayToBeMerged = [];

        if ($type == Comment::TYPE_LIKE) {
            array_set($arrayToBeMerged, 'is_liked', Comment::isLiked($taskHistory, $user));
        }

        $comments = [];
        foreach ($commentList as $comment) {
            array_push($comments, $comment->outline());
        }

        return Response::json(array_merge($arrayToBeMerged, [
            'count' => sizeof($comments),
            'current_actor_id' => $user->id,
            'items' => $comments,
        ]));
    }


    // is liked by user
    public function getIsLiked($taskHistoryId)
    {
        $user = JWTAuth::parseToken()->toUser();
        $taskHistory = TaskHistory::findOrNew($taskHistoryId);

        $isLike = Comment::isLiked($taskHistory, $user);

        return Response::json([
            'actor_id' => $user->id,
            'task_history_id' => $taskHistory->id,
            'value' => $isLike
        ]);
    }
}
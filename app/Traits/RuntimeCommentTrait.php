<?php namespace App\Traits;

trait RuntimeCommentTrait
{
//    static public function like(TaskHistory $taskHistory, User $actor)
//    {
//        $comment = Comment::firstOrNew([
//            'task_history_id' => $taskHistory->id,
//            'actor_id' => $actor->id,
//            'type' => Comment::TYPE_LIKE
//        ]);
//
//        // create like if not liked before by this actor
//        if (!$comment->exists) {
//            $comment = Comment::create([
//                'task_history_id' => $taskHistory->id,
//                'actor_id' => $actor->id,
//                'type' => Comment::TYPE_LIKE,
//            ]);
//
//            // fire event
//            Event::fire(new CommentPosted($comment));
//        }
//
//        return self::count($taskHistory);
//    }
//
//
//    static public function unlike(TaskHistory $taskHistory, User $actor)
//    {
//        $comment = Comment::firstOrNew([
//            'task_history_id' => $taskHistory->id,
//            'actor_id' => $actor->id,
//            'type' => Comment::TYPE_LIKE,
//        ]);
//
//        // delete like if exists
//        if ($comment->exists) {
//            $comment->delete();
//
//            // fire event
//            Event::fire(new CommentDeleted($taskHistory));
//        }
//
//        return self::count($taskHistory);
//    }
//
//
//    static public function deleteComment(Comment $comment)
//    {
//        $taskHistory = $comment->taskHistory;
//        $comment->delete();
//
//        // fire event
//        Event::fire(new CommentDeleted($taskHistory));
//    }
//
//
//    static public function post(TaskHistory $taskHistory, User $actor, $comment)
//    {
//        $comment = Comment::create([
//            'task_history_id' => $taskHistory->id,
//            'actor_id' => $actor->id,
//            'type' => Comment::TYPE_COMMENT,
//
//            'json_local' => json_encode([
//                'comment' => $comment
//            ])
//        ]);
//
//        // fire event
//        Event::fire(new CommentPosted($comment));
//
//        return self::count($taskHistory);
//    }
//
//
//    static public function isLiked(TaskHistory $taskHistory, User $actor)
//    {
//        return Comment::where([
//            'task_history_id' => $taskHistory->id,
//            'actor_id' => $actor->id,
//            'type' => Comment::TYPE_LIKE
//        ])->exists();
//    }
//
//
//    static public function count(TaskHistory $taskHistory, $type = Comment::TYPE_LIKE)
//    {
//        return Comment::where([
//            'task_history_id' => $taskHistory->id,
//            'type' => $type
//        ])->count();
//    }
//
//
//    static public function listAll(TaskHistory $taskHistory, $type = Comment::TYPE_LIKE)
//    {
//        return Comment::where([
//            'task_history_id' => $taskHistory->id,
//            'type' => $type
//        ])->orderBy('updated_at', 'DESC')->get();
//    }
}
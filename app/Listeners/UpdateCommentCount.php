<?php

namespace App\Listeners;

use App\Events\CommentDeleted;
use App\Events\CommentPosted;
use App\Models\Comment;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateCommentCount
{

    public function __construct()
    {
        //
    }


    public function onCommentPosted(CommentPosted $event)
    {
        $comment = $event->comment;
        $taskHistory = $comment->taskHistory;

        $taskHistory->jsonUpdate([
            'count_like' => Comment::count($taskHistory, Comment::TYPE_LIKE),
            'count_comment' => Comment::count($taskHistory, Comment::TYPE_COMMENT),
        ]);
    }


    public function onCommentDeleted(CommentDeleted $event)
    {
        $taskHistory = $event->taskHistory;

        $taskHistory->jsonUpdate([
            'count_like' => Comment::count($taskHistory, Comment::TYPE_LIKE),
            'count_comment' => Comment::count($taskHistory, Comment::TYPE_COMMENT),
        ]);
    }


    public function subscribe($events)
    {
        $events->listen(
            'App\Events\CommentPosted',
            'App\Listeners\UpdateCommentCount@onCommentPosted'
        );

        $events->listen(
            'App\Events\CommentDeleted',
            'App\Listeners\UpdateCommentCount@onCommentDeleted'
        );
    }
}
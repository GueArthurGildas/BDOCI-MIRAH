<?php

namespace Modules\Administration\Notifications;

use App\Models\Comment;
use App\Support\Notifications\Alert;
use App\Support\Notifications\AlertLevel;

class ConnectorCommentAlert extends Alert
{
    private readonly int $commentId;

    private readonly int $dataSourceId;

    private readonly string $connector;

    private readonly string $author;

    public function __construct(Comment $comment)
    {
        $this->commentId = $comment->id;
        $this->dataSourceId = $comment->object_id;
        $this->connector = $comment->object->name;
        $this->author = $comment->author->name;
    }

    public function key(): string
    {
        return "connectors.comment.{$this->commentId}";
    }

    protected function level(): AlertLevel
    {
        return AlertLevel::Info;
    }

    protected function icon(): string
    {
        return 'icon-message-square';
    }

    protected function translationKey(): string
    {
        return 'administration::alerts.connector_comment';
    }

    protected function params(): array
    {
        return ['connector' => $this->connector, 'author' => $this->author];
    }

    protected function target(): array
    {
        return ['administration.connectors.show', ['dataSource' => $this->dataSourceId, 'tab' => 'comments']];
    }
}

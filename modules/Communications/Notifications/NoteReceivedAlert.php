<?php

namespace Modules\Communications\Notifications;

use App\Enums\NoteUrgency;
use App\Models\InternalNote;
use App\Support\Notifications\Alert;
use App\Support\Notifications\AlertLevel;

class NoteReceivedAlert extends Alert
{
    public function __construct(private readonly InternalNote $note) {}

    public function key(): string
    {
        return "communications.note.{$this->note->id}";
    }

    protected function level(): AlertLevel
    {
        return $this->note->urgency === NoteUrgency::High ? AlertLevel::Warning : AlertLevel::Info;
    }

    protected function icon(): string
    {
        return 'icon-edit';
    }

    protected function translationKey(): string
    {
        return "communications::alerts.note.{$this->note->type->value}";
    }

    protected function params(): array
    {
        return [
            'vessel' => $this->note->vessel?->name ?? '',
            'author' => $this->note->author->name,
        ];
    }

    protected function target(): array
    {
        return ['communications.notes.index', ['box' => 'inbox']];
    }
}

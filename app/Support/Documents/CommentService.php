<?php

namespace App\Support\Documents;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CommentService
{
    public function add(Model $object, User $author, string $body, ?string $kind = null): Comment
    {
        return Comment::create([
            'object_type' => $object->getMorphClass(),
            'object_id' => $object->getKey(),
            'user_id' => $author->id,
            'kind' => $kind,
            'body' => $body,
        ]);
    }

    /** @return Collection<int, Comment> */
    public function for(Model $object): Collection
    {
        return Comment::query()
            ->with('author')
            ->where('object_type', $object->getMorphClass())
            ->where('object_id', $object->getKey())
            ->latest('id')
            ->get();
    }
}

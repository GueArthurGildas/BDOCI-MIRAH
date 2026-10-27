<?php

namespace App\Models;

use App\Enums\ConnectorCommentKind;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['data_source_id', 'user_id', 'kind', 'body'])]
class DataSourceComment extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['kind' => ConnectorCommentKind::class];
    }

    public function dataSource(): BelongsTo
    {
        return $this->belongsTo(DataSource::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

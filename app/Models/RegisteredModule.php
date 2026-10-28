<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'label', 'version', 'is_active', 'dependencies'])]
class RegisteredModule extends Model
{
    protected $table = 'module_registry';

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'dependencies' => 'array'];
    }
}

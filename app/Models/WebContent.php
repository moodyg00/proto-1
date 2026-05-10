<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WebContent extends Model
{
    use HasUuids;

    protected $table = 'web_contents';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'content_data' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}

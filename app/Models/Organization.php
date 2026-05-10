<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'address' => 'array',
            'notes' => 'array',
            'tags' => 'array',
            'is_1099_vendor' => 'boolean',
            'last_contacted_at' => 'datetime',
        ];
    }
}

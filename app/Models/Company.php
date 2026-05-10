<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'invoice_template' => 'array',
            'address' => 'array',
            'tax_settings' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
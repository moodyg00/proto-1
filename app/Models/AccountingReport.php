<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountingReport extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
            'email_recipients' => 'array',
            'last_generated_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}

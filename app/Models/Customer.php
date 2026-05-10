<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasUuids;

    protected $table = 'contacts';

    protected $guarded = [];

    protected static function booted(): void
    {
        static::addGlobalScope('customers', function (Builder $query) {
            $query->where('type', 'customer');
        });

        static::creating(function (Customer $model) {
            $model->type = 'customer';
        });
    }
}

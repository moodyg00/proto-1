<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'is_for_sale' => 'boolean',
            'is_internal_use' => 'boolean',
            'unit_price' => 'decimal:2',
        ];
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(Inventory::class);
    }
}

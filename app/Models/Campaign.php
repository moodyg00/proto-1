<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends Model
{
    use HasUuids;

    protected $table = 'ad_campaigns';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'total_budget' => 'decimal:2',
            'amount_spent' => 'decimal:2',
            'roas' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class, 'campaign_id');
    }
}

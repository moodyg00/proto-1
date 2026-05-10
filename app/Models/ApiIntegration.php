<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ApiIntegration extends Model
{
    use HasUuids;

    protected $table = 'integrations';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'configuration' => 'array',
            'last_connected_at' => 'datetime',
        ];
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class, 'integration_id');
    }
}

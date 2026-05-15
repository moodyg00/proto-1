<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PublicBookingLink extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (self $link): void {
            if (! filled($link->token)) {
                $link->token = Str::lower(Str::random(32));
            }
        });
    }

    protected function casts(): array
    {
        return [
            'available_weekdays' => 'array',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function getPublicUrlAttribute(): string
    {
        return url('/booking/' . $this->token);
    }
}
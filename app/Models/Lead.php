<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Lead extends Model
{
    use HasUuids;

    public const SOURCE_OPTIONS = [
        'website_organic' => 'Website Organic',
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'craigslist' => 'Craigslist',
        'nextdoor' => 'Nextdoor',
        'referral' => 'Referral',
        'physical_media' => 'Physical Media',
        'in_person' => 'In-Person',
    ];

    protected $guarded = [];

    protected static function booted(): void
    {
        static::creating(function (Lead $model) {
            if (empty($model->created_by)) {
                $model->created_by = Auth::id();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'notes'             => 'array',
            'next_follow_up'    => 'datetime',
            'last_contacted_at' => 'datetime',
            'closed_at'         => 'datetime',
            'converted_at'      => 'datetime',
            'expected_value'    => 'decimal:2',
        ];
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public static function sourceOptions(): array
    {
        return self::SOURCE_OPTIONS;
    }
}

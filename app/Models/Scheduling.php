<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Scheduling extends Model
{
    use HasUuids;

    public const STATUS_OPTIONS = [
        'new' => 'New',
        'scheduled' => 'Scheduled',
        'in_progress' => 'In Progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
        'rework' => 'Rework',
        'archived' => 'Archived',
    ];

    protected $table = 'work_orders';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'address' => 'array',
            'notes' => 'array',
            'scheduled_date' => 'date',
            'booking_date' => 'date',
            'booking_time' => 'datetime:H:i:s',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public static function statusOptions(): array
    {
        return self::STATUS_OPTIONS;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
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

    public const KANBAN_STATUSES = [
        'new' => [
            'label' => 'New',
            'description' => 'New work orders waiting for scheduling.',
            'accent' => 'slate',
        ],
        'scheduled' => [
            'label' => 'Scheduled',
            'description' => 'Work orders booked and ready to dispatch.',
            'accent' => 'blue',
        ],
        'in_progress' => [
            'label' => 'In Progress',
            'description' => 'Active jobs currently being worked.',
            'accent' => 'amber',
        ],
        'completed' => [
            'label' => 'Completed',
            'description' => 'Finished jobs pending final review.',
            'accent' => 'emerald',
        ],
        'rework' => [
            'label' => 'Rework',
            'description' => 'Jobs that need another pass before closeout.',
            'accent' => 'rose',
        ],
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

    public static function kanbanStatuses(): array
    {
        return self::KANBAN_STATUSES;
    }

    public static function normalizeStatus(?string $status): string
    {
        return $status === 'assigned' ? 'scheduled' : ($status ?: 'new');
    }
}

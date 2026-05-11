<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'address' => 'array',
            'notes' => 'array',
            'scheduled_date' => 'date',
            'booking_time' => 'datetime:H:i:s',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function materials()
    {
        return $this->hasMany(WorkOrderMaterial::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function photos()
    {
        return $this->hasMany(WorkOrderPhoto::class);
    }
}

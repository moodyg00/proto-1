<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class WorkOrderPhoto extends Model
{
    use HasUuids;

    protected $guarded = [];

    protected $table = 'work_order_photos';
}

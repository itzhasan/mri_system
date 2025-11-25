<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'reported_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    public function mriScan()
    {
        return $this->belongsTo(MriScan::class);
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
     protected $guarded = [];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function mriScan()
    {
        return $this->belongsTo(MriScan::class);
    }

    public function report()
    {
        return $this->belongsTo(Report::class);
    }
}

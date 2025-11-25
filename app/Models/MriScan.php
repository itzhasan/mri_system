<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MriScan extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'scan_date' => 'datetime',
        'contrast_used' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'mri_technician_id');
    }

    public function assignedDoctor()
    {
        return $this->belongsTo(User::class, 'assigned_doctor_id');
    }

    public function referringDoctor()
    {
        return $this->belongsTo(User::class, 'referring_doctor_id');
    }

    public function images()
    {
        return $this->hasMany(MriImage::class);
    }

    public function report()
    {
        return $this->hasOne(Report::class);
    }
}

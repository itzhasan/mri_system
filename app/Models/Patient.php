<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function mriScans()
    {
        return $this->hasMany(MriScan::class);
    }

    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute()
    {
        return $this->date_of_birth->age;
    }
}

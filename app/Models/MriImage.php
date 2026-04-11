<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MriImage extends Model
{
    protected $guarded = [];

    public function mriScan()
    {
        return $this->belongsTo(MriScan::class);
    }

    public function getUrlAttribute()
    {
        return '/storage/' . $this->file_path;
    }

    public function getIsDicomAttribute()
    {
        return in_array(strtolower((string) $this->file_type), ['dcm', 'dicom']);
    }
}

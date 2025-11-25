<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class MriImage extends Model
{
    protected $guarded = [];

    public function mriScan()
    {
        return $this->belongsTo(MriScan::class);
    }

    public function getUrlAttribute()
    {
        return Storage::url($this->file_path);
    }
}

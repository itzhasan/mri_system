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

    /**
     * Base64 data URI for embedding the image directly in a PDF/print view.
     * Returns null for DICOM or unreadable files (those can't be rendered inline).
     */
    public function getDataUriAttribute()
    {
        if ($this->is_dicom) {
            return null;
        }

        $fullPath = storage_path('app/public/' . $this->file_path);

        if (! is_file($fullPath)) {
            return null;
        }

        $mime = match (strtolower((string) $this->file_type)) {
            'jpg', 'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            default => 'application/octet-stream',
        };

        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($fullPath));
    }

    /**
     * Data URI suitable for embedding in a PDF/print view — including DICOM,
     * which is rendered to a PNG preview on the fly (and cached). Returns null
     * only if the file is missing or a DICOM image cannot be decoded.
     */
    public function getPreviewDataUriAttribute(): ?string
    {
        if (! $this->is_dicom) {
            return $this->data_uri;
        }

        return app(\App\Support\DicomRenderer::class)->dataUri($this->file_path);
    }
}

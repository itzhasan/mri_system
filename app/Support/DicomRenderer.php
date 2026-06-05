<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Renders an uncompressed DICOM image to a PNG using only PHP + GD —
 * a server-side port of the browser viewer (window/level grayscale or RGB).
 *
 * Supports Explicit/Implicit VR Little Endian, uncompressed pixel data
 * (transfer syntaxes 1.2.840.10008.1.2 and 1.2.840.10008.1.2.1).
 * Compressed transfer syntaxes (JPEG / JPEG2000 / RLE) are not supported
 * and will return null so callers can fall back gracefully.
 */
class DicomRenderer
{
    /** Largest edge (px) of the produced PNG; bigger images are scaled down. */
    protected int $maxEdge = 700;

    // Parser state.
    protected string $buf = '';
    protected int $blen = 0;
    protected int $p = 0;
    protected bool $explicit = true;
    protected string $ts = '1.2.840.10008.1.2.1';
    protected array $elements = [];

    /**
     * Return a base64 PNG data URI for a DICOM file stored on the public disk,
     * or null if it cannot be rendered. Results are cached on disk.
     *
     * @param  string  $publicPath  path relative to the public disk (e.g. "mri-images/x.dcm")
     */
    public function dataUri(string $publicPath): ?string
    {
        $png = $this->png($publicPath);

        return $png ? 'data:image/png;base64,' . base64_encode($png) : null;
    }

    /** Return raw PNG bytes (cached) for a DICOM file, or null on failure. */
    public function png(string $publicPath): ?string
    {
        $disk = Storage::disk('public');
        if (! $disk->exists($publicPath)) {
            return null;
        }

        $cacheKey = 'mri-images/dicom-cache/' . md5($publicPath . '|' . $disk->size($publicPath)) . '.png';
        if ($disk->exists($cacheKey)) {
            return $disk->get($cacheKey);
        }

        try {
            $png = $this->render($disk->get($publicPath));
        } catch (\Throwable $e) {
            $png = null;
        }

        if ($png !== null) {
            $disk->put($cacheKey, $png);
        }

        return $png;
    }

    /** Parse DICOM bytes and produce PNG bytes (or null if unsupported). */
    protected function render(string $data): ?string
    {
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $len = strlen($data);
        if ($len < 132 || substr($data, 128, 4) !== 'DICM') {
            return null;
        }

        $this->buf = $data;
        $this->blen = $len;
        $this->p = 132;
        $this->explicit = true;   // file meta group (0002) is always explicit VR LE
        $this->ts = '1.2.840.10008.1.2.1';
        $this->elements = [];

        $this->parseDataset($len);

        $elements = $this->elements;

        // Compressed pixel data is not supported.
        if (! in_array($this->ts, ['1.2.840.10008.1.2', '1.2.840.10008.1.2.1'], true)) {
            return null;
        }

        if (! isset($elements['7FE00010'], $elements['00280010'], $elements['00280011'])) {
            return null;
        }

        $rows = $this->us($elements['00280010']);
        $cols = $this->us($elements['00280011']);
        if ($rows < 1 || $cols < 1) {
            return null;
        }

        $bitsAllocated = isset($elements['00280100']) ? $this->us($elements['00280100']) : 8;
        $pixelRep = isset($elements['00280103']) ? $this->us($elements['00280103']) : 0;
        $samples = isset($elements['00280002']) ? $this->us($elements['00280002']) : 1;
        $photometric = isset($elements['00280004']) ? trim($elements['00280004'], " \0") : 'MONOCHROME2';
        $slope = isset($elements['00281053']) ? (float) $elements['00281053'] : 1.0;
        $intercept = isset($elements['00281052']) ? (float) $elements['00281052'] : 0.0;
        if ($slope == 0.0) {
            $slope = 1.0;
        }

        $pixelData = $elements['7FE00010'];
        $img = imagecreatetruecolor($cols, $rows);

        if ($samples === 3) {
            $this->renderRgb($img, $pixelData, $rows, $cols);
        } else {
            $this->renderGray($img, $pixelData, $rows, $cols, $bitsAllocated, $pixelRep, $slope, $intercept, $photometric, $elements);
        }

        $img = $this->maybeScale($img, $cols, $rows);

        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        return $png ?: null;
    }

    /**
     * Parse dataset elements from the current position up to $end, capturing
     * top-level elements into $this->elements. Stops after pixel data.
     */
    protected function parseDataset(int $end): void
    {
        while ($this->p + 8 <= $end && $this->p + 8 <= $this->blen) {
            $group = $this->u16($this->p);
            $element = $this->u16($this->p + 2);

            // Item delimiters end the current item/sequence — let the caller handle them.
            if ($group === 0xFFFE) {
                return;
            }

            // Once past the file-meta group, honour an implicit-VR transfer syntax.
            if ($group !== 0x0002 && $this->explicit && $this->ts === '1.2.840.10008.1.2') {
                $this->explicit = false;
            }

            $this->p += 4;
            [$vr, $length] = $this->readVrLength();
            $tag = sprintf('%04X%04X', $group, $element);

            // Undefined length → a sequence; skip its items.
            if ($length === 0xFFFFFFFF) {
                $this->skipSequence();
                continue;
            }

            // Defined-length sequences: skip the whole block.
            if ($vr === 'SQ') {
                $this->p += $length;
                continue;
            }

            $value = substr($this->buf, $this->p, $length);
            $this->p += $length;
            $this->elements[$tag] = $value;

            if ($tag === '00020010') {
                $this->ts = trim($value, " \0");
            }
            if ($tag === '7FE00010') {
                return;
            }
        }
    }

    /** Read VR (explicit only) + length at the current position; advances $this->p. */
    protected function readVrLength(): array
    {
        if ($this->explicit) {
            $vr = substr($this->buf, $this->p, 2);
            $this->p += 2;
            if (in_array($vr, ['OB', 'OW', 'OF', 'SQ', 'UT', 'UN'], true)) {
                $this->p += 2; // reserved
                $length = $this->u32($this->p);
                $this->p += 4;
            } else {
                $length = $this->u16($this->p);
                $this->p += 2;
            }

            return [$vr, $length];
        }

        $length = $this->u32($this->p);
        $this->p += 4;

        return [null, $length];
    }

    /** Skip an undefined-length sequence: items until a Sequence Delimitation Item. */
    protected function skipSequence(): void
    {
        while ($this->p + 8 <= $this->blen) {
            $group = $this->u16($this->p);
            $element = $this->u16($this->p + 2);
            $itemLen = $this->u32($this->p + 4);
            $this->p += 8;

            if ($group === 0xFFFE && $element === 0xE0DD) {
                return; // sequence delimitation
            }
            if ($group === 0xFFFE && $element === 0xE000) {
                if ($itemLen === 0xFFFFFFFF) {
                    $this->skipItem();
                } else {
                    $this->p += $itemLen;
                }
                continue;
            }

            return; // unexpected token — bail out safely
        }
    }

    /** Skip an undefined-length item: dataset elements until an Item Delimitation Item. */
    protected function skipItem(): void
    {
        while ($this->p + 8 <= $this->blen) {
            $group = $this->u16($this->p);
            $element = $this->u16($this->p + 2);

            if ($group === 0xFFFE && $element === 0xE00D) {
                $this->p += 8; // item delimitation (length 0)
                return;
            }

            $this->p += 4;
            [$vr, $length] = $this->readVrLength();
            if ($length === 0xFFFFFFFF) {
                $this->skipSequence();
            } else {
                $this->p += $length;
            }
        }
    }

    protected function u16(int $at): int
    {
        return unpack('v', substr($this->buf, $at, 2))[1];
    }

    protected function u32(int $at): int
    {
        return unpack('V', substr($this->buf, $at, 4))[1];
    }

    protected function renderGray($img, string $pixelData, int $rows, int $cols, int $bits, int $rep, float $slope, float $intercept, string $photometric, array $elements): void
    {
        $count = $rows * $cols;

        if ($bits === 16) {
            $format = $rep === 1 ? 's' : 'v'; // signed/unsigned 16-bit LE
            $values = array_values(unpack($format . $count, substr($pixelData, 0, $count * 2)));
        } else {
            $values = array_values(unpack('C' . $count, substr($pixelData, 0, $count)));
        }

        // Window center/width: use DICOM values if present, else auto from min/max.
        $wc = $ww = null;
        if (isset($elements['00281050'], $elements['00281051'])) {
            $wc = (float) explode('\\', trim($elements['00281050']))[0];
            $ww = (float) explode('\\', trim($elements['00281051']))[0];
        }
        if ($wc === null || $ww === null || $ww == 0.0) {
            $min = INF;
            $max = -INF;
            foreach ($values as $v) {
                $r = $v * $slope + $intercept;
                if ($r < $min) $min = $r;
                if ($r > $max) $max = $r;
            }
            $wc = ($min + $max) / 2;
            $ww = ($max - $min) ?: 1;
        }

        $low = $wc - $ww / 2;
        $invert = $photometric === 'MONOCHROME1';

        // Precompute gray-color allocations to avoid re-allocating per pixel.
        $colors = [];
        for ($i = 0; $i < $count; $i++) {
            $v = $values[$i] * $slope + $intercept;
            $g = (int) round((($v - $low) / $ww) * 255);
            if ($g < 0) $g = 0;
            elseif ($g > 255) $g = 255;
            if ($invert) $g = 255 - $g;

            if (! isset($colors[$g])) {
                $colors[$g] = imagecolorallocate($img, $g, $g, $g);
            }
            imagesetpixel($img, $i % $cols, intdiv($i, $cols), $colors[$g]);
        }
    }

    protected function renderRgb($img, string $pixelData, int $rows, int $cols): void
    {
        $count = $rows * $cols;
        $bytes = array_values(unpack('C' . ($count * 3), substr($pixelData, 0, $count * 3)));
        for ($i = 0; $i < $count; $i++) {
            $r = $bytes[$i * 3] ?? 0;
            $g = $bytes[$i * 3 + 1] ?? 0;
            $b = $bytes[$i * 3 + 2] ?? 0;
            imagesetpixel($img, $i % $cols, intdiv($i, $cols), imagecolorallocate($img, $r, $g, $b));
        }
    }

    protected function maybeScale($img, int $cols, int $rows)
    {
        $maxDim = max($cols, $rows);
        if ($maxDim <= $this->maxEdge) {
            return $img;
        }
        $scaled = imagescale($img, (int) round($cols * $this->maxEdge / $maxDim));
        if ($scaled !== false) {
            imagedestroy($img);
            return $scaled;
        }
        return $img;
    }

    /** Read an unsigned short (little-endian) from a raw value. */
    protected function us(string $v): int
    {
        if (strlen($v) < 2) {
            return 0;
        }
        return unpack('v', substr($v, 0, 2))[1];
    }
}

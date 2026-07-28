<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\File;

class Document extends Model
{
    use HasFactory;

    protected $casts = [
        'ocr_settings' => 'array',
    ];

    public const TAX_MODE_EXCLUDED = 'excluded';

    public const TAX_MODE_INCLUDED = 'included';

    public function ocrSumAmountsEnabled(): bool
    {
        return (bool) data_get($this->ocr_settings, 'sum_amounts', false);
    }

    public function ocrTaxMode(): string
    {
        $mode = (string) data_get($this->ocr_settings, 'tax_mode', self::TAX_MODE_EXCLUDED);

        return $mode === self::TAX_MODE_INCLUDED
            ? self::TAX_MODE_INCLUDED
            : self::TAX_MODE_EXCLUDED;
    }

    public function ocrTaxIncluded(): bool
    {
        return $this->ocrTaxMode() === self::TAX_MODE_INCLUDED;
    }

    public function files() {
        return $this->hasMany(File::class);
    }
}
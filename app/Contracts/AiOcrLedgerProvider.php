<?php

namespace App\Contracts;

use App\Data\LedgerOcrResult;
use Illuminate\Http\UploadedFile;

interface AiOcrLedgerProvider
{
    /**
     * @param  array{sum_amounts?: bool}  $options
     */
    public function ledgerOcr(UploadedFile $file, string $prompt, array $options = []): LedgerOcrResult;
}


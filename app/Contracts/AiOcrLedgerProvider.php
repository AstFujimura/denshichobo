<?php

namespace App\Contracts;

use App\Data\LedgerOcrResult;
use Illuminate\Http\UploadedFile;

interface AiOcrLedgerProvider
{
    public function ledgerOcr(UploadedFile $file, string $prompt): LedgerOcrResult;
}


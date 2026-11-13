<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // These values are now global .env settings. Remove any tenant-level
        // copies so credentials and endpoints cannot be applied accidentally.
        DB::connection('control')
            ->table('tenant_ai_settings')
            ->update([
                'gemini_api_key_encrypted' => null,
                'gemini_api_base' => null,
                'internal_ocr_base_url' => null,
                'internal_ocr_ledger_path' => null,
                'internal_ocr_token_encrypted' => null,
                'internal_ocr_timeout' => null,
                'card_ocr_provider' => null,
                'trace_enabled' => false,
            ]);
    }

    public function down(): void
    {
        // Removed credentials and endpoints cannot be reconstructed.
    }
};

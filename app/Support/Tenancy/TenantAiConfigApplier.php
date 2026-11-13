<?php

namespace App\Support\Tenancy;

use App\Models\Control\Tenant;
use Illuminate\Support\Facades\Config;

class TenantAiConfigApplier
{
    /**
     * Apply only the AI choices that are allowed to vary by tenant.
     * Credentials, URLs, paths, tokens, timeouts and trace settings remain
     * global and are loaded from .env/config.
     */
    public function apply(?Tenant $tenant): void
    {
        if (! $tenant) {
            return;
        }

        $ai = $tenant->relationLoaded('aiSettings')
            ? $tenant->aiSettings
            : $tenant->aiSettings()->first();

        if (! $ai) {
            return;
        }

        if (filled($ai->ledger_ocr_provider)) {
            Config::set('ai_ocr.provider', $ai->ledger_ocr_provider);
        }

        if (filled($ai->gemini_model)) {
            Config::set('version.gemini_model', $ai->gemini_model);
        }
    }
}

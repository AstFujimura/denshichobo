<?php

namespace App\Data;

class LedgerOcrResult
{
    /**
     * @param  list<array{amount: int, label: string}>  $kinngakuBreakdown
     */
    public function __construct(
        public readonly ?string $hiduke,
        public readonly ?string $kinngaku,
        public readonly ?string $torihikisaki,
        public readonly array $raw = [],
        public readonly ?string $provider = null,
        /** 切り分け用: 最後に到達した処理段階 */
        public readonly ?string $step = null,
        /** 切り分け用: 人が読めるエラー概要 */
        public readonly ?string $error = null,
        public readonly array $kinngakuBreakdown = [],
    ) {}

    public function hasAnyField(): bool
    {
        return (bool) ($this->hiduke || $this->kinngaku || $this->torihikisaki);
    }

    /** 取引日・金額・取引先のいずれかが欠落している */
    public function hasMissingCoreFields(): bool
    {
        return !$this->hiduke || !$this->kinngaku || !$this->torihikisaki;
    }

    public static function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    /**
     * PDF 結果を優先し、欠落項目だけ JPEG 再試行結果で埋める。
     */
    public static function mergePreferPrimary(self $primary, self $fallback): self
    {
        return new self(
            hiduke: $primary->hiduke ?: $fallback->hiduke,
            kinngaku: $primary->kinngaku ?: $fallback->kinngaku,
            torihikisaki: $primary->torihikisaki ?: $fallback->torihikisaki,
            raw: [
                'hybrid' => true,
                'primary' => $primary->raw,
                'jpeg_retry' => $fallback->raw,
            ],
            provider: $primary->provider ?? $fallback->provider,
            step: 'hybrid.pdf_then_jpeg',
            error: null,
            kinngakuBreakdown: $primary->kinngakuBreakdown !== []
                ? $primary->kinngakuBreakdown
                : $fallback->kinngakuBreakdown,
        );
    }

    public function toArray(): array
    {
        return [
            'hiduke' => $this->hiduke,
            'kinngaku' => $this->kinngaku,
            'torihikisaki' => $this->torihikisaki,
            'kinngaku_breakdown' => $this->kinngakuBreakdown,
        ];
    }
}


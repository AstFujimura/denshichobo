<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    /** タグ色プリセット（キーはフォーム値、値はカラーコード） */
    public const PRESET_COLOR_HEX = [
        'red' => '#e53935',
        'blue' => '#1e88e5',
        'yellow' => '#fbc02d',
        'green' => '#43a047',
        'purple' => '#8e24aa',
    ];

    /**
     * 保存済みカラーコードがどのプリセットか判定（一致しなければ custom）
     */
    public static function presetKeyForHex(string $hex): string
    {
        $norm = self::normalizeHexForCompare($hex);
        if ($norm === '') {
            return 'custom';
        }
        foreach (self::PRESET_COLOR_HEX as $key => $presetHex) {
            if (self::normalizeHexForCompare($presetHex) === $norm) {
                return $key;
            }
        }

        return 'custom';
    }

    private static function normalizeHexForCompare(string $hex): string
    {
        $hex = trim($hex);
        if ($hex === '') {
            return '';
        }
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6 || !ctype_xdigit($hex)) {
            return '';
        }

        return '#' . strtoupper($hex);
    }

    protected $table = 'tags';

    protected $fillable = [
        'ユーザーID',
        'タグ名',
        'カラーコード',
        '非公開',
    ];

    protected $casts = [
        '非公開' => 'boolean',
    ];

    public function cards()
    {
        return $this->belongsToMany(Card::class, 'card_tag', 'タグID', '名刺ID')->withTimestamps();
    }
}

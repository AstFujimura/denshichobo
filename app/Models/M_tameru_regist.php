<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class M_tameru_regist extends Model
{
    use HasFactory;
    protected $fillable = [
        'カテゴリマスタID',
        'ファイル',
        '取引日',
        '金額',
        '取引先',
        '書類区分',
        '提出',
        '保存方法',
        '検索ワード',
    ];
    protected $table = 'm_tameru_regist';
}

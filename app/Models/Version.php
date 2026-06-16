<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\File;
use App\Models\User;


class Version extends Model
{
    use HasFactory;

    protected $table = 'versions';

    protected $fillable = [
        'フロー',
        '名刺',
        'tameru',
        'スケジュール',
        '文書',
        'BANBAN',
        'ichifuji',
    ];

    protected $casts = [
        'フロー' => 'boolean',
        '名刺' => 'boolean',
        'tameru' => 'boolean',
        'スケジュール' => 'boolean',
        '文書' => 'boolean',
        'BANBAN' => 'boolean',
        'ichifuji' => 'boolean',
    ];
}

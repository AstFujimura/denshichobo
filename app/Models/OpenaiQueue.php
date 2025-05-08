<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpenaiQueue extends Model
{
    use HasFactory;

    protected $table = 'openai_queues';
    protected $fillable = [
        'トークン',
        '開始時刻',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\File;
use App\Models\User;


class Card extends Model
{
    use HasFactory;

    protected $table = 'cards';
    protected $fillable = [
        '最新フラグ',
    ];

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'card_tag', '名刺ID', 'タグID')->withTimestamps();
    }
}

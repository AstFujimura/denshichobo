<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UploadedCard extends Model
{
    use HasFactory;

    protected $table = 'uploaded_cards';
    protected $fillable = [
        'front_url',
        'back_url',
        'status',
        'openai_response',
        'upload_id',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Drive extends Model
{
    use HasFactory;
    protected $casts = [
        '出発時刻' => 'datetime',
        '到着時刻' => 'datetime'
    ];
    public function users() {
        return $this->belongsTo(User::class,'運転者コード','id');
    }
}

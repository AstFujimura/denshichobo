<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Document;
use App\Models\Client;

class File extends Model
{
    use HasFactory;
    public function users() {
        return $this->belongsTo(User::class,'保存者ID','id');
    }

    public function documents() {
        return $this->belongsTo(Document::class,'書類ID','id');
    }

    public function clients() {
        return $this->belongsTo(Client::class,'取引先ID','取引先');
    }
}
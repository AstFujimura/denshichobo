<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\File;

class Document extends Model
{
    use HasFactory;
    public function files() {
        return $this->hasMany(File::class);
    }
}
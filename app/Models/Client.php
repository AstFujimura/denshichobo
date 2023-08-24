<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\File;

class Client extends Model
{
    use HasFactory;
    public function files() {
        return $this->hasMany(File::class);
    }
}

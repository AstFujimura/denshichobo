<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\File;
use App\Models\User;


class Group extends Model
{
    use HasFactory;

    public function files() {
        return $this->hasMany(File::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\File;
use App\Models\User;


class Event_User extends Model
{
    use HasFactory;

    protected $table = 'event_user';
}

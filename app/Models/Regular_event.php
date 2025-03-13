<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\File;
use App\Models\User;


class Regular_event extends Model
{
    use HasFactory;

    protected $table = 'regular_events';
}

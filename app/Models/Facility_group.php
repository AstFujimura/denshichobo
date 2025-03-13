<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\File;
use App\Models\User;


class Facility_group extends Model
{
    use HasFactory;

    protected $table = 'facility_groups';
}

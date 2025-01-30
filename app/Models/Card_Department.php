<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\File;
use App\Models\User;


class Card_Department extends Model
{
    use HasFactory;

    protected $table = 'card_department';
}

<?php

namespace App\Models\Control;

use Illuminate\Database\Eloquent\Model;

abstract class ControlModel extends Model
{
    protected $connection = 'control';
}

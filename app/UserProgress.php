<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserProgress extends Model
{
    protected $fillable = ['questId', 'userId', 'status'];
}

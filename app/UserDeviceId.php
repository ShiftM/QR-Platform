<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserDeviceId extends Model
{
    protected $fillable = ['userId', 'deviceToken', 'deviceType'];

    protected $hidden = ['id', 'updated_at'];
}

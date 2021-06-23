<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtpRequest extends Model
{
    protected $fillable = ['userId', 'otpCode', 'otpCodeExpiration', 'otpType'];

    protected $hidden = ['id', 'userId', 'otpType', 'updated_at'];
}

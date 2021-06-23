<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegistrationOtp extends Model
{
    protected $fillable = ['countryCode', 'phoneNumber', 'otpCode', 'otpCodeExpiration'];

    protected $hidden = ['id', 'updated_at'];
}

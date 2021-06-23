<?php

namespace App;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'birthday', 'countryCode', 'phoneNumber', 'deviceType', 'points', 'gender', 'email',"code"
    ];

    protected $dates = ['deleted_at'];

    protected $hidden = ['remember_token', 'updated_at'];


	public static function boot() {
		parent::boot();
		// registering a callback to be executed upon the creation of an activity AR
		static::creating(function ($self) {
			$code = preg_replace('/\s+/', '', str_shuffle($self->username.'abcdefghijklmnopqrstuvwxyz'. now()->timestamp));;
			$self->code = $code;
		});
	}

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function deviceIds() {
        return $this->hasMany('App\UserDeviceId', 'userId', 'id');
    }

    public function company() {
        return $this->hasOne(Company::class);
    }

	public function hasOneWalletAccount() {
		return $this->morphOne('App\WalletAccount', 'table');
	}

}

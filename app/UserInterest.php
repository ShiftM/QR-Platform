<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserInterest extends Model
{
    protected $fillable = ['user_id', 'interest_option_id'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function interestOption()
    {
        return $this->belongsTo('App\InterestOption');
    }

    public function scopeWithRelatedModels($query)
    {
        return $query->with(['user', 'interestOption']);
    }
}

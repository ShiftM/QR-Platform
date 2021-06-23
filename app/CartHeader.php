<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CartHeader extends Model
{
    protected $fillable = ['user_id', 'limit'];

    public function hasManyCartDetail()
    {
        return $this->hasMany('App\CartDetail');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}

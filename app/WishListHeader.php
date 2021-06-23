<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WishListHeader extends Model
{
    protected $fillable = ['user_id'];

    public function hasManyWishListDetail()
    {
        return $this->hasMany('App\WishListDetail');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function scopeWithRelatedModels($query) {
        return $query->with(['hasManyWishListDetail'=> function ($query){
            $query->with(['itemStock' => function ($query) {
                $query->withRelatedModels();
            }]);
        }]);
    }
}

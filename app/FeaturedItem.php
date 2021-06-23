<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FeaturedItem extends Model
{
    protected $fillable = ['item_id'];

    public function item()
    {
        return $this->belongsTo('App\Item');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WishListDetail extends Model
{
    protected $fillable = ['wish_list_header_id', 'item_stock_id'];

    public function itemStock()
    {
        return $this->belongsTo('App\ItemStock');
    }

    public function wishListHeader() {
        return $this->belongsTo('App\WishListHeader');
    }
}

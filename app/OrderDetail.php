<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $fillable = ['order_header_id', 'currency_type_id', 'color_option_id', 'item_stock_id', 'size_option_id', 'price', 'quantity', 'sub_total'];

    public function orderHeader()
    {
        $this->belongsTo('App\OrderHeader');
    }

    public function itemStock()
    {
        return $this->belongsTo('App\ItemStock');
    }

    public function currencyType()
    {
        return $this->belongsTo('App\CurrencyType');
    }

    public function colorOption()
    {
        return $this->belongsTo('App\ColorOption');
    }

    public function sizeOption()
    {
        return $this->belongsTo('App\SizeOption');
    }

    public function scopeWithRelatedModels($query)
    {
        return $query->with(['itemStock' => function($query){
            $query->withRelatedModels();
        }, 'currencyType', 'colorOption', 'sizeOption']);
    }
}

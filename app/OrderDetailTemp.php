<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderDetailTemp extends Model
{
    protected $fillable = ['order_header_temp_id', 'currency_type_id', 'color_option_id', 'item_stock_id', 'size_option_id', 'price', 'quantity', 'sub_total'];


	public function scopeWithRelatedModels($query) {
		return $query->with([
			'itemStock' => function ($query) {
				$query->with(['item','itemVariant' => function ($query) {
					$query->with(['hasManyImage']);
				}]);
			},
		]);
	}

    public function orderHeaderTemp()
    {
        $this->belongsTo('App\OrderHeaderTemp');
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


}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CartDetail extends Model
{
    protected $fillable = ['cart_header_id', 'item_stock_id', 'quantity'];

    public function cartHeader()
    {
        $this->belongsTo('App\CartHeader');
    }

    public function itemStock()
    {
        return $this->belongsTo('App\ItemStock');
    }


	public function scopeWithRelatedModels($query) {
		return $query->with([
			'itemStock' => function ($query) {
				$query->with(['item','sizeOption','itemVariant' => function ($query) {
					$query->with(['hasManyImage','colorOption']);
				}]);
			},
		]);
	}
}

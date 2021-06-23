<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderTotal extends Model {
	protected $fillable = ['currency_type_id', 'shipping_fee', 'sub_total', 'discount', 'grand_total'];

	public function table() {
		return $this->morphTo();
	}

	public function currencyType() {
		return $this->belongsTo('App\CurrencyType');
	}
}

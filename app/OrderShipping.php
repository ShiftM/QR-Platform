<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderShipping extends Model {
	protected $fillable = ['order_header_id', "address_name","region", "house_number", "street", "barangay", "zip_code", "city", "province", "country"];

	public function orderHeader() {
		$this->belongsTo('App\OrderHeader');
	}
}

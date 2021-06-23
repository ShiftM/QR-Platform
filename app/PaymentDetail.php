<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentDetail extends Model
{
    //

	protected $fillable = ['transaction_id','payment_method_id','meta', 'table_type','table_id',"status_option_id"];

	protected $casts = [
		'meta' => 'array'
	];

	public function table() {
		return $this->morphTo();
	}

}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrderHeaderTemp extends Model {


	protected $fillable = ['user_id', 'order_number'];


	public static function boot() {
		parent::boot();
		// registering a callback to be executed upon the creation of an activity AR
		static::creating(function ($self) {
			$self->order_number = 'QR-ORDER#' . $self->user_id . now()->timestamp;
		});
	}

	public function hasManyOrderDetailTemp() {
		return $this->hasMany('App\OrderDetailTemp');
	}

	public function hasManyOrderTotal() {

		return $this->morphMany('App\OrderTotal', 'table');
	}

	public function user() {
		return $this->belongsTo('App\User');
	}

	public function scopeWithRelatedModels($query) {
		return $query->with([
			'hasManyOrderTotal',
			'hasManyOrderDetailTemp' => function ($query) {
				$query->withRelatedModels()->orderBy('id','DESC');
			},
		]);
	}


}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GemOrderHeaderTemp extends Model
{
    protected $fillable = ['order_number', 'user_id', 'status_option_id'];


	public static function boot() {
		parent::boot();
		// registering a callback to be executed upon the creation of an activity AR
		static::creating(function ($self) {
			$self->order_number = 'GEM-ORDER#' . $self->user_id . now()->timestamp;
			$self->status_option_id = 1;
		});
	}

    public function statusOption()
    {
        return $this->belongsTo('App\StatusOption');
    }



	public function hasOneGemOrderDetailTemp()
	{
		return $this->hasOne('App\GemOrderDetailTemp');
	}

	public function hasOneOrderTotal() {

		return $this->morphOne('App\OrderTotal', 'table');
	}

}

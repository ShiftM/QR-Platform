<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderHeader extends Model
{
    protected $fillable = ['status_option_id', 'user_id', 'order_number','additional_information'];


	public static function boot() {
		parent::boot();
		// registering a callback to be executed upon the creation of an activity AR
		static::creating(function ($self) {
			$self->order_number = 'QR-ORDER#' . $self->user_id . now()->timestamp;
		});
	}

    public function table() {
        return $this->morphTo();
    }

    public function hasManyOrderDetail()
    {
        return $this->hasMany('App\OrderDetail');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function hasOneOrderRecipient()
    {
        return $this->hasOne('App\OrderRecipient');
    }

    public function hasOneOrderShipping()
    {
        return $this->hasOne('App\OrderShipping');
    }

    public function statusOption()
    {
        return $this->belongsTo('App\StatusOption');
    }

    public function hasOneOrderTotal()
    {
        return $this->morphOne('App\OrderTotal', 'table');
    }
	public function hasManyOrderTotal() {

		return $this->morphMany('App\OrderTotal', 'table');
	}

	public function hasManyOrderStatusHistory() {
	    return $this->hasMany('App\OrderStatusHistory');
	}
	public function scopeWithRelatedModels($query)
    {
        return $query->with(['hasManyOrderDetail' => function($query){
            $query->withRelatedModels();
        }, 'hasOneOrderRecipient', 'hasOneOrderShipping', 'statusOption', 'hasManyOrderTotal','hasOneOrderTotal', 'user','hasManyOrderStatusHistory' => function($query){
        	$query->with(['statusOption']);
        }]);
    }


}

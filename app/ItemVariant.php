<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemVariant extends Model
{
    //

	protected $fillable = ['item_id','color_option_id','primary','status_option_id'];

	public function hasManyItemStock() {
	    return $this->hasMany('App\ItemStock');
	}

	public function hasManyImage() {
		return $this->morphMany('App\Image', 'table');
	}

	public function hasOneImage() {
		return $this->morphOne('App\Image', 'table');
	}


	public function colorOption() {
	    return $this->belongsTo('App\ColorOption');
	}
}

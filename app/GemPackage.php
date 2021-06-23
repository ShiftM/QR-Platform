<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GemPackage extends Model
{
    protected $fillable = ['status_option_id', 'name', 'amount', 'description', 'price', 'code'];

	public static function boot()
	{
		parent::boot();

		static::created(function($product) {
			$product->code .= 'GEM' . $product->id. now()->timestamp;
			$product->save();
		});
	}

    public function statusOption()
    {
        return $this->belongsTo('App\StatusOption');
    }

    public function delete() {

        $this->update(["status_option_id" => 2]);
    }
}

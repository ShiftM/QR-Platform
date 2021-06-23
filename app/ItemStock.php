<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Facades\JWTAuth;

class ItemStock extends Model {
	protected $fillable = ['item_id', 'currency_type_id', 'size_option_id', 'status_option_id', 'quantity', 'price', 'sku', 'code', 'item_variant_id'];
	protected $appends = ['remaining_quantity', 'is_in_wish_list'];

	public function item() {
		return $this->belongsTo('App\Item');
	}

	public function currencyType() {

		return $this->belongsTo('App\CurrencyType');
	}

	public function sizeOption() {
		return $this->belongsTo('App\SizeOption');
	}

	public function statusOption() {
		return $this->belongsTo('App\StatusOption');
	}

	public function itemVariant() {
		return $this->belongsTo('App\ItemVariant');
	}

	public function scopeWithRelatedModels($query) {
		return $query->with(['item' => function ($query){
			$query->with(['hasManyItemCategory' => function ($query) {
				$query->with(['categoryHeader']);
			}]);
		}, 'currencyType', 'sizeOption', 'statusOption', 'itemVariant' => function ($query) {
			$query->with(['hasManyImage', 'colorOption']);
		}]);
	}

	public function hasManyOrderDetail() {
		return $this->hasMany('App\OrderDetail');
	}

	public function hasManyCartDetail() {
		return $this->hasMany('App\CartDetail');
	}

	public function getRemainingQuantityAttribute() {

//		dd($this->hasManyCartDetail->count());
		$remainingQuantity = $this->quantity - $this->hasManyCartDetail->sum('quantity');

		return $remainingQuantity < 0 ? 0 : $remainingQuantity;
	}

	public function getIsInWishListAttribute() {
		$status = false;
		$user = auth('user')->user();
		if ($user) {
			$wishlist = WishListDetail::with('wishListHeader')->whereItemStockId($this->id)
				->whereHas('wishListHeader', function ($query) use ($user) {
					$query->whereUserId($user->id);
				})
				->first();
			if($wishlist){
				$status = true;
			}
		}

		return $status;
	}

}

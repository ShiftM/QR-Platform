<?php

namespace App;

use App\Helpers\Status;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Item extends Model {
	protected $fillable = ['status_option_id', 'name', 'slug', 'description'];
	protected $appends = ['is_featured'];

	public function hasManyItemStock() {
		return $this->hasMany('App\ItemStock');
	}

	public function hasManyItemCategory() {
		return $this->hasMany('App\ItemCategory');
	}


	public function hasManyItemVariant() {
		return $this->hasMany('App\ItemVariant');
	}


	public function hasOneStatusOption() {
		return $this->hasOne('App\StatusOption');
	}

	public function hasOneFeaturedItem() {
		return $this->hasOne('App\FeaturedItem');
	}

	public function scopeWithRelatedModels($query, $data) {
		return $query->with([
			'hasOneFeaturedItem', 'hasManyItemVariant'  => function ($query) use ($data) {
				$query->with(['hasManyImage', 'colorOption', 'hasManyItemStock' => function ($query) use ($data) {
					$query->with(['sizeOption'])->withCount([
						'hasManyCartDetail as in_cart_quantity' => function ($query) {

							$query->select(DB::raw("SUM(quantity)"));
						},
					]);
//					if (isset($data['related_status_option_id']) && $data['related_status_option_id']) {
						$query->whereStatusOptionId(Status::ACTIVE)
							->whereHas('sizeOption', function ($query) use ($data) {
								$query->whereStatusOptionId(Status::ACTIVE);
							});
//					}

					$query->orderBy('size_option_id', 'ASC');
				}]);
//				if (isset($data['related_status_option_id']) && $data['related_status_option_id']) {
					$query->whereStatusOptionId(Status::ACTIVE)
						->whereHas('colorOption', function ($query) use ($data) {
							$query->whereStatusOptionId(Status::ACTIVE);
						});
//				}

				$query->whereStatusOptionId(Status::ACTIVE)->orderBy('primary', 'DESC');
			},
			                      'hasManyItemCategory' => function ($query) {
				                      $query->with(['categoryHeader' => function ($q) {
					                      $q->whereStatusOptionId(1);
				                      }]);
			                      },
		]);
	}


	public function itemImage() {
		return $this->morphMany('App\Image', 'table');
	}

	public function getIsFeaturedAttribute() {


		return $this->hasOneFeaturedItem ? true : false;
	}


	public static function boot() {
		parent::boot();
		// registering a callback to be executed upon the creation of an activity AR
		static::creating(function ($self) {
			$self->slug = Str::slug($self->name . now()->timestamp);
		});
	}

	public function delete() {
		$this->update(["status_option_id" => 2]);
	}

	public function forceDelete() {

		parent::delete();

		$this->hasManyItemStock()->delete();
		$this->hasManyItemCategory()->delete();

	}
}

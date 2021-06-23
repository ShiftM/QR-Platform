<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model {
	protected $fillable = ['user_id', 'address_name', 'recipient_name', 'house_number',
		'street', 'barangay', 'city', 'province', 'region', 'country', 'zip_code', 'phone_number',
		'primary', 'status_option_id'];

	protected $appends = ['complete_address'];

	public function user() {
		return $this->belongsTo('App\User');
	}

	public function delete() {
		$this->update(["status_option_id" => 2]);
	}

	public function getCompleteAddressAttribute() {

		$address = $this->house_number . ' ' .
			$this->street . ', ' .
			$this->barangay . ', ' .
			$this->city . ' ' .
			$this->zip_code . ', '.
			$this->country;

		return $address;
	}

}

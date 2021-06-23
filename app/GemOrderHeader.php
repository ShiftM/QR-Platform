<?php

namespace App;

use App\Helpers\Status;
use App\Repositories\Wallet\WalletRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class GemOrderHeader extends Model {
	protected $fillable = ['order_number', 'user_id', 'status_option_id', 'payment_method_id'];


	public static function boot() {
		parent::boot();
		// registering a callback to be executed upon the creation of an activity AR
		static::creating(function ($self) {
			$self->order_number = 'GEM-ORDER#' . $self->user_id . now()->timestamp;
		});
	}

	public function statusOption() {
		return $this->belongsTo('App\StatusOption');
	}

	public function user() {
		return $this->belongsTo('App\User');
	}

	public function paymentMethod() {
		return $this->belongsTo('App\PaymentMethod');
	}

	public function hasOneOrderRecipient() {
		return $this->morphOne('App\OrderRecipient', 'table');
	}

	public function hasOneOrderTotal() {

		return $this->morphOne('App\OrderTotal', 'table');
	}

	public function hasOnePaymentDetail() {

		return $this->morphOne('App\PaymentDetail', 'table');
	}

	public function hasOneGemOrderDetail() {

		return $this->hasOne('App\GemOrderDetail');
	}

	public function scopeWithRelatedModels($query) {
		return $query->with([
			'statusOption',
			'user',
			'hasOneOrderRecipient',
			'hasOneOrderTotal',
			'hasOneGemOrderDetail',
			'hasOnePaymentDetail',
		]);
	}


	/*checkout*/

	public function postCheckout($data) {
		$temp = GemOrderHeaderTemp::with(['hasOneGemOrderDetailTemp', 'hasOneOrderTotal'])
			->whereId($data['order_id'])->whereOrderNumber($data['order_number'])->first()->toArray();
		$temp['status_option_id'] = $data['status_option_id'];
		$response = $this->create($temp);

		if ($data['order_shipping']) {

			$response->hasOneOrderShipping()->create($data['order_shipping']);
		}

		$response->hasOneOrderTotal()->create($temp['has_one_order_total']);
		$response->hasOneGemOrderDetail()->create($temp['has_one_gem_order_detail_temp']);

		if (isset($data['order_recipient']) && $data['order_recipient']) {
			$user = User::with(['hasOneWalletAccount', 'deviceIds'])->where('phoneNumber', $data['order_recipient']['phone_number'])->first();
			$data['order_recipient']['full_name'] = $user ? $user->username : '';
			$response->hasOneOrderRecipient()->create($data['order_recipient']);
		}


		return [
			"id"    => $response->id,
			"total" => $temp['has_one_order_total']['grand_total'],
		];
	}

	public function checkoutComplete($data) {
		$response = $this->with(['hasOneGemOrderDetail', 'user' => function ($query) {
			$query->with(['hasOneWalletAccount', 'deviceIds']);
		}])->find($data['order_id']);


		if ($response->status_option_id == Status::PENDING) {
			$response->status_option_id = Status::COMPLETED;
			$response->save();
			$this->setPayment([
				"table_id"          => $data['order_id'],
				"payment_method_id" => 2,
				"transaction_id"    => $data['paymentId'],
				"status_option_id"  => Status::PAID,
				"meta"              => [
					"payment_id" => $data['paymentId'],
				],
			]);

			$wallet = new WalletRepository();
			$this->givePoints($wallet, $response->user->hasOneWalletAccount->account_number, $response->hasOneGemOrderDetail->gem_package_amount . '00');
			$balance = $wallet->balanceInquiry($response->user->hasOneWalletAccount->account_number);
			foreach ($response->user->deviceIds as $device) {
				$push = new PushNotification();
				$push->userBalance(json_encode($balance), $device->deviceToken);
			}
		}

		return ['amount' => $response->hasOneGemOrderDetail->gem_package_amount];
	}

	public function setPayment($data) {
		$data['table_type'] = 'gem_order_headers';
		PaymentDetail::create($data);

	}

	public function givePoints($wallet, $destination_account, $amount) {

		$source = [
			"account"  => env('WALLET_MERCHANT_GEM_ACCOUNT'),
			"type"     => "ACCOUNT_NUMBER",
			"currency" => "GEM",
		];
		$destination = [
			"account"  => $destination_account,
			"type"     => "DEVICE_ID",
			"currency" => "GEM",
		];


		return $wallet->fundTransfer($source, $destination, $amount);
	}

}

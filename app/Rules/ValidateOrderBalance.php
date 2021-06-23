<?php

namespace App\Rules;

use App\Repositories\Wallet\WalletRepository;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\App;

class ValidateOrderBalance implements Rule {


	private $model;
	private $field;
	private $currency;
	/**
	 * @var WalletRepository
	 */
	private $walletRepository;


	public function __construct($model, $field) {
		$this->model = App::make($model);
		$this->field = $field;
		$this->walletRepository = new WalletRepository();
	}

	/**
	 * Determine if the validation rule passes.
	 *
	 * @param string $attribute
	 * @param mixed $value
	 * @return bool
	 */
	public function passes($attribute, $value) {

		$okay = false;
		$order = $this->model->with(['hasManyOrderTotal','user.hasOneWalletAccount'])->where($this->field,$value)->first();

		$wallet = $this->walletRepository->balanceInquiry($order->user->hasOneWalletAccount->account_number);
		foreach ($wallet['anonymousWallets'] as $currency){
			if($currency['currencyCode'] == 'QOIN'){
				$wallet = $currency;
				$this->currency = $currency['currencyCode'];
			}
		}
		$grand_total = $order->hasManyOrderTotal[0]->grand_total.'00';
		if($wallet['availableBalance'] >= $grand_total){
			$okay = true;
		}

		return $okay;

	}

	/**
	 * Get the validation error message.
	 *
	 * @return string
	 */
	public function message() {
        $currency = $this->currency === 'QOIN' ? 'Qoins' : 'Gems';
        $message = "You do not have enough QR ".$currency.".";
		return $message;

	}
}

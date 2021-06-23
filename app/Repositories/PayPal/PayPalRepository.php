<?php

namespace App\Repositories\PayPal;

use App\Repositories\PayPal\Contracts\PayPalInterface;
use Braintree\Gateway;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;

class PayPalRepository implements PayPalInterface {

//	private $gateway;
	/**
	 * @var Transaction
	 */
	private $transaction;
	/**
	 * @var Braintree\Gateway
	 */
	private $gateway;

	public function __construct() {

		$this->gateway = new Gateway([
			'accessToken' => env('PAYPAL_ACCESS_TOKEN'),
		]);
	}


	public function generateToken() {

		$response = $this->gateway->clientToken()->generate();

		return $response;

	}

	public function pay($data) {

		$response = $this->gateway->transaction()->sale(
			[
				"amount"             => $data['amount'],
				"paymentMethodNonce" => $data['nonce'],
				"merchantAccountId"  => env('BRAINTREE_MERCHANT'),
				"options"            => [
					"submitForSettlement" => true,
				],
			]
		);


		if ($response->success) {
			$inputs = [
				"status" => true,
				"data"   => $response->transaction,
			];

			return $inputs;
		}

		return [
			"status" => false,
			"data"   => $response->errors,
		];
	}


	/*checkout*/
	public function getApiContext($paypalConf) {
		$apiContext = new ApiContext(new OAuthTokenCredential($paypalConf['client_id'], $paypalConf['secret']));

		return $apiContext;
	}


	public function setPayer() {
		$payer = new Payer();
		$payer->setPaymentMethod('paypal');

		return $payer;
	}

	public function setAmount($total) {
		$amount = new Amount();

		$amount->setTotal($total)
			->setCurrency('PHP');

		return $amount;
	}


	public function setTransaction($amount, $description) {
		$transaction = new Transaction();
		$transaction->setAmount($amount)
			->setDescription($description);

		return $transaction;
	}


	/**
	 * @return RedirectUrls
	 */
	public function setRedirectUrls($data) {

		$redirectUrl = new RedirectUrls();
		$redirectUrl->setReturnUrl($data['success'])
			->setCancelUrl($data['cancel']);

		return $redirectUrl;
	}

	public function setPayment($payer, $redirectUrl, $transaction) {

		$payment = new Payment();
		$payment->setIntent('Sale')
			->setPayer($payer)
			->setRedirectUrls($redirectUrl)
			->setTransactions([$transaction]);

		return $payment;
	}

	public function paymentExecution($payerId) {
		$execution = new PaymentExecution();
		$execution->setPayerId($payerId);

		return $execution;
	}
}

<?php

namespace App\Http\Controllers\V1_1_0;

use App\Helpers\Status;
use App\Repositories\PayPal\PayPalRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use PayPal\Api\Payment;

class PayPalController extends Controller {
	//

	/**
	 * @var PayPalRepository
	 */
	private $payPalRepository;
	/**
	 * @var \PayPal\Rest\ApiContext
	 */
	private $_api_context;

	public function __construct(PayPalRepository $payPalRepository) {

		$paypalConf = Config::get('paypal');

		$this->payPalRepository = $payPalRepository;
		$this->_api_context = $this->payPalRepository->getApiContext($paypalConf);
		$this->_api_context->setConfig($paypalConf['settings']);

	}


	public function generateToken() {
		$token = $this->payPalRepository->generateToken();

		$response = [
			"token" => $token,
		];

		return $this->responseSuccess($response, 200);
	}

	public function createTransaction(Request $request, $model) {

		$data = $request->all();

		$data['amount'] = $this->getAmount($model, $data['id']);

		$response = $this->payPalRepository->pay($data);

		if ($response['status']) {
			return $this->responseSuccess(["transaction_id" => $response['data']->id], 200);
		}

		return $this->responseFail($response['data'], 403);

	}


	public function postCheckOut(Request $request, $model) {
		$data = $request->all();


		$order = App::make('App\\' . $model)->postCheckout($data);


		$payer = $this->payPalRepository->setPayer();

		$amount = $this->payPalRepository->setAmount($order['total']);

		$transaction = $this->payPalRepository->setTransaction($amount, "Quest Reward Gems!");
		$url = [
			"success" => URL('v1.1.0/payments/paypal/payment/success?model='.$model.'&order_id='.$order['id'].'&url='.$request->headers->get('origin').'?status=success'),
			"cancel"  => URL('v1.1.0/payments/paypal/payment/cancelled?model='.$model.'&order_id='.$order['id'].'&url='.$request->headers->get('origin').'?status=cancelled'),
		];


		$redirectUrl = $this->payPalRepository->setRedirectUrls($url);
		$payment = $this->payPalRepository->setPayment($payer, $redirectUrl, $transaction);

		try {
			$payment->create($this->_api_context);
		} catch (\PayPal\Exception\PayPalConnectionException $e) {
			if (Config::get('app.debug')) {
				echo "Exception: " . $e->getMessage() . PHP_EOL;
				$err_data = json_decode($e->getData(), true);

				return $this->responseFail(['error' => $err_data], 403);
				exit;
			} else {
				return $this->responseFail(['error' => 'Some error occur, sorry for inconvenient'], 403);
			}
		}


		foreach ($payment->getLinks() as $link) {
			if ($link->getRel() == 'approval_url') {
				$redirectUrl = $link->getHref();
				break;
			}
		}

		$this->setPayment($order,$model,$payment->getId());

		if (isset($redirectUrl)) {
			return $this->responseSuccess(["redirect_url" => $redirectUrl], 200);
		}

	}

	public function setPayment($order,$model,$id){
		App::make('App\\' . $model)->setPayment([
			"table_id" => $order['id'],
			"payment_method_id" => 2,
			"status_option_id" => Status::PENDING,
			"meta" => [
				"payment_id"=> $id
			]
		]);
	}

	public function paymentSuccess(Request $request) {

		$data = $request->all();


		$execution = $this->payPalRepository->paymentExecution($request['PayerID']);

		$payment = Payment::get($request['paymentId'], $this->_api_context);

		$execution = $this->payPalRepository->paymentExecution($request['PayerID']);

		/*Execute the payment*/
		try {
			$payment->execute($execution, $this->_api_context);
			$response = App::make('App\\' . $data['model'])->checkoutComplete($data);
			return redirect($data['url'].'&code=200&amount='.$response['amount']);
		} catch (\PayPal\Exception\PayPalConnectionException $e) {

			return redirect($data['url'].'&code=503');
		}
	}

	public function paymentFailed(Request $request) {
		$data = $request->all();

		return redirect($data['url']);
	}


}

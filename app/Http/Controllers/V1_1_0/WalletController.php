<?php

namespace App\Http\Controllers\V1_1_0;

use App\Repositories\Wallet\WalletRepository;
use App\WalletAccount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WalletController extends Controller
{
	protected $guard = 'user';
    //
	/**
	 * @var WalletRepository
	 */
	private $walletRepository;
	/**
	 * @var WalletAccount
	 */
	private $walletAccount;
	private $account;

	public function __construct(WalletRepository $walletRepository,WalletAccount $walletAccount){

		$this->middleware('auth:' . $this->guard);
		$this->walletAccount = $walletAccount;
		if ($user = auth($this->guard)->user()) {
				$this->account = $this->walletAccount->whereTableId(auth($this->guard)->user()->id)->whereTableType('users')->first();
		}

		$this->walletRepository = $walletRepository;



	}


	public function balanceInquiry(){
		$response = $this->walletRepository->balanceInquiry($this->account->account_number);

		return $this->responseSuccess($response, 200);
	}

	public function exchange(Request $request){
		$data = $request->all();

		$source = [
			"account"  => $this->account->account_number,
			"type"     => "DEVICE_ID",
			"currency" => $data['source_currency'],
		];
		$destination = [
			"account"  => $this->account->account_number,
			"type"     => "DEVICE_ID",
			"currency" =>  $data['destination_currency'],
		];
		$wallet = $this->walletRepository->fundTransfer($source, $destination,$data['amount']);

		if ($wallet['status'] == "FAILED") {
//            $currency = $data['source_currency'] === 'QOIN' ? 'Qoins' : 'Gems';
//            $message = "You do not have enough QR ".$currency.".";
			return $this->responseFail(["message" => $wallet['message'], "code" => 400], 400);
		}
		return $this->responseSuccess($wallet, 200);

	}

	public function rate(Request $request){
		$data = $request->all();
		$wallet = $this->walletRepository->rate($data['destination_currency']);
		return $this->responseSuccess($wallet, 200);

	}
}

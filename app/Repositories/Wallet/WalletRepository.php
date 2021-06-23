<?php namespace App\Repositories\Wallet;


use App\Repositories\Wallet\Contracts\WalletInterface;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;
use GuzzleHttp\Exception\ClientException;

class WalletRepository implements WalletInterface {


	private $walletAccount;
	/**
	 * @var Client
	 */
	private $client;

	public function __construct() {

		$this->client = new Client([
			'verify'   => false,
			'base_uri' => config('env.wallet_api_base_uri')
		]);


		$this->walletAccount = App::make('App\WalletAccount');
	}

	public function createAccount($type) {


		$body = [
			'transactingMerchantId' => config('env.wallet_merchant_id'),
			'deviceType'            => $type,
		];

		$response = $this->client->request('POST', 'https://api.questrewards.com/api/pg/v1/anonymous-wallet/create-for-all-currencies', [
			'headers' => [],
			'json'    => $body,
		]);


		return json_decode($response->getBody()->getContents(), true);

	}

	public function rate($destination_currency) {

		$body = [
			'transactingMerchantId' => config('env.wallet_merchant_id'),
			'currency'            => $destination_currency
		];

		$response = $this->client->request('POST', '/api/pg/v1/currency/exchange-rate', [
			'headers' => [],
			'json'    => $body,
		]);

		return json_decode($response->getBody()->getContents(), true);

	}

	public function transactionLog($account, $type, $filter) {

		$body = [
			'transactingMerchantId' => config('env.wallet_merchant_id'),
			'identifier'            => $account,
			'requestType'           => $type,
			'pageNumber'            => $filter['page'],
			'sortProperty'          => 'logDateTime',
			'sortDirection'         => 'DESC',
		];

		$response = $this->client->request('POST', '/api/pg/v1/wallet/history', [
			'headers' => [],
			'json'    => $body,
		]);

		return json_decode($response->getBody()->getContents(), true);

	}


	public function storeAccount($data) {

		$this->walletAccount->create($data);

		return true;
	}

	public function fundTransfer($source, $destination, $amount) {
		$body = [
			'transactingMerchantId'  => config('env.wallet_merchant_id'),
			'sourceIdentifier'       => $source['account'],
			'sourceRequestType'      => $source['type'],
			'sourceCurrency'         => $source['currency'],
			'destinationIdentifier'  => $destination['account'],
			'destinationRequestType' => $destination['type'],
			'destinationCurrency'    => $destination['currency'],
			'amount'                 => $amount,
			'pin'                    => null,
			'claimCode'              => null,
			'remarks'                => null,
			'paymentOrderNo'         => null,
		];


		try {
			$response = $this->client->request('POST', '/api/pg/v2/wallet/fund-transfer', [
				'headers' => [],
				'json'    => $body,
			]);
			$result  = json_decode($response->getBody()->getContents(), true);
		} catch (ClientException $e) {
			$result  =   json_decode($e->getResponse()->getBody()->getContents(),true);
		}

		return $result;

	}


	public function balanceInquiry($account) {
		$body = [
			'transactingMerchantId' => config('env.wallet_merchant_id'),
			'deviceId'              => $account,
		];

		$response = $this->client->request('POST', '/api/pg/v1/anonymous-wallet/balance', [
			'headers' => [],
			'json'    => $body,
		]);

		return json_decode($response->getBody()->getContents(), true);
	}

}

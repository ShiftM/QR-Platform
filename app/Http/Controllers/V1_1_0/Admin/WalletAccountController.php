<?php

namespace App\Http\Controllers\V1_1_0\Admin;

use App\Booth;
use App\Event;
use App\Helpers\Status;
use App\Repositories\Rest\RestRepository;
use App\Repositories\Wallet\WalletRepository;
use App\User;
use App\WalletAccount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WalletAccountController extends Controller
{
    /**
     * @var RestRepository
     */
    private $rest;
    /**
     * @var WalletRepository
     */
    private $walletRepository;

    public function __construct(WalletAccount $rest, WalletRepository $walletRepository)
    {
        $this->walletRepository = $walletRepository;
        $this->rest = $rest;
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = $request->all();
        $response = $this->rest->getModel()->with(['table' => function ($query) {
        	$query->withTrashed();
        }]);

        if(isset($data['table_type']) && $data['table_type']){
            if ($data['table_type'] === 'events'){
                $response = $response->whereTableType($data['table_type'])->orWhere('table_type', 'users');
            } else {
                $response = $response->whereTableType($data['table_type']);
            }
            if(isset($data['table_id']) && $data['table_id']){
                $this->checkRelation($response, $data);
            }
        }
//        else {
//            $response = $response->where('table_type', '!=' , 'users');
//        }

        $response = $response->whereHasMorph('table', [Event::class, Booth::class, User::class], function ($query, $type) use ($data) {
            /*Temporary solution display all deactivate event/booth*/
            $query->withTrashed();
            if(isset($data['account_detail']) && $data['account_detail']) {
                if ($type === Event::class) {
                    $query->where('title', 'like', '%' . $data['account_detail'] . '%');
                }

                if ($type === Booth::class) {
                    $query->where('name', 'like', '%' . $data['account_detail'] . '%');
                }

                if ($type === User::class) {
                    $query->where('username', 'like', '%' . $data['account_detail'] . '%');
                }
            }
        });

        $response = json_decode($data['paginate']) ? $response->paginate($data['per_page']) : $response->get();
        return listResponse($response);
    }

    public function fundTransfer(Request $request)
    {
        $data = $request->all();
        $currency = 'QOIN';
        $source = array(
            'type' => $data['source']['type'],
            'currency' => $currency,
            'account' => $data['source']['account_number']
        );
        $destination = array(
            'type' => $data['destination']['type'],
            'currency' => $currency,
            'account' => $data['destination']['account_number']
        );
        $amount = $data['amount'].'00';
        $wallet = $this->walletRepository->fundTransfer($source, $destination, $amount);

	    if ($wallet['status'] == "FAILED") {
		    $res = [
				    ['status' => 422,
				    'detail' => 'Source Account Insufficient Balance',
				    'field' => 'source',
					    ]
		    ];
		    return clientErrorResponse($res,422);
	    }
        return listResponse($wallet);
    }

    public function balanceInquiry($id){
//	    $data = $request->all();
		$response = $this->walletRepository->balanceInquiry($id);
	    return showResponse($response);
    }

    public function transactionHistory(Request $request){
        $data = $request->all();
        $transactionLog = $this->walletRepository->transactionLog($data['account_number'], $data['type'], $data);
        return listResponse($transactionLog);
    }

    public function exchangeRate(Request $request){
        $data = $request->all();
        $rate = $this->walletRepository->rate($data['currency']);
        return listResponse($rate);
    }

    public function checkRelation ($response, $data){
        $response = $response->whereHasMorph('table', [Event::class, Booth::class], function ($query, $type) use ($data) {
            /*Temporary solution display all deactivate event/booth*/
            $query->withTrashed();
            if ($type === Event::class && $data['table_type'] == 'events') {
                $query->leftJoin('event_booths', 'event_booths.eventId', 'events.id')->where('event_booths.boothId', $data['table_id']);
            }

            if ($type === Booth::class && $data['table_type'] == 'booths') {
                $query->leftJoin('event_booths', 'event_booths.boothId', 'booths.id')->where('event_booths.eventId', $data['table_id']);
            }
        });

        return $response;
    }
}

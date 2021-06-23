<?php

namespace App\Http\Controllers\V1_1_0;

use App\CartDetail;
use App\CartHeader;
use App\Helpers\ResponseCode;
use App\Helpers\OrderFormatter;
use App\Helpers\ResponseFormatter;
use App\Helpers\Status;
use App\Http\Requests\Admin\IndexRequest;
use App\Http\Requests\OrderRequest;
use App\OrderHeader;
use App\OrderHeaderTemp;
use App\PaymentDetail;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Wallet\WalletRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

    protected $guard = 'user';
    /**
     * @var OrderHeaderTemp
     */
    private $orderHeaderTemp;
    /**
     * @var OrderHeader
     */
    private $orderHeader;
    /**
     * @var int
     */
    private $userId;
    /**
     * @var CartDetail
     */
    private $cartDetail;
    /**
     * @var CartHeader
     */
    private $cartHeader;
    /**
     * @var ResponseFormatter
     */
    private $responseFormatter;
    /**
     * @var OrderFormatter
     */
    private $orderFormatter;
    /**
     * @var OrderRepository
     */
    private $orderRepository;
    /**
     * @var WalletRepository
     */
    private $walletRepository;
    /**
     * @var PaymentDetail
     */
    private $paymentDetail;

    public function __construct(OrderHeaderTemp $orderHeaderTemp, OrderHeader $orderHeader, CartHeader $cartHeader, OrderFormatter $orderFormatter,
                                ResponseFormatter $responseFormatter, OrderRepository $orderRepository, WalletRepository $walletRepository, PaymentDetail $paymentDetail)
    {

        $this->middleware('auth:' . $this->guard);
        $this->orderHeaderTemp = $orderHeaderTemp;
        $this->orderHeader = $orderHeader;
//
        if (auth($this->guard)->user()) {
	        $this->userId = auth($this->guard)->user()->id;
        }

        $this->cartHeader = $cartHeader;
        $this->orderFormatter = $orderFormatter;
        $this->responseFormatter = $responseFormatter;
        $this->orderRepository = $orderRepository;
        $this->walletRepository = $walletRepository;
        $this->paymentDetail = $paymentDetail;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(IndexRequest $request)
    {
        //

        $data = $request->all();

        $response = $this->orderHeader
            ->withRelatedModels()
            ->withCount(['hasManyOrderDetail as total_quantity' => function ($query) {
	            $query->select(DB::raw("SUM(quantity)"));
             }])
            ->whereUserId($this->userId);


        if (isset($data['order_number']) && $data['order_number']) {
            $response = $response->where('order_number', 'like', '%' . $data['order_number'] . '%');
        }

        if (isset($data['status_option_id']) && $data['status_option_id']) {
            $response = $response->whereStatusOptionId($data['status_option_id']);
        } else {
            $response = $response->where('status_option_id', '!=', 2);
        }


        $response = $response->orderBy('id', 'DESC');

        $response = json_decode($data['paginate']) ? $response->paginate($data['per_page'])->toArray() : $response->get()->toArray();

        if (!json_decode($data['paginate'])) {
            $arrays = $response;
            $response = [];
            foreach ($arrays as $key => $arr) {
                array_push($response, $this->responseFormatter->toOrder($arr));
            }

            return $this->responseSuccess($response, ResponseCode::OKAY);
        } else {
            $arrays = $response['data'];
            $response['data'] = [];
            foreach ($arrays as $key => $arr) {
                array_push($response['data'], $this->responseFormatter->toOrder($arr));
            }

            return $this->responseSuccessWithPagination($response, ResponseCode::OKAY);
        }

    }

    public function orderLists(IndexRequest $request)
    {
        $data = $request->all();

        $response = $this->orderHeader
            ->withRelatedModels()
	        ->withCount(['hasManyOrderDetail as total_quantity' => function ($query) {
		        $query->select(DB::raw("SUM(quantity)"));
	        }])
            ->whereUserId($this->userId);


        if (isset($data['order_number']) && $data['order_number']) {
            $response = $response->where('order_number', 'like', '%' . $data['order_number'] . '%');
        }

        if (isset($data['status_option_id']) && $data['status_option_id']) {
            $response = $response->whereStatusOptionId($data['status_option_id']);
        }

        $response = $response->orderBy('id', 'DESC');

        $response = json_decode($data['paginate']) ? $response->paginate($data['per_page'])->toArray() : $response->get()->toArray();
        if (!json_decode($data['paginate'])) {
            $arrays = $response;
            $response = [];
            foreach ($arrays as $key => $arr) {
                array_push($response, $this->orderFormatter->toOrder($arr));
            }



	        return $this->responseSuccess($response, ResponseCode::OKAY);
        } else {
            $arrays = $response['data'];
            $response['data'] = [];
            foreach ($arrays as $key => $arr) {
                array_push($response['data'], $this->orderFormatter->toOrder($arr));
            }

            return $this->responseSuccessWithPagination($response, ResponseCode::OKAY);
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(OrderRequest $request)
    {
        //

        $data = $request->all();


        $temp = $this->orderHeaderTemp
            ->withRelatedModels()
            ->whereId($data['order_id'])->whereOrderNumber($data['order_number'])->first()->toArray();
        $temp['status_option_id'] = Status::PENDING;

        $response = $this->orderHeader->create($temp);
        $response->hasManyOrderTotal()->createMany($temp['has_many_order_total']);
        $response->hasManyOrderDetail()->createMany($temp['has_many_order_detail_temp']);
        $response->hasOneOrderRecipient()->create($data['order_recipient']);
        $response->hasOneOrderShipping()->create($data['order_shipping']);

        $pay =  $this->pay($response->user_id,$response);

        if(!$pay["status"]){
            $order = $this->orderHeader->find($response->id);
            $order->status_option_id = Status::ARCHIVE;
            $order->save();
        	return clientErrorResponse($pay['data'],422);
        }
        $this->orderRepository->updateInventory($temp['has_many_order_detail_temp'], 'remove');
        $this->removeCartItems();

        $response = json_decode($this->show($response->id)->content(), true);

        return $this->responseSuccess($response['data'], ResponseCode::OKAY);
    }

    public function pay($userId, $order)
    {
        $response = $this->orderHeader->with(['hasManyOrderTotal', 'user.hasOneWalletAccount'])->find($order->id);
        $res = [
            "status" => true,
        ];

        $source = [
            "account" => $response->user->hasOneWalletAccount->account_number,
            "type" => "DEVICE_ID",
            "currency" => "QOIN",
        ];
        $destination = [
            "account" => config('env.wallet_merchant_coin_account'),
            "type" => "ACCOUNT_NUMBER",
            "currency" => "QOIN",
        ];

	    $grand_total = $response->hasManyOrderTotal[0]->grand_total.'00';
        $wallet = $this->walletRepository->fundTransfer($source, $destination, $grand_total);

        if ($wallet['status'] == "FAILED") {
            $res = [
                "status" => false,
                "data" => [
                    'status' => 422,
                    'detail' => $wallet['message'],
                    'field' => 'order_number',
                ]
            ];
        } else {
            $payment = [
                "status_option_id" => Status::PAID,
                "table_type" => "order_headers",
                "table_id" => $response->id,
                "transaction_id" => $wallet['refNo'],
                "payment_method_id" => 1,

            ];
            $this->paymentDetail->create($payment);
        }

        return $res;
    }

    public function removeCartItems()
    {
        $userId = $this->userId;
        $cart = $this->cartHeader->whereUserId($userId)->first();
        $cart->hasManyCartDetail()->delete();

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        $response = $this->orderHeader
            ->withRelatedModels()
            ->find($id);

        return $this->responseSuccess($response, ResponseCode::OKAY);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //

        $data = $request->all();

        if ($response = $this->orderHeader->getModel()->find($id)) {
            $response->fill($data)->save();

            $response = json_decode($this->show($response->id)->content(), true);

            return $this->responseSuccess($response['data'], ResponseCode::OKAY);

        }

        return $this->responseFailWithCode(ResponseCode::NOT_FOUND);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //

        $this->orderHeader->whereUserId($id)->update(["status_option_id" => 2]);
    }
}

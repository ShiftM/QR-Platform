<?php

namespace App\Http\Controllers\V1_1_0\Admin;

use App\Booth;
use App\Event;
use App\GemOrderHeader;
use App\Item;
use App\ItemStock;
use App\OrderHeader;
use App\Repositories\Rest\RestRepository;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SearchAutofillController extends Controller
{
    /**
     * @var RestRepository
     */
    private $user;
    private $item;
    private $itemStock;
    private $orderHeader;

    private $event;
    private $booth;
	/**
	 * @var RestRepository
	 */
	private $gemOrderHeader;

	public function __construct(User $user, Item $item, ItemStock $itemStock, OrderHeader $orderHeader, Event $event, Booth $booth, GemOrderHeader $gemOrderHeader)
    {

        $this->user = new RestRepository($user);
        $this->item = new RestRepository($item);
        $this->itemStock = new RestRepository($itemStock);
        $this->orderHeader = new RestRepository($orderHeader);
        $this->gemOrderHeader = new RestRepository($gemOrderHeader);
        $this->event = new RestRepository($event);
        $this->booth = new RestRepository($booth);

    }

    public function salesByCategoryAndItem(Request $request)
    {
        $data = $request->all();
        $itemInfo = $this->item->getModel()->select(DB::raw('name as suggestion'))->get();
        if (isset($data['item_detail']) && $data['item_detail']) {
            $arrs = array();
            $arrs[] = $this->item->getModel()
                ->select(DB::raw('name as suggestion'))
                ->where('name', 'LIKE', '%' . $data['item_detail'] . '%')->get()->toArray();

            $arrs[] = $this->itemStock->getModel()
                ->select(DB::raw('sku as suggestion'))
                ->where('sku', 'LIKE', '%' . $data['item_detail'] . '%')->get()->toArray();
            $list = array();

            foreach ($arrs as $arr) {
                if (is_array($arr)) {
                    $list = array_merge($list, $arr);
                }
            }
            return listResponse($list);
        }
        return listResponse($itemInfo);
    }

    public function salesByCustomerName(Request $request)
    {
        $data = $request->all();
        $customerInfo = $this->user->getModel()->select(DB::raw('phoneNumber as suggestion'))->get();
        if (isset($data['user_detail']) && $data['user_detail']) {
            $arrs = array();
            $arrs[] = $this->user->getModel()
                ->select(DB::raw('phoneNumber as suggestion'))
                ->where('phoneNumber', 'LIKE', '%' . $data['user_detail'] . '%')->get()->toArray();

            $arrs[] = $this->user->getModel()
                ->select(DB::raw('email as suggestion'))
                ->where('email', 'LIKE', '%' . $data['user_detail'] . '%')->get()->toArray();
            $list = array();

            foreach ($arrs as $arr) {
                if (is_array($arr)) {
                    $list = array_merge($list, $arr);
                }
            }
            return listResponse($list);
        }
        return listResponse($customerInfo);
    }

    public function orderInfo(Request $request)
    {
        $data = $request->all();
        $customerInfo = $this->orderHeader->getModel()->select(DB::raw('order_number as suggestion'))->get();
        if (isset($data['keyword']) && $data['keyword']) {
            $arrs = array();
            $arrs[] = $this->user->getModel()
                ->select(DB::raw('username as suggestion'))
                ->where('username', 'LIKE', '%' . $data['keyword'] . '%')->get()->toArray();

            $arrs[] = $this->orderHeader->getModel()
                ->select(DB::raw('order_number as suggestion'))
                ->where('order_number', 'LIKE', '%' . $data['keyword'] . '%')->get()->toArray();
            $list = array();

            foreach ($arrs as $arr) {
                if (is_array($arr)) {
                    $list = array_merge($list, $arr);
                }
            }
            return listResponse($list);
        }
        return listResponse($customerInfo);
    }

    public function walletAccountInfo(Request $request)
    {
        $data = $request->all();
        $walletInfo = DB::table('events')->select(DB::raw('title as suggestion'))->get();
        if (isset($data['account_detail']) && $data['account_detail']) {
            $arrs = array();
            $arrs[] = DB::table('events')
                ->select(DB::raw('title as suggestion'))
                ->where('title', 'LIKE', '%' . $data['account_detail'] . '%')->get()->toArray();

            $arrs[] = $this->booth->getModel()
                ->select(DB::raw('name as suggestion'))
                ->where('name', 'LIKE', '%' . $data['account_detail'] . '%')->get()->toArray();


            $arrs[] = $this->user->getModel()
                ->select(DB::raw('username as suggestion'))
                ->where('username', 'LIKE', '%' . $data['account_detail'] . '%')->get()->toArray();
            $list = array();

            foreach ($arrs as $arr) {
                if (is_array($arr)) {
                    $list = array_merge($list, $arr);
                }
            }
            return listResponse($list);
        }
        return listResponse($walletInfo);
    }

    public function eventInfo(Request $request)
    {
        $data = $request->all();
        $eventInfo = DB::table('events')->select(DB::raw('title as suggestion, id'));
        if (isset($data['event_detail']) && $data['event_detail']) {
            $eventInfo = $eventInfo->where('title', 'LIKE', '%' . $data['event_detail'] . '%')
                ->orWhere('name', 'like' ,'%'.$data['name'].'%');
        }
        return listResponse($eventInfo->get());
    }
	public function gemOrderInfo(Request $request)
	{
		$data = $request->all();
		$customerInfo = $this->gemOrderHeader->getModel()->select(DB::raw('order_number as suggestion'))->get();
		if (isset($data['keyword']) && $data['keyword']) {
			$arrs = array();
			$arrs[] = $this->user->getModel()
				->select(DB::raw('username as suggestion'))
				->where('username', 'LIKE', '%' . $data['keyword'] . '%')->get()->toArray();

			$arrs[] = $this->orderHeader->getModel()
				->select(DB::raw('order_number as suggestion'))
				->where('order_number', 'LIKE', '%' . $data['keyword'] . '%')->get()->toArray();
			$list = array();

			foreach ($arrs as $arr) {
				if (is_array($arr)) {
					$list = array_merge($list, $arr);
				}
			}
			return listResponse($list);
		}
		return listResponse($customerInfo);
	}


}

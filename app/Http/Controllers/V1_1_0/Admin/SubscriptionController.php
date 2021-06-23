<?php

namespace App\Http\Controllers\V1_1_0\Admin;

use App\Http\Requests\Admin\SubscriptionRequest;
use App\Http\Requests\Admin\IndexRequest;
use App\AppUpdate;
use App\Helpers\ResponseCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\SubscriptionPlan;
use App\Repositories\Rest\RestRepository;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    
	/**
	 * @var RestRepository
	 */
	private $rest;

	public function __construct(SubscriptionPlan $rest) {

		$this->rest = new RestRepository($rest);

	}

	public function getPlans() {
        
        $response = DB::table('subscription_plans')->simplePaginate(100);
        return listResponse($response);
    }
    
	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(IndexRequest $request) {

        $data = $request->all();

		$callback = function () use ($data) {
			$response = SubscriptionPlan::getModel();

			if (isset($data['plan_name']) && $data['plan_name']) {
				$response = $response->where('name', 'like', '%' . $data['name'] . '%');
			}

			if (isset($data['order_by']) && $data['order_by']) {
				$response = $response->orderBy($data['order_by_key'], $data['order_by']);
			}

            if(isset($data['has_featured']) && json_decode($data['has_featured'])){
                $featured = [];
                $nonFeatured = [];
                $filter = $response->get();
                foreach($filter as $key => $value){
                    if($value->hasOneFeaturedItem){
                        array_push($featured, $value);
                    } else {
                        array_push($nonFeatured, $value);
                    }
                }

                $response =  array_merge($featured, $nonFeatured);
                $myCollectionObj = collect($response);
                $response = json_decode($data['paginate']) ? $this->paginate($myCollectionObj, $data['per_page']) : $response->get();
            } else {
                $response = json_decode($data['paginate']) ? $response->paginate($data['per_page']) : $response->get();
            }

			return listResponse($response);
		};

		return $this->exceptionHandler($callback);
	}


    /**
    * Store a newly created resource in storage.
    *
    * @return Response
    */
    public function create(Request $request)
    {

        $data = $request->all();
        
        $rules = [
            'plan_name' => 'required',
            'period' => 'required',
            'price' => 'required',
            'item_points_cap' => 'required',
        ];

        try {

            $validation = $this->validator($data, $rules);

            if ($validation->fails()) {
                throw new \Exception("ValidationException");
            }
            $existingUser = SubscriptionPlan::getModel()->where('plan_name', $data['plan_name'])->first();

            if (!$existingUser) {

                $subscriptionplan = SubscriptionPlan::create($data);
                return $this->responseSuccess(['subscriptionplan' => $subscriptionplan], 200);

            } else {
                return response()->json(['message' => 'Plan name is already existing',
                                        'error' => '402'], 400);
            }
        } catch (\Exception $ex) {
            $validationMessage = $ex->getMessage();

            if ($validationMessage === "ValidationException") {
                return $this->responseFailWithCode(600);
            } else if ($validationMessage === "NotFoundException") {
                return $this->responseFailWithCode(700);
            }

            return $this->responseFailWithCode(500);
        }
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request) {

        $data = $request->all();
        $callback = function () use ($id, $data) {
            $response = SubscriptionPlan::getModel();
            $response = $response->find($id);
            if ($response) {
                return showResponse($response);
            }

            return notFoundResponse();
        };

        return $this->exceptionHandler($callback);

    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(SubscriptionRequest $request, $id) {
        $data = $request->all();

        $callback = function () use ($data, $id) {

            if ($response = SubscriptionPlan::getModel()->find($id)) {

                $response->fill($data)->save();

                return showResponse($response);
            }

            return notFoundResponse();
        };

        return $this->exceptionHandler($callback);
    }

    /**
    * Remove the specified resource from storage.
    *
    * @param  int  $id
    * @return Response
    */
    public function delete($id) {

        $callback = function () use ($id) {

            if ($response = SubscriptionPlan::getModel()->find($id)) {

                $response->delete($id);

                return deletedResponse();
            }

            return notFoundResponse();
        };

        return $this->exceptionHandler($callback);

    }

    public function paginate($items, $perPage = 5, $page = null, $options = [])
    {
        $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
        $items = $items instanceof Collection ? $items : Collection::make($items);
        return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
    }
}

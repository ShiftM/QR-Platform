<?php

namespace App\Http\Controllers\V1_1_0\Admin;

use App\Http\Requests\Admin\IndexRequest;
use App\Http\Requests\Admin\ItemRequest;
use App\Repositories\Rest\RestRepository;
use App\Item;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ItemController extends Controller {

	/**
	 * @var RestRepository
	 */
	private $rest;

	public function __construct(Item $rest) {

		$this->rest = new RestRepository($rest);

	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(IndexRequest $request) {
		//
		$data = $request->all();

		$callback = function () use ($data) {
			$response = $this->rest->getModel();

			if (isset($data['with_related_models']) && json_decode($data['with_related_models'])) {
				$response = $response->withRelatedModels($data)
                    ->whereHas('hasManyItemCategory', function ($query) {
                        $query->with(['categoryHeader' => function($q){
                            $q->whereStatusOptionId(1);
                        }]);
                    });
			}


			if (isset($data['name']) && $data['name']) {
				$response = $response->where('name', 'like', '%' . $data['name'] . '%');
			}


			if (isset($data['status_option_id']) && $data['status_option_id']) {
				$response = $response->whereStatusOptionId($data['status_option_id']);
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
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create() {
		//
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(ItemRequest $request) {
		//


		$data = $request->all();

		$callback = function () use ($data) {

			$response = $this->rest->create($data);

			return createdResponse($response);
		};

		return $this->exceptionHandler($callback);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id, Request $request) {
		//

		$data = $request->all();


		$callback = function () use ($id, $data) {
			$response = $this->rest->getModel();
			if (isset($data['with_related_models']) && json_decode($data['with_related_models'])) {
				$response = $response->withRelatedModels($data);
			}
			$response = $response->find($id);
			if ($response) {
				return showResponse($response);
			}

			return notFoundResponse();
		};

		return $this->exceptionHandler($callback);

	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit($id) {
		//
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(ItemRequest $request, $id) {
		//
		$data = $request->all();

		$callback = function () use ($data, $id) {

			if ($response = $this->rest->getModel()->find($id)) {

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
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id, Request $request) {
		//

		$data = $request->all();


		$callback = function () use ($id, $data) {

			if ($response = $this->rest->getModel()->find($id)) {

				$response->delete($data);

				return deletedResponse();
			}

			return notFoundResponse();
		};

		return $this->exceptionHandler($callback);

	}

	public function forceDestroy($id) {
		$callback = function () use ($id) {

			if ($response = $this->rest->getModel()->find($id)) {

				$response->forceDelete();

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

<?php

namespace App\Http\Controllers\V1_1_0\Admin;

use App\Http\Requests\Admin\IndexRequest;
use App\Http\Requests\Admin\ItemVariantRequest;
use App\ItemVariant;
use App\Repositories\Rest\RestRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ItemVariantController extends Controller {

	/**
	 * @var RestRepository
	 */
	private $rest;

	public function __construct(ItemVariant $rest) {

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

			if (isset($data['status_option_id']) && $data['status_option_id']) {
				$response = $response->whereStatusOptionId($data['status_option_id']);
			}

			$response = json_decode($data['paginate']) ? $response->paginate($data['per_page']) : $response->get();


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
	public function store(ItemVariantRequest $request) {
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
				$response = $response->withRelatedModels();
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
	public function update(ItemVariantRequest $request, $id) {
		//
		$data = $request->all();


		$callback = function () use ($data, $id) {
			$response = $this->rest->getModel()->updateOrCreate(
				["id" => $id, "item_id" => $data['item_id']],
				$data
			);

			return showResponse($response);
		};

		return $this->exceptionHandler($callback);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param int $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		//

		$callback = function () use ($id) {

			if ($response = $this->rest->getModel()->find($id)) {

				$response->delete();

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

}

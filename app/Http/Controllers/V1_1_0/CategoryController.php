<?php

namespace App\Http\Controllers\V1_1_0;

use App\CategoryHeader;
use App\Helpers\ResponseCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;


class CategoryController extends Controller {
	//
	/**
	 * @var CategoryHeader
	 */
	private $categoryHeader;

	public function __construct(CategoryHeader $categoryHeader) {

		$this->categoryHeader = $categoryHeader;
	}

	public function index() {


		$response = $this->categoryHeader->orderByRaw(
			DB::raw('CASE
					WHEN name = "Featured" THEN 1
					END DESC'
			)
		)->whereStatusOptionId(1)->get();

		$response = $response->map(function($response) {
			$response->default = $response->name == "Featured" ? true : false;

			return $response;
		})->toArray();

		$all = [
			'id' => 0,
			'name' => 'All',
			'slug' => 'all',
			'statusOptionId' => 1,
			'createdAt' => '',
			'updatedAt' => '',
			'default' => false
		];

		array_splice($response,1,0,[$all]);
		return $this->responseSuccess($response, ResponseCode::OKAY);
	}

}

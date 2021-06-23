<?php

namespace App\Http\Controllers\V1_1_0;

use App\Helpers\ResponseCode;
use App\NumberView;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;

class NumberViewController extends Controller {
	//
	protected $guard = 'user';
	private $userId;
	/**
	 * @var NumberView
	 */
	private $numberView;

	public function __construct(NumberView $numberView) {


		if ($user = auth($this->guard)->user()) {
			$this->userId = auth($this->guard)->user()->id;
		}


		$this->numberView = $numberView;
	}


	public function store(Request $request){
		$data = $request->all();
		$inputs = [
			"table_type" => $data['type'],
			"table_id" => $data['id'],
			"user_id" => $this->userId
		];

		$response = $this->numberView->create($inputs);
		return $this->responseSuccess($response, ResponseCode::OKAY);
	}

	public function getViewed(Request $request) {

        $data = $request->all();
		$callback = function () use ($data) {
			$responseMain = NumberView::getModel()->where('table_type', '=', 'quest-view');
			
			$filter = DB::table('quests')->get();

			$new = [];
			foreach($filter as $item){
				$viewCount = 0;
				$response = $responseMain->where('table_id', '=', $item->id)->get();

				foreach($response as $record){
					$viewCount = $viewCount + 1;
				}

				$item = array (
					'title' => $item->title,
					'id' => $item->id,
					'description' => $item->description,
					'count' => $viewCount
				);
				array_push($new, $item);
			}
			$myCollectionObj = collect($new);
			$response = $this->paginate($myCollectionObj, $data['per_page']);

			return listResponse($response);
		};
		return $this->exceptionHandler($callback);
	}

	public function getStarted(Request $request) {

        $data = $request->all();
		$callback = function () use ($data) {
			$responseMain = NumberView::getModel()->where('table_type', '=', 'quest-start');
			
			$filter = DB::table('quests')->get();

			$new = [];
			foreach($filter as $item){
				$viewCount = 0;
				$response = $responseMain->where('table_id', '=', $item->id)->get();

				foreach($response as $record){
					$viewCount = $viewCount + 1;
				}

				$item = array (
					'title' => $item->title,
					'id' => $item->id,
					'description' => $item->description,
					'count' => $viewCount
				);
				array_push($new, $item);
			}
			$myCollectionObj = collect($new);
			$response = $this->paginate($myCollectionObj, $data['per_page']);

			return listResponse($response);
		};
		return $this->exceptionHandler($callback);
	}

	public function getFinished(Request $request) {

        $data = $request->all();
		$callback = function () use ($data) {
			$responseMain = NumberView::getModel()->where('table_type', '=', 'quest-finish');
			
			$filter = DB::table('quests')->get();

			$new = [];
			foreach($filter as $item){
				$viewCount = 0;
				$response = $responseMain->where('table_id', '=', $item->id)->get();

				foreach($response as $record){
					$viewCount = $viewCount + 1;
				}

				$item = array (
					'title' => $item->title,
					'id' => $item->id,
					'description' => $item->description,
					'count' => $viewCount
				);
				array_push($new, $item);
			}
			$myCollectionObj = collect($new);
			$response = $this->paginate($myCollectionObj, $data['per_page']);

			return listResponse($response);
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

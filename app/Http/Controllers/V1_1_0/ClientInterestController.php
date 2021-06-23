<?php

namespace App\Http\Controllers\V1_1_0;

use App\ClientInterest;
use App\Helpers\ResponseCode;
use App\Helpers\ResponseFormatter;
use App\Http\Requests\Admin\IndexRequest;
use App\Http\Requests\ClientInterestRequest;
use App\Http\Requests\WishListRequest;
use App\Repositories\Rest\RestRepository;
use App\WishListDetail;
use App\WishListHeader;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ClientInterestController extends Controller {
    protected $guard = 'user';
    /**
     * @var RestRepository
     */
    private $rest;
    private $detail;
    private $userId;
    /**
     * @var ResponseFormatter
     */
    private $responseFormatter;

    public function __construct(ClientInterest $clientInterest) {
        $this->rest = new RestRepository($clientInterest);

	    $this->middleware('auth:' . $this->guard);
        if (auth($this->guard)->user()) {
            $this->userId = auth($this->guard)->user()->id;
        }

//	    $this->userId = 7;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(IndexRequest $request) {
        $data = $request->all();

        $response = $this->rest->getModel()->whereUserId($this->userId);
	    if(isset($data['type']) && $data['type']){
	        if($data['type'] == 'events'){
                $response =$response->with([$data['type'] => function ($q){
                    $q->with(['eventImages' => function ($query) {
                        $query->select('eventId', DB::raw("CONCAT(path, fileName) as fullPath"));
                    }, 'organizer']);
                }])->whereTableType($data['type'])->whereHas($data['type']);
            } else{
                $response =$response->with($data['type'])->whereTableType($data['type'])->whereHas($data['type']);

            }
	    }
        $response = json_decode($data['paginate']) ? $response->paginate($data['per_page'])->toArray() : $response->get()->toArray();
        if (!json_decode($data['paginate'])) {

            return $this->responseSuccess($response, 200);
        } else {

            return $this->responseSuccessWithPagination($response, 200);
        }
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
    public function store(ClientInterestRequest $request) {
        $data = $request->all();
        $userId = $this->userId;
        $response =$this->rest->getModel()
            ->whereUserId($userId)
            ->whereTableType($data['type'])
            ->whereTableId($data['id'])
            ->with(['events'])->first();

        if ($response) {
//            $res = json_decode($this->show($response->id)->content(), true);
            if($data['type'] == 'events'){
                $res['data'] = $this->rest->getModel()->whereId($response->id)->with([$data['type'] => function ($q){
                    $q->with(['eventImages' => function ($query) {
                        $query->select('eventId', DB::raw("CONCAT(path, fileName) as fullPath"));
                    }, 'organizer']);
                }])->first()->toArray();

            } else {
                $res['data'] = $this->rest->getModel()->whereId($response->id)->with([$data['type']])->first()->toArray();

            }
            $response->delete();

            $res['data']['bookmarked'] =  false;
        } else {
            $data['user_id'] = $this->userId;
            $data['table_type'] = $data['type'];
            $data['table_id'] = $data['id'];
            $result = $this->rest->create($data);
            if($data['type'] == 'events'){
                $res['data'] = $this->rest->getModel()->whereId($result->id)->with([$data['type'] => function ($q){
                    $q->with(['eventImages' => function ($query) {
                        $query->select('eventId', DB::raw("CONCAT(path, fileName) as fullPath"));
                    }, 'organizer']);
                }])->first()->toArray();
            } else {
                $res['data'] = $this->rest->getModel()->whereId($result->id)->with([$data['type']])->first()->toArray();
            }
            $res['data']['bookmarked'] =  true;

        }
        return $this->responseSuccess($res['data'], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $response = $this->rest->getModel()->find($id);

        return $this->responseSuccess($response, 200);

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
    public function update(Request $request, $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id,ClientInterestRequest $request) {
        $data = $request->all();

        $userId = $this->userId;
        $response =$this->rest->getModel()
            ->whereUserId($userId)
            ->whereTableType($data['type'])
            ->whereTableId($id)->first();

        if ($response) {

            $res = json_decode($this->show($response->id)->content(), true);

            $response->delete();

            return $this->responseSuccess($res['data'], 200);
        }


        return $this->responseFailWithCode(ResponseCode::NOT_FOUND);


    }
}

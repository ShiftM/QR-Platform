<?php

namespace App\Http\Controllers\V1_1_0;

use App\Helpers\Status;
use App\Http\Requests\Admin\IndexRequest;
use App\InterestOption;
use App\Repositories\Rest\RestRepository;
use App\UserInterest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseCode;

class UserInterestController extends Controller
{
    /**
     * @var RestRepository
     */
    private $rest;
    private $userInterest;

    public function __construct(InterestOption $rest, UserInterest $userInterest)
    {

        $this->rest = new RestRepository($rest);
        $this->userInterest = new RestRepository($userInterest);

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(IndexRequest $request)
    {
        $data = $request->all();

        $response = $this->rest->getModel();
        if (isset($data['status_option_id']) && $data['status_option_id']) {
            $response = $response->whereStatusOptionId($data['status_option_id']);
        }
        if (isset($data['interest_name']) && $data['interest_name']) {
            $response = $response->where('name', 'LIKE', "%" . $data['interest_name'] . "%");
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
    public function store(Request $request)
    {
        $data = $request->all();
        $user = auth('user')->user();
        $this->userInterest->getModel()->whereUserId($user->id)->delete();
        $userInterests = [];
        foreach ($data as $interests) {
            $userInterests['interest_option_id'] = $interests['interest_option_id'];
            $userInterests['user_id'] = $user->id;
            $response = $this->userInterest->create($userInterests);
        }
        $response = $this->rest->getModel()->whereHas('hasManyUserInterest', function ($query) use ($user){
            $query->whereUserId($user->id);
        })->get();
        return $this->responseSuccess($response, 200);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $response = $this->rest->getModel()->whereHas('hasManyUserInterest', function ($query) use ($id){
            $query->whereUserId($id);
        })->whereStatusOptionId(Status::ACTIVE)->get();

        return $this->responseSuccess($response, 200);
    }
}

<?php

namespace App\Http\Controllers\V1_1_0;

use App\GemPackage;
use App\Helpers\ResponseCode;
use App\Repositories\Rest\RestRepository;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GemPackageController extends Controller
{

    /**
     * @var RestRepository
     */
    private $rest;
    private $userId;

    public function __construct(GemPackage $rest)
    {

        $this->rest = new RestRepository($rest);


    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = $request->all();
        $response = $this->rest->getModel();
        if (isset($data['status_option_id']) && $data['status_option_id']) {
            $response = $response->whereStatusOptionId($data['status_option_id']);
        }
        return $this->responseSuccess($response->get()->toArray(), ResponseCode::OKAY);

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

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        if ($response = $this->rest->getModel()->find($id)) {
            return $this->responseSuccess($response, ResponseCode::OKAY);
        }

        return $this->responseFailWithCode(ResponseCode::NOT_FOUND);

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
        $data = $request->all();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {




    }
}

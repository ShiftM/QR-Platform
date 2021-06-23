<?php

namespace App\Http\Controllers\V1_1_0\Admin;

use App\Http\Requests\Admin\IndexRequest;
use App\Http\Requests\Admin\RegionOptionRequest;
use App\RegionOption;
use App\Repositories\Rest\RestRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RegionOptionController extends Controller
{
    /**
     * @var RestRepository
     */
    private $rest;

    public function __construct(RegionOption $rest)
    {

        $this->rest = new RestRepository($rest);

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(IndexRequest $request)
    {
        $data = $request->all();

        $callback = function () use ($data) {
            $response = $this->rest->getModel()->withRelatedModels();
            if(isset($data['status_option_id']) && $data['status_option_id']){
                $response = $response->whereStatusOptionId($data['status_option_id']);
            }
            if(isset($data['country_option_id'])){
                $response = $response->whereCountryOptionId($data['country_option_id']);
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
    public function store(RegionOptionRequest $request)
    {
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
    public function show($id)
    {
        $callback = function () use ($id) {

            if ($response = $this->rest->getModel()->find($id)) {
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
    public function update(RegionOptionRequest $request, $id)
    {
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
    public function destroy($id)
    {
        $callback = function () use ($id) {

            if ($response = $this->rest->getModel()->find($id)) {

                $response->delete();

                return deletedResponse();
            }

            return notFoundResponse();
        };

        return $this->exceptionHandler($callback);
    }
}

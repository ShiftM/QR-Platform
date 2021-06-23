<?php

namespace App\Http\Controllers\V1_1_0\Admin;

use App\CountryOption;
use App\Http\Requests\Admin\CountryOptionRequest;
use App\Http\Requests\Admin\IndexRequest;
use App\Repositories\Rest\RestRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CountryOptionController extends Controller
{
    /**
     * @var RestRepository
     */
    private $rest;

    public function __construct(CountryOption $rest)
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
            $response = $this->rest->getModel();
            if(isset($data['status_option_id']) && $data['status_option_id']){
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
    public function store(CountryOptionRequest $request)
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
    public function update(CountryOptionRequest $request, $id)
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

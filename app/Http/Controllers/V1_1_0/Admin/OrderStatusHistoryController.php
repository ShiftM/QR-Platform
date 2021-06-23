<?php

namespace App\Http\Controllers\V1_1_0\Admin;

use App\Http\Requests\Admin\IndexRequest;
use App\OrderStatusHistory;
use App\Repositories\Rest\RestRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderStatusHistoryController extends Controller
{
    /**
     * @var RestRepository
     */
    private $rest;

    public function __construct(OrderStatusHistory $rest)
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
            if (isset($data['order_header_id']) && $data['order_header_id']) {
                $response = $response->whereOrderHeaderId($data['order_header_id']);
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
    public function store(Request $request)
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

            if ($response = $this->rest->getModel()->withRelatedModels()->find($id)) {
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}

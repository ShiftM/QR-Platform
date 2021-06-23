<?php

namespace App\Http\Controllers\V1_1_0\Admin;

use App\Http\Requests\Admin\CompanyRequest;
use App\Http\Requests\Admin\IndexRequest;
use App\AppUpdate;
use App\Helpers\ResponseCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\QuestAnalytics;
use App\Repositories\Rest\RestRepository;
use DB;

class QuestAnalyticsController extends Controller
{
     /**
    * Store a newly created resource in storage.
    *
    * @return Response
    */
    public function create(Request $request)
    {

        $data = $request->all();
        // $code = $data['countryCode'];
        $rules = [
            'userId' => 'required',
            'eventId' => 'required',
            'questId' => 'required',
            'type' => 'required',
        ];
        $validation = $this->validator($data, $rules);

        if ($validation->fails()) {
            throw new \Exception("ValidationException");
        }

        $questlog = QuestAnalytics::create($data);
        return $this->responseSuccess(['questlog' => $questlog], 200);
    }



    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request) {

        $data = $request->all();

        $callback = function () use ($id, $data) {
            $response = QuestAnalytics::getModel();
            $response = $response->find($id);
            if ($response) {
                return showResponse($response);
            }

            return notFoundResponse();
        };

        return $this->exceptionHandler($callback);

    }
    
}

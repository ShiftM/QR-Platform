<?php

namespace App\Http\Controllers\V1_1_0;

use App\Http\Requests\Admin\CompanyRequest;
use App\Http\Requests\Admin\IndexRequest;
use App\AppUpdate;
use App\Helpers\ResponseCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\UserProgress;
use App\Repositories\Rest\RestRepository;
use DB;

class UserProgressController extends Controller
{
    
	/**
	 * @var RestRepository
	 */
	private $rest;

	public function __construct(UserProgress $rest) {
		$this->rest = new RestRepository($rest);
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getUserQuest(Request $request, $id) {

        $data = $request->all();
        $userprogress = UserProgress::get()->where('userId', $id);
        // dd($userprogress);
        return listResponse($userprogress);
	}

    
    /**
    * Store a newly created resource in storage.
    *
    * @return Response
    */
    public function startQuest(Request $request)
    {
        $data = $request->all();
        $rules = [
            'questId' => 'required',
            'userId' => 'required',
            'status' => 'required',
        ];
        $validation = $this->validator($data, $rules);

        if ($validation->fails()) {
            throw new \Exception("ValidationException");
        }

        $existingquest = DB::table('quests')->where('id', $data['questId'])->first();
        $existinguser = DB::table('users')->where('id', $data['userId'])->first();
        
        if( $existingquest && $existinguser) {
            $userprogress = UserProgress::create($data);
            return $this->responseSuccess(['userprogress' => $userprogress], 200);
        } else {
            return notFoundResponse();
        }     
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function updateQuest(Request $request, $id) {
        $data = $request->all();


        if ($response = UserProgress::getModel()->find($id)) {

            $response->fill($data)->save();

            return showResponse($response);
        } else {
            return notFoundResponse();
        }
    }
}

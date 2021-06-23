<?php

namespace App\Http\Controllers\V1_1_0\Admin;
use App\Http\Requests\Admin\IndexRequest;
use App\AppUpdate;
use App\Helpers\ResponseCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Admin;
use App\Company;

use App\Repositories\Rest\RestRepository;
use Illuminate\Support\Facades\DB;


class AdminController extends Controller
{

	/**
	 * @var RestRepository
	 */
	private $rest;

	public function __construct(Admin $rest) {

		$this->rest = new RestRepository($rest);

	}




	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index(IndexRequest $request) {
		//
        // dd(Company::all());
        $data = $request->all();

		$callback = function () use ($data) {
            $response = DB::table('admins')->simplePaginate(100);

			return listResponse($response);
		};

		return $this->exceptionHandler($callback);
	}

    
        /**
            * Store a newly created resource in storage.
            *
            * @return Response
            */
        public function create(Request $request)
        {
            $data = $request->all();
            
            $rules = [
                'name' => 'required',
                'email' => 'required',
                'emailVerifiedAt' => '',
                'password' => 'required'
            ];
            $validation = $this->validator($data, $rules);

            if ($validation->fails()) {
                throw new \Exception("ValidationException");
            }

            $admin = DB::table('admins')->insert([
                'name' => $data['name'],
                'email' => $data['email'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'gender' => $data['gender'],
                'number' => $data['number'],
                'account_type' => $data['account_type'],

                'emailVerifiedAt' => date('Y-m-d h:i:s'),
                'password' => bcrypt($data['password'])
            ]);
            
            return $this->responseSuccess(['admin' => $admin], 200);

        }

       	/**
         * Display the specified resource.
         *
         * @param int $id
         * @return \Illuminate\Http\Response
         */
        public function show($id, Request $request) {
            //

            $data = $request->all();


            $callback = function () use ($id, $data) {
                $response = DB::table('admins');
                $response = $response->find($id);
                if ($response) {
                    return showResponse($response);
                }

                return notFoundResponse();
            };

            return $this->exceptionHandler($callback);

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
            $data = $request->all();

            $callback = function () use ($data, $id) {

                if ($response = DB::table('admins')->find($id)) {

                    $admin = DB::table('admins')->where('id', $id)->update([
                        'name' => $data['name'],
                        'first_name' => $data['first_name'],
                        'last_name' => $data['last_name'],
                        'gender' => $data['gender'],
                        'number' => $data['number'],
                        'account_type' => $data['account_type'],
                        'emailVerifiedAt' => date('Y-m-d h:i:s'),
                        'password' => bcrypt($data['password'])
                    ]);
                    return $this->responseSuccess(['admin' => $admin], 200);
                } else {
                    return notFoundResponse();

                }

            };

            return $this->exceptionHandler($callback);
        }
    
        /**
        * Remove the specified resource from storage.
        *
        * @param  int  $id
        * @return Response
        */
        public function delete($id, Request $request) {
            //
    
            $data = $request->all();
    
    
            $callback = function () use ($id, $data) {
    
                if ($response =  DB::table('admins')->find($id)) {
    
                    DB::table('admins')->delete($id);
    
                    return deletedResponse();
                }
    
                return notFoundResponse();
            };
    
            return $this->exceptionHandler($callback);
    
        }

        public function paginate($items, $perPage = 5, $page = null, $options = [])
        {
            // $results = new \Illuminate\Pagination\Paginator($parameters);

            $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
            $items = $items instanceof Collection ? $items : Collection::make($items);
            return new LengthAwarePaginator($items->forPage($page, $perPage), $items->count(), $perPage, $page, $options);
        } 
}

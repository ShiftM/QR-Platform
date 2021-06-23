<?php

namespace App\Http\Controllers\V1_1_0;

use App\Http\Requests\Admin\CompanyRequest;
use App\Http\Requests\Admin\IndexRequest;
use App\AppUpdate;
use App\Helpers\ResponseCode;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Company;
use App\Repositories\Rest\RestRepository;
use DB;
use Illuminate\Support\Facades\Log;

class CompanyController extends Controller
{
    
	/**
	 * @var RestRepository
	 */
	private $rest;

	public function __construct(Company $rest) {

		$this->rest = new RestRepository($rest);

	}

    
    public function getSubscriptionPlans($id)
    {
        $states = DB::table("companies")->where("plan_package",$id)->pluck("name","id");
        return json_encode($states);
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
			$response = Company::getModel();

			if (isset($data['name']) && $data['name']) {
				$response = $response->where('name', 'like', '%' . $data['name'] . '%');
			}

			if (isset($data['order_by']) && $data['order_by']) {
				$response = $response->orderBy($data['order_by_key'], $data['order_by']);
			}

            if(isset($data['has_featured']) && json_decode($data['has_featured'])){
                $featured = [];
                $nonFeatured = [];
                $filter = $response->get();
                foreach($filter as $key => $value){
                    if($value->hasOneFeaturedItem){
                        array_push($featured, $value);
                    } else {
                        array_push($nonFeatured, $value);
                    }
                }

                $response =  array_merge($featured, $nonFeatured);
                $myCollectionObj = collect($response);

    
                $response = json_decode($data['paginate']) ? $this->paginate($myCollectionObj, $data['per_page']) : $response->get();
            } else {
                $response = json_decode($data['paginate']) ? $response->paginate($data['per_page']) : $response->get();
            }

			return listResponse($response);
		};

		return $this->exceptionHandler($callback);
	}


	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function getCompanies() {
        $response = Company::get();
        return $this->responseSuccess($response, 200);
	}

    
    /**
    * Store a newly created resource in storage.
    *
    * @return Response
    */
    public function create(CompanyRequest $request)
    {

        $data = $request->all();

        $rules = [
            'name' => 'required',
            'address' => 'required',
            'city' => 'required',
            'country' => 'required',
            'created_at' => '',
            'updated_at' => '',
            'image_url' => '',
            'link' => 'required',
            'logo' => '',
            'plan_package' => 'required',
            'email' => 'required|array'
        ];
            $validation = $this->validator($data, $rules);

            if ($validation->fails()) {
                throw new \Exception("ValidationException");
            }

            usort($data['email'], function($a, $b)
            {
                return strtotime($b['date']) < strtotime($a['date']);
            });

            $data = array(
                'name' => $data['name'],
                'logo' => $data['logo'],
                'address' => $data['address'],
                'city' => $data['city'],
                'country' => $data['country'],
                'plan_package' => $data['plan_package'],
                'created_at' => $data['created_at'],
                'updated_at' => $data['updated_at'],
                'image_url' => $data['image_url'],
                'link' => $data['link'],
                'email' => json_encode($data['email'], true),
            );

            $company = Company::create($data);
            return $this->responseSuccess(['company' => $company], 200);
    }


    public function uploadPhoto(Request $request)
    {
        try {
            $userId = $request->user()->id;
            $path = 'company/' . $userId;


            $file = Input::file('file');
            $size = Input::file('file')->getSize();
            if($file) {
                if($size > 0){
                    $photo = Upload::path($path)->file($file)->s3();

                    $this->user->where('id', $userId)->update([
                        'photo' => $photo['fullPath'],
                    ]);
                    $user = $this->user->find($userId);
                    return $this->responseSuccess($user, 200);
                } else {
                    return $this->responseFail(["message" => "Your file seems corrupted. Please check your file."],500);
                }

            } else {
                return $this->responseFail(["message" => "No file uploaded."],500);
            }


        } catch (\Exception $ex) {
            $validationMessage = $ex->getMessage();

            if ($validationMessage === "ValidationException") {
                return $this->responseFailWithCode(600);
            } else if ($validationMessage === "NotFoundException") {
                return $this->responseFailWithCode(700);
            }

            return $this->responseFailWithCode(422);
        }
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
            $response = Company::getModel();
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
    public function update(CompanyRequest $request, $id) {
        //
        $data = $request->all();

        $callback = function () use ($data, $id) {

            if ($response = Company::getModel()->find($id)) {

                usort($data['email'], function($a, $b)
                {
                    return strtotime($b['date']) < strtotime($a['date']);
                });

                $data = array(
                    'name' => $data['name'],
                    'logo' => $data['logo'],
                    'address' => $data['address'],
                    'city' => $data['city'],
                    'country' => $data['country'],
                    'plan_package' => $data['plan_package'],
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['updated_at'],
                    'image_url' => $data['image_url'],
                    'link' => $data['link'],
                    'email' => json_encode($data['email'], true),
                );

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
    * @param  int  $id
    * @return Response
    */
    public function delete($id, Request $request) {
        //

        $data = $request->all();


        $callback = function () use ($id, $data) {

            if ($response = Company::getModel()->find($id)) {

                $response->delete($data);

                return deletedResponse();
            }

            return notFoundResponse();
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

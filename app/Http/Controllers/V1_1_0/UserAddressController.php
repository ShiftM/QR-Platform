<?php

namespace App\Http\Controllers\V1_1_0;

use App\Helpers\ResponseCode;
use App\Http\Requests\User\UserAddressRequest;
use App\Repositories\Rest\RestRepository;
use App\UserAddress;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserAddressController extends Controller
{
    protected $guard = 'user';
    /**
     * @var RestRepository
     */
    private $rest;
    private $userId;

    public function __construct(UserAddress $rest)
    {
        $this->middleware('auth:' . $this->guard);
        $this->rest = new RestRepository($rest);
        if (auth($this->guard)->user()) {
            $this->userId = auth($this->guard)->user()->id;
        }


    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = $request->all();
        $response = $this->rest->getModel()->whereUserId($this->userId)
            ->select('user_addresses.*', DB::raw("city_options.id as city_id, 
                        country_options.id as country_id, province_options.id as province_id, region_options.id as region_id"))
            ->leftjoin('country_options','country_options.name','user_addresses.country')
            ->leftjoin('region_options','region_options.name','user_addresses.region')
            ->leftjoin('province_options','province_options.name','user_addresses.province')
            ->leftjoin('city_options','city_options.name','user_addresses.city')->groupBy('user_addresses.id');
        if (isset($data['status_option_id']) && $data['status_option_id']) {
            $response = $response->where('user_addresses.status_option_id',$data['status_option_id']);
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
    public function store(UserAddressRequest $request)
    {
        $data = $request->all();
        $checkExist = $this->rest->getModel()->whereUserId($this->userId)->where('status_option_id','!=',2)->first();
        (!$checkExist) ? $data['primary'] = 1 : $data['primary'] = 0;
        $data['user_id'] = $this->userId;
        $data['status_option_id'] = 1;
        $response = $this->rest->create($data);

        return $this->responseSuccess($response, ResponseCode::OKAY);
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
            $response = $response->select('user_addresses.*', DB::raw("city_options.id as city_id, 
                        country_options.id as country_id, province_options.id as province_id, region_options.id as region_id"))
                        ->leftjoin('country_options','country_options.name','user_addresses.country')
                        ->leftjoin('region_options','region_options.name','user_addresses.region')
                        ->leftjoin('province_options','province_options.name','user_addresses.province')
                        ->leftjoin('city_options','city_options.name','user_addresses.city')
                        ->first();
            
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
    public function update(UserAddressRequest $request, $id)
    {
        $data = $request->all();


        if ($response = $this->rest->getModel()->find($id)) {

            $response->fill($data)->save();

            return $this->responseSuccess($response, ResponseCode::OKAY);
        }

        return $this->responseFailWithCode(ResponseCode::NOT_FOUND);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        if ($response = $this->rest->getModel()->find($id)) {

            $response->delete();
            return $this->responseSuccess($response, ResponseCode::OKAY);
        }

        return $this->responseFailWithCode(ResponseCode::NOT_FOUND);


    }
}

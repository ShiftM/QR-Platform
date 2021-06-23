<?php

namespace App\Http\Controllers\V1_1_0;

use App\CityOption;
use App\CountryOption;
use App\Helpers\Status;
use App\ProvinceOption;
use App\RegionOption;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LocationController extends Controller
{
    //


	/**
	 * @var CityOption
	 */
	private $cityOption;
	/**
	 * @var CountryOption
	 */
	private $countryOption;
	/**
	 * @var ProvinceOption
	 */
	private $provinceOption;
	/**
	 * @var RegionOption
	 */
	private $regionOption;

	public function __construct(CityOption $cityOption, CountryOption $countryOption, ProvinceOption $provinceOption,
                                RegionOption $regionOption){

		$this->cityOption = $cityOption;
		$this->countryOption = $countryOption;
		$this->provinceOption = $provinceOption;
		$this->regionOption = $regionOption;
	}

	public function getCities(Request $request){

		$data = $request->all();

		$response = $this->cityOption;
		if(isset($data['province_option_id']) && $data['province_option_id']){
			$response = $response->where('province_option_id',$data['province_option_id']);
		}
		$response = $response->whereStatusOptionId(Status::ACTIVE)->get();
		return $this->responseSuccess($response, 200);
	}

	public function getCountries(Request $request){

		$response = $this->countryOption->whereStatusOptionId(Status::ACTIVE)->get();

		return $this->responseSuccess($response, 200);
	}
	public function getProvinces(Request $request){
		$data = $request->all();

		$response = $this->provinceOption;
        if(isset($data['region_option_id']) && $data['region_option_id']){
            $response = $response->where('region_option_id',$data['region_option_id']);
        }
		$response = $response->whereStatusOptionId(Status::ACTIVE)->get();


		return $this->responseSuccess($response, 200);
	}
    public function getRegions(Request $request){
        $data = $request->all();

        $response = $this->regionOption;
        if(isset($data['country_option_id']) && $data['country_option_id']){

            $response = $response->where('country_option_id',$data['country_option_id']);
        }
        $response = $response->whereStatusOptionId(Status::ACTIVE)->get();


        return $this->responseSuccess($response, 200);
    }
}

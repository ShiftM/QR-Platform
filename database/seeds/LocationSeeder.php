<?php

use App\CityOption;
use App\CountryOption;
use App\ProvinceOption;
use App\RegionOption;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

	    CountryOption::truncate();
	    CountryOption::insert([

		    [
			    "name" => "Philippines",
			    "slug" => "PH",
			    "status_option_id" => 1
		    ]
	    ]);


	    ProvinceOption::truncate();
	    ProvinceOption::insert([

		    [
			    "name" => "NCR",
			    "slug" => "ncr",
			    "country_option_id" => 1,
			    "status_option_id" => 1,
			    "region_option_id" => 1,
		    ]
	    ]);

	    CityOption::truncate();
	    CityOption::insert([

		    [
			    "name" => "Pasig",
			    "slug" => "pasigc",
			    "status_option_id" => 1,
			    "country_option_id" => 1,
			    "province_option_id" => 1,
			    "region_option_id" => 1,
		    ]
	    ]);

	    RegionOption::truncate();
	    RegionOption::insert([

		    [
			    "name" => "NCR",
			    "slug" => "NCR",
			    "status_option_id" => 1,
			    "country_option_id" => 1,
		    ]
	    ]);


    }
}

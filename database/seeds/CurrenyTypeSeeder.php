<?php

use App\CurrencyType;
use Illuminate\Database\Seeder;

class CurrenyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

	    CurrencyType::truncate();
	    CurrencyType::insert([
		    ["name" => "Gem", "slug" => "Gem"],
		    ["name" => "Qoin", "slug" => "qoin"],
	    ]);
    }
}

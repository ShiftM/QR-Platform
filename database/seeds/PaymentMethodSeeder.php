<?php

use App\PaymentMethod;
use Illuminate\Database\Seeder;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        PaymentMethod::truncate();
	    PaymentMethod::insert([
            ["name" => "OctoWallet"],
		    ["name" => "Paypal"],
        ]);
    }
}

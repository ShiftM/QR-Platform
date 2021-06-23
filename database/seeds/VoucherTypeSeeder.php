<?php

use App\VoucherType;
use Illuminate\Database\Seeder;

class VoucherTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        VoucherType::truncate();
        VoucherType::insert([

            [
                "name" => "Fix",
                "slug" => "fix"
            ],
            [
                "name" => "Percentage",
                "slug" => "percentage"
            ]
        ]);
    }
}

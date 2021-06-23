<?php

use App\StatusOption;
use Illuminate\Database\Seeder;

class StatusOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        StatusOption::truncate();
        StatusOption::insert([
            ["name" => "Active","slug" => "active"],
            ["name" => "Archive","slug" => "archive"],
            ["name" => "Pending","slug" => "pending"],
            ["name" => "Processing","slug" => "processing"],
            ["name" => "Shipped to customer","slug" => "shipped-to-customer"],
            ["name" => "Completed","slug" => "completed"],
            ["name" => "Cancelled","slug" => "cancelled"],
            ["name" => "Refund","slug" => "refund"],
            ["name" => "Paid","slug" => "paid"],
        ]);
    }
}

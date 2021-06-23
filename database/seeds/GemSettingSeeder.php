<?php

use App\GemSetting;
use Illuminate\Database\Seeder;

class GemSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GemSetting::truncate();
        GemSetting::insert([
            ["coin" => 1,"gem" => 1],
        ]);
    }
}

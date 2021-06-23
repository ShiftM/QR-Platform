<?php

use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        App\Admin::truncate();
        DB::table('admins')->insert([
            'name' => 'Administrator',
            'email' => 'admin@questrewards.com',
            'first_name' => 'Juan',
            'last_name' => 'Cruz',
            'gender' => 'male',
            'number' => '+639752898605',
            'account_type' => 'super-admin',
            'emailVerifiedAt' => date('Y-m-d h:i:s'),
            'password' => bcrypt('questrewards2020')
        ]);
    }
}

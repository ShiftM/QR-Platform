<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class UpdateUserCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:update_code';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
	/**
	 * @var User
	 */
	private $user;

	/**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        parent::__construct();
	    $this->user = $user;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
	    foreach ($this->user->get() as $key => $value){
		    $value->code = str_shuffle($value->username.'abcdefghijklmnopqrstuvwxyz'. now()->timestamp);
		    $value->save();
	    }
    }
}

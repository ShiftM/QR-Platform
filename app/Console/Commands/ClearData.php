<?php

namespace App\Console\Commands;

use App\Booth;
use App\CartDetail;
use App\CartHeader;
use App\ClientInterest;
use App\Event;
use App\EventBooth;
use App\EventCheckIn;
use App\EventDay;
use App\EventImage;
use App\EventOrganizer;
use App\EventSchedule;
use App\EventSegment;
use App\EventSegmentExhibitor;
use App\EventSegmentImage;
use App\GemOrderHeader;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ClearData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
	protected $signature = 'data:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
	    $tables = array_map('reset', \DB::select('SHOW TABLES'));
	    unset($tables[36]);
	    unset($tables[1]);
//	    $el = [0,1,6,7,9,10,11,22,28,29,32,33,34,35,36,50,51,54,56,57,64,31];
	    $el = [0,1,6,7,9,10,11,36,50,51,54,56,57,64,31];
	    foreach ($tables as  $v){
	    	unset($tables[$v]);
	    }
	    foreach ($tables as $key => $v){
		    DB::table($v)->truncate();
//		    print_r($v);
	    }
	    echo "SUCCESS: Data Cleared!";
    }
}

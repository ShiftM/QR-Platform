<?php

namespace App\Console\Commands;

use App\ItemVariant;
use Illuminate\Console\Command;

class UpdatePrimaryVariant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:update_item_variant';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';
	/**
	 * @var ItemVariant
	 */
	private $itemVariant;

	/**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(ItemVariant $itemVariant)
    {
        parent::__construct();
	    $this->itemVariant = $itemVariant;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

	    foreach ( $this->itemVariant->get() as $key => $value){
		    $value->primary = 0;
		    $value->save();
	    }

	    foreach ( $this->itemVariant->groupBy('item_id')->get() as $key => $value){
		    $value->primary = 1;
		    $value->save();
	    }
	    echo "SUCCESS: Variant Updated!";
    }
}

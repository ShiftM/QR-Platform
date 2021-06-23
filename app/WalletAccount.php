<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WalletAccount extends Model
{
    //

	protected $fillable = ['table_type', 'table_id', "account_identifier", "account_number"];

    public function user() {
        return $this->belongsTo('App\User', 'table_id', 'id');
    }

    public function booth() {
        return $this->belongsTo('App\Booth', 'table_id', 'id');
    }

    public function event() {
        return $this->belongsTo('App\Event', 'table_id', 'id');
    }

	public function table()
	{
		return $this->morphTo();
	}

}

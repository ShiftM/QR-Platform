<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserTransactionHistory extends Model
{
    protected $fillable = ['userId', 'questId', 'eventId', 'redeemedDate', 'itemName','action', 'point'];

    public function quest() {
        return $this->belongsTo('App\Quest', 'questId', 'id')->with('booth');
    }

    public function event() {
        return $this->belongsTo('App\Event', 'eventId', 'id');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventCheckIn extends Model
{
    //
    protected $fillable = ["event_id",'date', 'time',"user_id"];
}

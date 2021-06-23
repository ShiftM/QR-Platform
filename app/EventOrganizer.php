<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventOrganizer extends Model
{
    //
    protected $fillable = ['eventId', 'name', 'imageUrl'];

    protected $hidden = ['updated_at', 'created_at'];
}

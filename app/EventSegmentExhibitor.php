<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventSegmentExhibitor extends Model
{
    protected $fillable = ['eventId', 'eventSegmentId','description'];

    protected $hidden = ['updated_at', 'created_at'];
}

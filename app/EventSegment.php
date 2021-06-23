<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventSegment extends Model
{
    protected $fillable = ['eventId', 'title'];

    protected $hidden = ['eventId', 'deleted_at', 'updated_at'];

    public function locationImages(){
        return $this->hasMany('App\EventSegmentImage', 'eventSegmentId', 'id');
    }
    public function segmentExhibitors(){
        return $this->hasMany('App\EventSegmentExhibitor', 'eventSegmentId', 'id');
    }

}

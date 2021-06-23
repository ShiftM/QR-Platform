<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventDay extends Model
{
    protected $fillable = [
        'date', 'eventId'
    ];

    protected $hidden = ['updated_at'];
    protected $appends = ["date","createdAt"];


    public function schedules(){
        return $this->hasMany('App\EventSchedule', 'eventDayId', 'id');
    }

    public function getDateAttribute() {
        $dt = new \DateTime($this->attributes['date']);
//        $dt->setTimezone(new \DateTimeZone('UTC'));
        return $dt->format('Y-m-d\TH:i:s.v\Z');
    }

    public function getCreatedAtAttribute() {
        $dt = new \DateTime($this->attributes['created_at']);
//        $dt->setTimezone(new \DateTimeZone('UTC'));
        return $dt->format('Y-m-d\TH:i:s.v\Z');
    }
}

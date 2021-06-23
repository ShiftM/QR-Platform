<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventSchedule extends Model
{
    protected $fillable = ['eventDayId','title', 'time'];

    protected $hidden = ['id', 'eventId', 'deleted_at', 'updated_at'];

    protected $appends = ["time","createdAt"];


    public function getTimeAttribute() {
        $dt = new \DateTime($this->attributes['time']);
//        $dt->setTimezone(new \DateTimeZone('UTC'));
        return $dt->format('Y-m-d\TH:i:s.v\Z');
    }

    public function getCreatedAtAttribute() {
        $dt = new \DateTime($this->attributes['created_at']);
//        $dt->setTimezone(new \DateTimeZone('UTC'));
        return $dt->format('Y-m-d\TH:i:s.v\Z');
    }
}

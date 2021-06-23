<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'title',
        'startDate',
        'endDate',
        'startTime',
        'endTime',
        'location',
        'city',
        'country',
        'description',
        'link',
        'admissionFee',
        'code'
    ];

    protected $hidden = ['updated_at'];

    protected $appends = ["startDate", "endDate", "startTime", "endTime", "createdAt"];


    public static function boot() {
        parent::boot();
        // registering a callback to be executed upon the creation of an activity AR
        static::creating(function ($self) {
            $self->code = str_shuffle('abcdefghijklmnopqrstuvwxyz'. now()->timestamp);
        });
    }
    public function getStartDateAttribute() {
        $dt = new \DateTime($this->attributes['startDate']);
//        $dt->setTimezone(new \DateTimeZone('UTC'));
        return $dt->format('Y-m-d\TH:i:s.v\Z');
    }

    public function getEndDateAttribute() {
        $dt = new \DateTime($this->attributes['endDate']);
//        $dt->setTimezone(new \DateTimeZone('UTC'));
        return $dt->format('Y-m-d\TH:i:s.v\Z');
    }

    public function getStartTimeAttribute() {
        $dt = new \DateTime($this->attributes['startTime']);
//        $dt->setTimezone(new \DateTimeZone('UTC'));
        return $dt->format('Y-m-d\TH:i:s.v\Z');
    }

    public function getEndTimeAttribute() {
        $dt = new \DateTime($this->attributes['endTime']);
//        $dt->setTimezone(new \DateTimeZone('UTC'));
        return $dt->format('Y-m-d\TH:i:s.v\Z');
    }

    public function getCreatedAtAttribute() {
        $dt = new \DateTime($this->attributes['created_at']);
//        $dt->setTimezone(new \DateTimeZone('UTC'));
        return $dt->format('Y-m-d\TH:i:s.v\Z');
    }

    public function eventImages()
    {
        return $this->hasMany('App\EventImage', 'eventId', 'id');

    }

    public function segments()
    {
        return $this->hasMany('App\EventSegment', 'eventId', 'id')->with(['segmentExhibitors', 'locationImages']);
    }

    public function schedules()
    {
        return $this->hasMany('App\EventDay', 'eventId', 'id');
    }

    public function quests()
    {
        return $this->hasMany('App\Quest', 'eventId', 'id');
    }

    public function eventDays()
    {
        return $this->hasMany('App\EventDay', 'eventId', 'id')->with('schedules');
    }

    public function organizer()
    {
        return $this->hasOne('App\EventOrganizer', 'eventId', 'id');
    }

    public function deleteRelatedData() {
        $this->eventDays()->delete();
        $this->schedules()->delete();
        $this->segments()->delete();
        $this->eventImages()->delete();
        $this->organizer()->delete();
    }


}

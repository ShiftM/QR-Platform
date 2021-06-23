<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventBooth extends Model
{
    protected $fillable = ['eventId', 'boothId'];

    protected $hidden = ['deleted_at', 'updated_at', 'created_at'];

    public function event(){
        return $this->hasOne('App\Event', 'id', 'eventId');
    }

    public function booth(){
        return $this->hasOne('App\Booth', 'id', 'boothId');
    }
}

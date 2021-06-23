<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserBookmark extends Model
{
    protected $fillable = ['eventId', 'userId'];

    protected $hidden = ['userId', 'updated_at'];

    public function events(){
        return $this->hasOne(Event::class, 'id', 'eventId');
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventCompany extends Model
{
    protected $fillable = ['eventId', 'companyId'];

    protected $hidden = ['deleted_at', 'updated_at', 'created_at'];

    public function event(){
        return $this->hasOne('App\Event', 'id', 'eventId');
    }

    public function company(){
        return $this->hasOne('App\Company', 'id', 'companyId');
    }
}

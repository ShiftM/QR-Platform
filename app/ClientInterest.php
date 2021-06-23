<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ClientInterest extends Model
{
    //
    protected $fillable = ['table_type','table_id','user_id'];

    public function table()
    {
        return $this->morphTo();
    }

    public function events(){
        return $this->hasOne('App\Event', 'id', 'table_id');
    }


}

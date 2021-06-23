<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InterestOption extends Model
{
    protected $fillable = ['name', 'status_option_id'];

    public function hasManyUserInterest(){
        return $this->hasMany('App\UserInterest');
    }

    public function delete()
    {
        $this->update(["status_option_id" => 2]);
    }
}

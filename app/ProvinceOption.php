<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProvinceOption extends Model
{
    //

	protected $fillable = ['status_option_id', 'name', 'slug','region_option_id','country_option_id'];

    public function statusOption()
    {
        return $this->belongsTo('App\StatusOption');
    }

    public function regionOption()
    {
        return $this->belongsTo('App\RegionOption');
    }

    public function countryOption()
    {
        return $this->belongsTo('App\CountryOption');
    }

    public function scopeWithRelatedModels($query)
    {
        return $query->with(['regionOption', 'countryOption', 'statusOption']);
    }

    public function delete()
    {
        $this->update(["status_option_id" => 2]);
    }
}

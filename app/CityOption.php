<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CityOption extends Model
{
    protected $fillable = ['status_option_id', 'name', 'slug','country_option_id','province_option_id','region_option_id'];

    public function statusOption()
    {
        return $this->belongsTo('App\StatusOption');
    }

    public function countryOption()
    {
        return $this->belongsTo('App\CountryOption');
    }

    public function regionOption()
    {
        return $this->belongsTo('App\RegionOption');
    }

    public function provinceOption()
    {
        return $this->belongsTo('App\ProvinceOption');
    }

    public function scopeWithRelatedModels($query)
    {
        return $query->with(['regionOption', 'provinceOption', 'countryOption', 'statusOption']);
    }

    public static function boot()
    {
        parent::boot();
        // registering a callback to be executed upon the creation of an activity AR
        static::creating(function ($self) {
            $self->slug = Str::slug($self->name . now()->timestamp);
        });
    }

    public function delete()
    {
        $this->update(["status_option_id" => 2]);
    }
}

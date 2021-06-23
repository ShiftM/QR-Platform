<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class RegionOption extends Model
{
    protected $fillable = ['status_option_id', 'name', 'slug','country_option_id'];

    public function statusOption()
    {
        return $this->belongsTo('App\StatusOption');
    }

    public function countryOption()
    {
        return $this->belongsTo('App\CountryOption');
    }

    public function scopeWithRelatedModels($query)
    {
        return $query->with(['countryOption', 'statusOption']);
    }

    public function delete()
    {
        $this->update(["status_option_id" => 2]);
    }

    public static function boot()
    {
        parent::boot();
        // registering a callback to be executed upon the creation of an activity AR
        static::creating(function ($self) {
            $self->slug = Str::slug($self->name . now()->timestamp);
        });
    }
}

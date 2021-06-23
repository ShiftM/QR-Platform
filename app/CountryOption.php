<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CountryOption extends Model
{
    protected $fillable = ['status_option_id', 'name', 'slug'];

    public function statusOption()
    {
        return $this->belongsTo('App\StatusOption');
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

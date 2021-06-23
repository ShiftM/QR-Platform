<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ColorOption extends Model
{
    protected $fillable = ['status_option_id', 'name', 'slug', 'hex'];

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
        // registering a callback to be executed upon the updating of an activity AR
        static::updating(function ($self) {
            $self->slug = Str::slug($self->name . now()->timestamp);
        });
    }

    public function delete()
    {
        $this->update(["status_option_id" => 2]);
    }
}

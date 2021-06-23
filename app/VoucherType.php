<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class VoucherType extends Model
{
    protected $fillable = ['name', 'slug'];

    public static function boot()
    {
        parent::boot();
        // registering a callback to be executed upon the creation of an activity AR
        static::creating(function($self) {
            $self->slug = Str::slug($self->name.now()->timestamp);
        });
    }
}

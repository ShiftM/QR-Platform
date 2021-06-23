<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class EventImage extends Model
{
    protected $fillable = ['eventId', 'path', 'fileName'];

    protected $hidden = ['id', 'eventId', 'deleted_at', 'updated_at', 'created_at'];

}

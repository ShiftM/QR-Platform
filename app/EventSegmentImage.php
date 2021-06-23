<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EventSegmentImage extends Model
{
    protected $fillable = ['eventSegmentId', 'path', 'fileName'];

    protected $hidden = ['id', 'eventSegmentId', 'deleted_at', 'updated_at', 'created_at'];
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GemOrderStatusHistory extends Model
{
    protected $fillable = ['gem_order_header_id', 'status_option_id'];

    public function statusOption()
    {
        return $this->belongsTo('App\StatusOption');
    }

    public function gemOrderHeader()
    {
        return $this->belongsTo('App\GemOrderHeader');
    }

    public function scopeWithRelatedModels($query)
    {
        return $query->with(['gemOrderHeader', 'statusOption']);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
    protected $fillable = ['order_header_id', 'status_option_id'];

    public function orderHeader()
    {
        return $this->belongsTo('App\OrderHeader');
    }

    public function statusOption()
    {
        return $this->belongsTo('App\StatusOption');
    }

    public function scopeWithRelatedModels($query)
    {
        return $query->with(['orderHeader', 'statusOption']);
    }
}

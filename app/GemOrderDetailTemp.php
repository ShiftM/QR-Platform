<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class GemOrderDetailTemp extends Model
{
    protected $fillable = ['gem_package_name', 'gem_package_amount', 'gem_package_price', 'gem_package_id', 'gem_order_header_temp_id'];

    public function gemPackage()
    {
        return $this->belongsTo('App\GemPackage');
    }

    public function gemOrderHeaderTemp()
    {
        return $this->belongsTo('App\GemOrderHeaderTemp');
    }
}

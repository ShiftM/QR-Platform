<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ItemCategory extends Model
{
    protected $fillable = ['item_id', 'category_header_id','status_option_id'];

    public function categoryHeader()
    {
       return $this->belongsTo('App\CategoryHeader');
    }

}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OrderRecipient extends Model
{
    protected $fillable = ['order_header_id', 'full_name','phone_number', 'tel_number','email_address','table_type','table_id'];

    public function table() {
        return $this->morphTo();
    }

    public function orderHeader()
    {
        $this->belongsTo('App\OrderHeader');
    }
}

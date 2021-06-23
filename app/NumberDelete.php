<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NumberDelete extends Model {
    //

    protected $fillable = ['table_type', 'table_id', 'table_from', 'user_id'];

    public function table() {
        return $this->morphTo();
    }

    public function storeAsCartItem($id){
        $user = auth('user')->user();
        $inputs  = [
            "table_type" => 'item_stocks',
            "table_id" => $id,
            "user_id" => $user && $user->id ? $user->id : 0,
            "table_from"=>"cart_details"
        ];
        parent::create($inputs);
    }
}

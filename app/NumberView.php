<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NumberView extends Model
{
    //
    protected $fillable = ['table_type','table_id','user_id'];

    public function table() {
        return $this->morphTo();
    }

    public function storeAsItem($id){
        $user = auth('user')->user();
        $inputs  = [
            "table_type" => 'items',
            "table_id" => $id,
            "user_id" => $user && $user->id ? $user->id : 0
        ];
        parent::create($inputs);
    }

    public function storeAsQuests($id){
        $user = auth('user')->user();
        $inputs  = [
            "table_type" => 'quests',
            "table_id" => $id,
            "user_id" => $user && $user->id ? $user->id : 0
        ];
        parent::create($inputs);
    }

    public function storeAsCategory($id){
        $user = auth('user')->user();
        $inputs  = [
            "table_type" => 'category_headers',
            "table_id" => $id,
            "user_id" => $user && $user->id ? $user->id : 0
        ];
        parent::create($inputs);
    }
}

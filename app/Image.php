<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    protected $fillable = ['table_type', 'table_id', 'path', 'file_name','full_path','primary'];

    public function table()
    {
        return $this->morphTo();
    }




}

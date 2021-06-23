<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class VoucherUsage extends Model
{
    protected $fillable = ['table_type', 'table_id', 'voucher_id'];

    public function table()
    {
        return $this->morphTo();
    }
}

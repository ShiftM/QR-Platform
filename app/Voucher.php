<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    protected $fillable = ['status_option_id', 'currency_type_id', 'use_per_customer', 'minimum_amount', 'number_of_use', 'voucher_type_id', 'name', 'code', 'expiry_date', 'expiry_time'];

    public function currencyType()
    {
        return $this->belongsTo('App\CurrencyType');
    }

    public function voucherType()
    {
        return $this->belongsTo('App\VoucherType');
    }

    public function statusOption()
    {
        return $this->belongsTo('App\StatusOption');
    }

    public function hasManyVoucherUsages()
    {
        return $this->morphMany('App\VoucherUsage', 'table');
    }

    public function scopeWithRelatedModels($query)
    {
        return $query->with(['currencyType', 'voucherType', 'statusOption', 'hasManyVoucherUsages']);
    }

    public function delete()
    {
        $this->update(["status_option_id" => 2]);
    }
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    protected $fillable = ['plan_name', 'period', 'price', 'item_points_cap'];

}

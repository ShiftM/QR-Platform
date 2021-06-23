<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Relation::morphMap([
            'voucher_usages' => \App\VoucherUsage::class,
            'order_totals' => \App\OrderTotal::class,
            'item_variants' => \App\ItemVariant::class,
            'images' => \App\Image::class,
            'order_headers' => \App\OrderHeader::class,
            'gem_order_headers' => \App\GemOrderHeader::class,
            'gem_order_header_temps' => \App\GemOrderHeaderTemp::class,
            'order_header_temps' => \App\OrderHeaderTemp::class,
            'order_recipients' => \App\OrderRecipient::class,
            'items' => \App\Item::class,
            'quests' => \App\Quest::class,
            'users' => \App\User::class,
            'booths' => \App\Booth::class,
            'events' => \App\Event::class,
        ]);
        Schema::defaultStringLength(191);


    }
}

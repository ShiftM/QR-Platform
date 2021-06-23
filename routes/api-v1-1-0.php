<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['prefix' => 'company'], function ($router) {
	$router->get('/get-companies', 'CompanyController@getCompanies');
	$router->get('/get-event-companies', 'CompanyController@getEventCompanies');
	$router->get('/', 'CompanyController@index');
	$router->get('/{id}', 'CompanyController@show');
	$router->post('create', 'CompanyController@create');
	$router->put('/update/{id}', 'CompanyController@update');
	$router->delete('/{id}', 'CompanyController@delete');
	$router->get('/subscriptionplanlist/{id}', 'CompanyController@getSubscriptionPlans');



});

Route::group(['prefix' => 'test'], function ($router){
	$router->post('push-notification','TestController@pushNotification');
});


Route::group(['prefix' => 'app'], function ($router){
    $router->get('validate-version','AppUpdateController@validateVersion');
});

Route::group(['prefix' => 'payments'], function ($router){

	$router->group(['prefix' => "paypal"], function($router){
		$router->post('pay/{model}','PayPalController@createTransaction');
		$router->post('post-payment/{model}','PayPalController@postCheckOut');
		$router->get('generate-token','PayPalController@generateToken');

		$router->group(['prefix' => "payment"], function($router){
			$router->get('success','PayPalController@paymentSuccess');
			$router->get('cancelled','PayPalController@paymentFailed');
		});
	});
});


Route::group(['prefix' => 'gems'], function ($router){


	$router->group(['prefix' => "checkout"], function($router){
		$router->post('post-order','GemCheckoutController@postOrder');
		$router->get('get-order','GemCheckoutController@getOrder');
	});

	$router->resources([
		'orders'          => 'GemOrderController',
		'packages'          => 'GemPackageController'
	]);
});

Route::group(['prefix' => "e-commerce"], function($router){

    $router->group(['prefix' => "items"], function($router){
        $router->get('suggestions','ItemController@suggestions');
        $router->get('/stocks','ItemController@stocks');
    });

    $router->resources([
        'items'          => 'ItemController',
	    'categories' => 'CategoryController'
    ]);

	$router->group(['prefix' => "services"], function($router){
		$router->get('get-cities','LocationController@getCities');
		$router->get('get-countries','LocationController@getCountries');
		$router->get('get-provinces','LocationController@getProvinces');
		$router->get('get-regions','LocationController@getRegions');
	});


});

Route::group(['prefix' => 'analytics', 'middleware' => ['auth:user']], function ($router){

	$router->resources([
		'number-of-views'          => 'NumberViewController',
	]);
});

Route::get("send-email", 'EmailController@sendEmail');


Route::group(['prefix' => 'user'], function ($router) {
	$router->post('register', 'UserAuthController@register');
	$router->get('check-username', 'UserAuthController@checkUsernameExist');
	$router->post('update-profile', 'UserAuthController@updateProfile');
	$router->post('send-otp', 'UserAuthController@requestOtp');
	$router->post('send-registration-otp', 'UserAuthController@requestRegistrationOtp');
	$router->post('login', 'UserAuthController@login');


	$router->post('test-send-otp', 'TestOtpController@requestOtp');
	$router->post('test-send-registration-otp', 'TestOtpController@requestRegistrationOtp');

	$router->group(['prefix' => 'wallet'], function ($router) {
		$router->get('balance', 'WalletController@balanceInquiry');
		$router->get('rate', 'WalletController@rate');
		$router->post('exchange', 'WalletController@exchange');
	});

	$router->group(['prefix' => 'events'], function ($router) {
		$router->post('get-events', 'EventController@getEvents');
		$router->post('get-event-info', 'EventController@getEventInfo');
		$router->post('search', 'EventController@search');
        $router->post('quest-count', 'EventController@addQuestCount');
        $router->resource('check-ins','CheckInController');
	});

	$router->resources([
		'client-interests'          => 'ClientInterestController',
	]);

	$router->middleware('jwt.auth')->group(function ($router) {


		$router->group(['prefix' => "e-commerce"], function($router){
			$router->group(['prefix' => "carts"], function($router){
			    $router->get('get-item-count-total','CartController@getItemCountAndTotal');
			});
			$router->resources([
				'carts'          => 'CartController',
				'orders'          => 'OrderController'
			]);
            $router->get('order-lists','OrderController@orderLists');

			$router->group(['prefix' => "checkout"], function($router){
				$router->post('post-order','CheckoutController@postOrder');
				$router->get('get-order','CheckoutController@getOrder');
				$router->post('place-order','CheckoutController@placeOrder');
			});

		});

		$router->post('change-phone-number', 'UserAuthController@updatePhoneNumber');
		$router->get('profile', 'UserController@profile');
		$router->post('upload-photo', 'UserController@uploadPhoto');
		$router->post('logout', 'UserAuthController@logout');
		$router->get('get-transaction-history', 'UserController@getTransactionHistory');
		$router->post('change-phone-number-otp', 'UserAuthController@changePhoneNumberOtp');
		$router->post('test-change-phone-number-otp', 'TestOtpController@changePhoneNumberOtp');
		$router->post('update-device-token', 'UserAuthController@updateDeviceToken');

        $router->resources([
//            'client-interests'          => 'ClientInterestController',
            'user-interests'          => 'UserInterestController'
        ]);
		$router->group(['prefix' => 'events'], function ($router) {
			// $router->post('get-events', 'EventController@getEvents');
			// $router->post('get-event-info', 'EventController@getEventInfo');
			$router->post('toggle-bookmark', 'EventController@bookEvent');
			$router->get('get-bookmarks', 'EventController@bookMarks');
		});

        $router->group(['prefix' => "e-commerce"], function ($router) {
            $router->resources([
                'wish-lists'          => 'WishListController',
                'user-address'          => 'UserAddressController',
            ]);
        });
	});
});

Route::group(['prefix' => 'guest'], function ($router) {
	$router->group(['prefix' => 'events'], function ($router) {
		$router->post('get-events', 'EventController@getEvents');
		$router->post('get-event-info', 'EventController@getEventInfo');
		$router->post('search', 'EventController@search');
	});
});

Route::group(['prefix' => 'merchant', 'namespace' => 'Merchant'], function ($router) {
	$router->post('login', 'MerchantAuthController@login');

	$router->middleware('auth:merchant')->group(function ($router) {
		$router->get('profile', 'MerchantAuthController@profile');
		$router->post('logout', 'MerchantAuthController@logout');

		$router->group(['prefix' => 'events'], function ($router) {
			$router->get('get-events', 'EventController@getEvents');
		});

		$router->group(['prefix' => 'quests'], function ($router) {
			$router->get('get-quests', 'EventController@getQuests');
			$router->post('give-points', 'EventController@givePoints');
		});

	});

});

Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function ($router) {
	$router->post('login', 'AdminAuthController@login');
	// $router->get('profile', 'AdminAuthController@profile');

	$router->middleware('bindings')->group(function ($router) {
		$router->get('profile', 'AdminAuthController@profile');
		$router->post('logout', 'AdminAuthController@logout');


		// ---------------------------- ADDED ---------------------------- //
		$router->group(['prefix' => 'users'], function ($router) {
			$router->get('/', 'AdminController@index');
			$router->get('/{id}', 'AdminController@show');
			$router->post('create', 'AdminController@create');
			$router->put('update/{id}', 'AdminController@update');
			$router->delete('delete/{id}', 'AdminController@delete');
		});
		$router->group(['prefix' => 'subscription-plan'], function ($router) {
			$router->get('/getplans', 'SubscriptionController@getPlans');
			$router->get('/', 'SubscriptionController@index');
			$router->get('/{id}', 'SubscriptionController@show');
			$router->post('create', 'SubscriptionController@create');
			$router->put('/update/{id}', 'SubscriptionController@update');
			$router->delete('/delete/{id}', 'SubscriptionController@delete');
		});
		// -------------------------- END ADDED -------------------------- //

		$router->group(['prefix' => 'events'], function ($router) {
			$router->get('index', 'EventController@index');
			$router->get('edit/{eid}', 'EventController@edit');
			$router->post('store', 'EventController@store');
			$router->put('update/{uid}', 'EventController@update');
			$router->delete('delete/{did}', 'EventController@delete');
			$router->get('get-booths', 'EventController@getBooths');
			$router->get('info/{id}', 'EventController@info');
			$router->get('get-events', 'EventController@getEventsApp');
			$router->post('update-status/{id}', 'EventController@updateStatus');

		});

		$router->group(['prefix' => 'event-booth'], function ($router) {
			$router->get('index', 'EventBoothController@index');
			$router->get('edit/{eid}', 'EventBoothController@edit');
			$router->post('store', 'EventBoothController@store');
			$router->put('update/{uid}', 'EventBoothController@update');
			$router->post('delete', 'EventBoothController@delete');
			$router->get('get-events-booths/{id}', 'EventBoothController@getEventBooth');
		});

		$router->group(['prefix' => 'event-company'], function ($router) {
			$router->get('index', 'EventCompanyController@index');
			$router->get('edit/{eid}', 'EventCompanyController@edit');
			$router->post('store', 'EventCompanyController@store');
			$router->put('update/{uid}', 'EventCompanyController@update');
			$router->post('delete', 'EventCompanyController@delete');
			$router->get('get-events-companies/{id}', 'EventCompanyController@getEventCompany');
		});

		$router->group(['prefix' => 'event-schedule'], function ($router) {
			$router->get('index', 'EventSchedulesController@index');
			$router->get('edit/{eid}', 'EventSchedulesController@edit');
			$router->post('store', 'EventSchedulesController@store');
			$router->put('update/{uid}', 'EventSchedulesController@update');
			$router->delete('delete/{did}', 'EventSchedulesController@delete');
		});

		$router->group(['prefix' => 'event-segment'], function ($router) {
			$router->get('index', 'EventSegmentController@index');
			$router->get('edit/{eid}', 'EventSegmentController@edit');
			$router->post('store', 'EventSegmentController@store');
			$router->put('update/{uid}', 'EventSegmentController@update');
			$router->delete('delete/{did}', 'EventSegmentController@delete');
		});

		$router->group(['prefix' => 'quest'], function ($router) {
			$router->get('index', 'QuestController@index');
			$router->get('edit/{eid}', 'QuestController@edit');
			$router->post('store', 'QuestController@store');
			$router->put('update/{uid}', 'QuestController@update');
			$router->delete('delete/{did}', 'QuestController@delete');
			$router->post('redeem-points', 'QuestController@redeemPoints');

		});

		$router->group(['prefix' => 'booth'], function ($router) {
			$router->get('index', 'BoothController@index');
			$router->get('edit/{eid}', 'BoothController@edit');
			$router->post('store', 'BoothController@store');
			$router->put('update/{uid}', 'BoothController@update');
			$router->post('update-status/{id}', 'BoothController@updateStatus');
			$router->delete('delete/{did}', 'BoothController@delete');
			$router->put('forgot-password/{id}', 'BoothController@forgotPassword');
		});

		$router->group(['prefix' => 'user'], function ($router) {
			$router->get('get-users', 'UserController@index');
			$router->get('get-user-details/{id}', 'UserController@edit');
			$router->put('update-user-details/{id}', 'UserController@update');
			$router->post('update-user-status/{id}', 'UserController@updateStatus');

		});

		$router->group(['prefix' => 'upload'], function ($router) {
			$router->post('single/{model}', 'UploadController@singleUpload');
			$router->post('multiple/{model}', 'UploadController@multipleUploads');
		});


		$router->group(['prefix' => "images"], function ($router) {
			$router->delete('delete-by-parent/{id}/{table}', 'ImageController@destroyByParent');
		});

		$router->resources([
			'images' => 'ImageController',
		]);

        $router->group(['prefix' => "app"], function ($router) {
            $router->resources([
                'updates' => 'AppUpdateController',
            ]);

        });
        $router->group(['prefix' => "search"], function ($router) {
            $router->get('event-info', 'SearchAutofillController@eventInfo');
            $router->get('item-info', 'SearchAutofillController@salesByCategoryAndItem');
            $router->get('customer-info', 'SearchAutofillController@salesByCustomerName');
            $router->get('order-info', 'SearchAutofillController@orderInfo');
	        $router->get('gem-order-info', 'SearchAutofillController@gemOrderInfo');
            $router->get('wallet-account-info', 'SearchAutofillController@walletAccountInfo');
        });
		$router->group(['prefix' => "e-commerce"], function ($router) {
			$router->group(['prefix' => "items"], function ($router) {
				$router->delete('force-delete/{id}', 'ItemController@forceDestroy');
			});

			$router->group(['prefix' => "reports"], function ($router) {
				$router->get('most-sold-by-price', 'ReportController@mostSoldByPrice');
				$router->get('most-sold-by-quantity', 'ReportController@mostSoldByQuantity');
			});

			$router->resources([
				'samples'          => 'SampleController',
				'items'            => 'ItemController',
				'item-stocks'      => 'ItemStockController',
				'size-options'     => 'SizeOptionController',
				'item-categories'  => 'ItemCategoryController',
				'color-options'    => 'ColorOptionController',
				'category-headers' => 'CategoryHeaderController',
				'item-variants'    => 'ItemVariantController',
				'featured-items' => 'FeaturedItemController',
				'vouchers' => 'VoucherController',
				'country-option' => 'CountryOptionController',
			    'push-notifications' => 'PushNotificationController',
			    'city-option' => 'CityOptionController',
                'province-option' => 'ProvinceOptionController',
                'region-option' => 'RegionOptionController',
			    'orders' => 'OrderController',

			    'order-status-history' => 'OrderStatusHistoryController'
			]);
		});
        $router->group(['prefix' => "wallet"], function ($router) {
            $router->resources([
                'gem-setting' => 'GemSettingController',
                'gem-packages' => 'GemPackageController',
                'gem-order-status-history' => 'GemOrderStatusHistoryController',
                'wallet-accounts' => 'WalletAccountController',
                'orders' => 'GemOrderController',
            ]);
            $router->post('transfer-fund', 'WalletAccountController@fundTransfer');
            $router->get('wallet-accounts/balance-inquiry/{id}', 'WalletAccountController@balanceInquiry');
            $router->get('transaction-history', 'WalletAccountController@transactionHistory');
            $router->get('exchange-rate', 'WalletAccountController@exchangeRate');
        });
        $router->group(['prefix' => "analytics"], function ($router) {
            $router->resources([
                'interest-options' => 'InterestOptionController',
            ]);
        });
	});

});

// -------------------  ANALYTICS ---------------------- //
Route::group(['prefix' => 'quests'], function ($router) {
	$router->get('/most-viewed', 'QuestController@mostViewed');
	$router->get('/number-of-finished', 'QuestController@numberOfFinished');
	$router->resources([
		'/quest-view'          => 'NumberViewController',
		'/quest-started'          => 'NumberViewController',
		'/quest-finished'          => 'NumberViewController',
	]);
	$router->get('/quest-analytics/view', 'NumberViewController@getViewed');
	$router->get('/quest-analytics/start', 'NumberViewController@getStarted');
	$router->get('/quest-analytics/finish', 'NumberViewController@getFinished');


	$router->get('/get-user-quests/{id}', 'UserProgressController@getUserQuest');
	$router->post('/start-quest-status', 'UserProgressController@startQuest');
	$router->put('/update-quest-status/{id}', 'UserProgressController@updateQuest');

});

Route::group(['prefix' => 'user'], function ($router) {
	$router->get('/user-lists', 'UserController@userLists');
	$router->get('/user-by-location', 'UserController@userByLocation');
	$router->get('/user-by-quest', 'UserController@userByQuest');
	$router->get('/user-by-gender', 'UserController@userByGender');
	
	// $router->post('log', 'QuestAnalyticsController@create');
	// $router->get('log/{id}', 'QuestAnalyticsController@show');
});
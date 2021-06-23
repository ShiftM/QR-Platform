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

Route::group(['prefix' => 'user'], function ($router){
    $router->post('register', 'UserAuthController@register');
    $router->post('send-otp', 'UserAuthController@requestOtp');
    $router->post('send-registration-otp', 'UserAuthController@requestRegistrationOtp');
    $router->post('login', 'UserAuthController@login');


    $router->post('test-send-otp', 'TestOtpController@requestOtp');
    $router->post('test-send-registration-otp', 'TestOtpController@requestRegistrationOtp');

    $router->group(['prefix' => 'events'], function($router){
    $router->post('get-events', 'EventController@getEvents');
    $router->post('get-event-info', 'EventController@getEventInfo');
    $router->post('search', 'EventController@search');
    });

    $router-> middleware('jwt.auth')->group(function ($router){
        $router->post('change-phone-number', 'UserAuthController@updatePhoneNumber');
        $router->get('profile', 'UserController@profile');
        $router->post('upload-photo', 'UserController@uploadPhoto');
        $router->post('logout', 'UserAuthController@logout');
        $router->get('get-transaction-history', 'UserController@getTransactionHistory');
        $router->post('change-phone-number-otp', 'UserAuthController@changePhoneNumberOtp');
        $router->post('test-change-phone-number-otp', 'TestOtpController@changePhoneNumberOtp');
        $router->post('update-device-token', 'UserAuthController@updateDeviceToken');

        $router->group(['prefix' => 'events'], function($router){
            // $router->post('get-events', 'EventController@getEvents');
            // $router->post('get-event-info', 'EventController@getEventInfo');
            $router->post('toggle-bookmark', 'EventController@bookEvent');
            $router->post('quest-count', 'EventController@addQuestCount');
            $router->get('get-bookmarks','EventController@bookMarks');
        });
    });
});

Route::group(['prefix' => 'guest'], function($router){
    $router->group(['prefix' => 'events'], function($router){
        $router->post('get-events', 'EventController@getEvents');
        $router->post('get-event-info', 'EventController@getEventInfo');
        $router->post('search', 'EventController@search');
    });
});

Route::group(['prefix' => 'merchant', 'namespace' => 'Merchant'], function ($router){
    $router->post('login', 'MerchantAuthController@login');

    $router-> middleware('auth:merchant')->group(function ($router){
        $router->get('profile', 'MerchantAuthController@profile');
        $router->post('logout', 'MerchantAuthController@logout');

        $router->group(['prefix' => 'events'], function($router){
            $router->get('get-events', 'EventController@getEvents');
        });
        $router->group(['prefix' => 'quests'], function($router){
            $router->get('get-quests', 'EventController@getQuests');
            $router->post('give-points', 'EventController@givePoints');
        });
    });
});

Route::group(['prefix' => 'admin', 'namespace' => 'Admin'], function ($router){
    $router->post('login', 'AdminAuthController@login');
    // $router->get('profile', 'AdminAuthController@profile');

    $router->middleware('auth:admin')->group(function($router){
        $router->get('profile', 'AdminAuthController@profile');
        $router->post('logout', 'AdminAuthController@logout');

        $router->group(['prefix' => 'events'], function($router){
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

        $router->group(['prefix' => 'event-booth'], function($router){
            $router->get('index', 'EventBoothController@index');
            $router->get('edit/{eid}', 'EventBoothController@edit');
            $router->post('store', 'EventBoothController@store');
            $router->put('update/{uid}', 'EventBoothController@update');
            $router->post('delete', 'EventBoothController@delete');
            $router->get('get-events-booths/{id}', 'EventBoothController@getEventBooth');

        });

        $router->group(['prefix' => 'event-schedule'], function($router){
            $router->get('index', 'EventSchedulesController@index');
            $router->get('edit/{eid}', 'EventSchedulesController@edit');
            $router->post('store', 'EventSchedulesController@store');
            $router->put('update/{uid}', 'EventSchedulesController@update');
            $router->delete('delete/{did}', 'EventSchedulesController@delete');
        });

        $router->group(['prefix' => 'event-segment'], function($router){
            $router->get('index', 'EventSegmentController@index');
            $router->get('edit/{eid}', 'EventSegmentController@edit');
            $router->post('store', 'EventSegmentController@store');
            $router->put('update/{uid}', 'EventSegmentController@update');
            $router->delete('delete/{did}', 'EventSegmentController@delete');
        });

        $router->group(['prefix' => 'quest'], function($router){
            $router->get('index', 'QuestController@index');
            $router->get('edit/{eid}', 'QuestController@edit');
            $router->post('store', 'QuestController@store');
            $router->put('update/{uid}', 'QuestController@update');
            $router->delete('delete/{did}', 'QuestController@delete');
            $router->post('redeem-points', 'QuestController@redeemPoints');

        });

        $router->group(['prefix' => 'booth'], function($router){
            $router->get('index', 'BoothController@index');
            $router->get('edit/{eid}', 'BoothController@edit');
            $router->post('store', 'BoothController@store');
            $router->put('update/{uid}', 'BoothController@update');
            $router->post('update-status/{id}', 'BoothController@updateStatus');
            $router->delete('delete/{did}', 'BoothController@delete');
            $router->put('forgot-password/{id}', 'BoothController@forgotPassword');
        });

        $router->group(['prefix' => 'user'], function($router){
            $router->get('get-users', 'UserController@index');
            $router->get('get-user-details/{id}', 'UserController@edit');
            $router->put('update-user-details/{id}', 'UserController@update');
            $router->post('update-user-status/{id}', 'UserController@updateStatus');

        });

        $router->group(['prefix' => 'upload'], function($router){
            $router->post('single/{model}', 'UploadController@singleUpload');
            $router->post('multiple/{model}', 'UploadController@multipleUploads');
        });


    });


});

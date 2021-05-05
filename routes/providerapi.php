<?php

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

Route::get('/settings' , 'ProviderAuth\TokenController@settings');

// Authentication
Route::post('/register' ,   'ProviderAuth\TokenController@register');
Route::post('/send/otp' , 'ProviderAuth\TokenController@send_otp');
Route::post('/voice/sms' , 'ProviderAuth\TokenController@voice_sms');
Route::post('/oauth/token' ,'ProviderAuth\TokenController@authenticate');
Route::post('/logout' ,     'ProviderAuth\TokenController@logout');
Route::post('/verify' ,     'ProviderAuth\TokenController@verify');
Route::post('/login' , 'ProviderAuth\TokenController@authenticate');
Route::post('/verify/otp' , 'ProviderAuth\TokenController@verify_otp');
Route::post('/auth/apple','ProviderAuth\TokenController@appleViaAPI');
Route::post('/auth/facebook','ProviderAuth\TokenController@facebookViaAPI');
Route::post('/auth/google',  'ProviderAuth\TokenController@googleViaAPI');
Route::post('/forgot/password','ProviderAuth\TokenController@forgot_password');
Route::post('/reset/password', 'ProviderAuth\TokenController@reset_password');

Route::group(['middleware' => ['provider.api']], function () {

    Route::post('/refresh/token' , 'ProviderAuth\TokenController@refresh_token');

    Route::group(['prefix' => 'profile'], function () {

        Route::get ('/' ,         'ProviderResources\ProfileController@index');
        Route::post('/' ,         'ProviderResources\ProfileController@update');
        Route::post('/password' , 'ProviderResources\ProfileController@password');
        Route::post('/location' , 'ProviderResources\ProfileController@location');
        Route::post('/available' ,'ProviderResources\ProfileController@available');

        Route::get('/documents' ,'ProviderResources\ProfileController@documents');
        Route::post('/documents' ,'ProviderResources\ProfileController@documents_post');

    });

    Route::get('/target' , 'ProviderResources\ProfileController@target');
    Route::resource('trip','ProviderResources\TripController');
    Route::post('cancel',  'ProviderResources\TripController@cancel');
    Route::post('summary', 'ProviderResources\TripController@summary');
    Route::get('help',     'ProviderResources\TripController@help_details');
   


    Route::group(['prefix' => 'trip'], function () {

        Route::post('{id}',          'ProviderResources\TripController@accept');
        Route::post('{id}/rate',     'ProviderResources\TripController@rate');
        Route::post('{id}/message' , 'ProviderResources\TripController@message');
        Route::post('{id}/calculate','ProviderResources\TripController@calculate_distance');

    });
    
    Route::post('requests/rides' , 'ProviderResources\TripController@request_rides');

    Route::group(['prefix' => 'requests'], function () {

        Route::get('/upcoming' ,       'ProviderResources\TripController@scheduled');
        Route::get('/history',         'ProviderResources\TripController@history');
        Route::get('/history/details', 'ProviderResources\TripController@history_details');
        Route::get('/upcoming/details','ProviderResources\TripController@upcoming_details');

    });
    Route::post('/test/push' ,  'ProviderResources\TripController@test');

// Geo fencing live track

    Route::post('/geo-fencing/live-track' , 'ProviderResources\TripController@geo_fencing_live_track');


    Route::post('/instant-ride' , 'ProviderResources\TripController@instant_ride');


    Route::post('/chat/push' ,'ProviderResources\ProfileController@chat_push'); 


    Route::post('/instant-ride/now' ,'ProviderResources\TripController@instant_ride_now');
    Route::get('/instant-ride/estimate' ,'ProviderResources\TripController@instant_ride_estimate');

});
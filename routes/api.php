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

Route::get('/settings' , 'UserApiController@settings');
Route::post('/verify' , 'UserApiController@verify');
Route::post('/voice/sms' , 'UserApiController@voice_sms');
Route::post('/oauth/token' , 'UserApiController@login');
Route::post('/signup' , 'UserApiController@signup');
Route::post('/send/otp' , 'UserApiController@send_otp');
Route::post('/login' , 'Auth\LoginController@apiLogin');
Route::post('/verify/otp' , 'UserApiController@verify_otp');
Route::post('/logout' , 'UserApiController@logout');

Route::post('/auth/apple', 		'Auth\SocialLoginController@appleViaAPI');
Route::post('/auth/facebook', 		'Auth\SocialLoginController@facebookViaAPI');
Route::post('/auth/google', 		'Auth\SocialLoginController@googleViaAPI');
Route::post('/forgot/password',     'UserApiController@forgot_password');
Route::post('/reset/password',      'UserApiController@reset_password');
Route::get('/polygon' , 'UserApiController@poly_check');
Route::group(['middleware' => ['auth:api']], function () {

	// user profile
	Route::post('/change/password' , 	'UserApiController@change_password');
	Route::post('/update/location' , 	'UserApiController@update_location');
	Route::get('/details' , 			'UserApiController@details');
	Route::post('/update/profile' , 	'UserApiController@update_profile');
	// services
	Route::get('/services' , 'UserApiController@services');
	// services Geo Fencing
	Route::get('/service/geo_fencing' , 'UserApiController@service_geo_fencing');
	// provider
	Route::post('/rate/provider' , 'UserApiController@rate_provider');

	// request
	Route::post('/send/request' , 	'UserApiController@send_request');
	Route::post('/cancel/request' , 'UserApiController@cancel_request');
	Route::get('/request/check' , 	'UserApiController@request_status_check');
	Route::get('/show/providers' , 	'UserApiController@show_providers');
	Route::post('/update/request' , 'UserApiController@modifiy_request');
	// history
	Route::get('/trips' , 				'UserApiController@trips');
	Route::get('upcoming/trips' , 		'UserApiController@upcoming_trips');
	Route::get('/trip/details' , 		'UserApiController@trip_details');
	Route::get('upcoming/trip/details' ,'UserApiController@upcoming_trip_details');
	// payment
	Route::post('/payment' , 	'PaymentController@payment');
	Route::post('/add/money' , 	'PaymentController@add_money');

	Route::post('/payment/rzp' , 	'PaymentController@rzp_flow');

	Route::post('/add/razor/money' , 	'PaymentController@rzp_success');



	// estimated
	Route::get('/estimated/fare' , 'UserApiController@estimated_fare');
	// help
	Route::get('/help' , 'UserApiController@help_details');
	// promocode
	Route::get('/promocodes' , 		'UserApiController@promocodes');
	Route::post('/promocode/add' , 	'UserApiController@add_promocode');
	// card payment
    Route::resource('card', 		'Resource\CardResource');
    // card payment
    Route::resource('location', 'Resource\FavouriteLocationResource');
    // passbook
	Route::get('/wallet/passbook' , 'UserApiController@wallet_passbook');
	Route::get('/promo/passbook' , 	'UserApiController@promo_passbook');

	Route::post('/test/push' , 	'UserApiController@test');


	Route::post('/chat/push' , 	'UserApiController@chat_push');

	Route::get('/package', 'UserApiController@package');


});

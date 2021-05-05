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

// Authentication

Route::post('/oauth/token' ,'FleetAuth\TokenController@authenticate');
Route::post('/logout' ,     'FleetAuth\TokenController@logout');

Route::post('/login' , 'FleetAuth\TokenController@apiLogin');

Route::post('/forgot/password','FleetAuth\TokenController@forgot_password');
Route::post('/reset/password', 'FleetAuth\TokenController@reset_password');

Route::group(['middleware' => ['fleet.api']], function () {

    Route::post('/refresh/token' , 'FleetAuth\TokenController@refresh_token');

    Route::get('/profile/detail', 'FleetAuth\TokenController@profile_detail');

    Route::post('/profile/update', 'FleetAuth\TokenController@profile_update');

    Route::post('/profile/password', 'FleetAuth\TokenController@password_update'); 


    Route::resource('provider', 'Resource\ProviderFleetResource');


    Route::group(['as' => 'provider.'], function () {
    
	    Route::get('review/provider', 'FleetController@provider_review');
	    Route::get('provider/{id}/request', 'Resource\ProviderFleetResource@request')->name('request');
	    Route::resource('provider/{provider}/document', 'Resource\ProviderFleetDocumentResource');
	    Route::delete('provider/{provider}/service/{document}', 'Resource\ProviderFleetDocumentResource@service_destroy')->name('document.service');
     });

    


});
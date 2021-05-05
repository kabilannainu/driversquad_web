<?php

Route::get('/', 'DispatcherController@index')->name('index');
Route::get('/dashboard', 'DispatcherController@dashboard')->name('dashboard');
Route::get('/heatmap', 'DispatcherController@heatmap')->name('heatmap');
Route::get('/translation',  'DispatcherController@translation')->name('translation');


Route::group(['as' => 'dispatcher.', 'prefix' => 'dispatcher'], function () {
	Route::get('/', 'DispatcherController@index')->name('index');
	Route::post('/', 'DispatcherController@store')->name('store');
	Route::get('/trips', 'DispatcherController@trips')->name('trips');
	Route::get('/trips/{trip}/{provider}', 'DispatcherController@assign')->name('assign');
	Route::get('/users', 'DispatcherController@users')->name('users');
	Route::get('/providers', 'DispatcherController@providers')->name('providers');
	Route::get('/cancelled', 'DispatcherController@cancelled')->name('cancelled');
	Route::get('/cancel', 'DispatcherController@cancel')->name('cancel');
});
Route::get('/estimated/fare', 'DispatcherController@estimated_fare');
Route::resource('service', 'Resource\ServiceResource');

Route::get('password', 'DispatcherController@password')->name('password');
Route::post('password', 'DispatcherController@password_update')->name('password.update');

Route::get('profile', 'DispatcherController@profile')->name('profile');
Route::post('profile', 'DispatcherController@profile_update')->name('profile.update');

 

Route::resource('user', 'Resource\UserResource');
Route::resource('dispatch-manager', 'Resource\DispatcherResource');
Route::resource('account-manager', 'Resource\AccountResource');
Route::resource('fleet', 'Resource\FleetResource');
Route::resource('provider', 'Resource\ProviderResource');
Route::resource('document', 'Resource\DocumentResource');

// geo fencing set city base drag location
Route::resource('geo-fencing', 'Resource\GeoFencingResource');

Route::group(['as' => 'provider.'], function () {
    Route::get('review/provider', 'DispatcherController@provider_review')->name('review');
    Route::get('provider/{id}/approve', 'Resource\ProviderResource@approve')->name('approve');
    Route::get('provider/{id}/disapprove', 'Resource\ProviderResource@disapprove')->name('disapprove');
    Route::get('provider/{id}/request', 'Resource\ProviderResource@request')->name('request');
    Route::get('provider/{id}/statement', 'Resource\ProviderResource@statement')->name('statement');
    Route::resource('provider/{provider}/document', 'Resource\ProviderDocumentResource');
    Route::delete('provider/{provider}/service/{document}', 'Resource\ProviderDocumentResource@service_destroy')->name('document.service');

    Route::get('service', 'DispatcherController@service')->name('service');
    
});

Route::get('review/user', 'DispatcherController@user_review')->name('user.review');
Route::get('user/{id}/request', 'Resource\UserResource@request')->name('user.request');

Route::get('map', 'DispatcherController@map_index')->name('map.index');
Route::get('map/ajax', 'DispatcherController@map_ajax')->name('map.ajax');


Route::get('payment', 'DispatcherController@payment')->name('payment');

// statements

Route::get('/statement', 'DispatcherController@statement')->name('ride.statement');
Route::get('/statement/provider', 'DispatcherController@statement_provider')->name('ride.statement.provider');
Route::get('/statement/today', 'DispatcherController@statement_today')->name('ride.statement.today');
Route::get('/statement/monthly', 'DispatcherController@statement_monthly')->name('ride.statement.monthly');
Route::get('/statement/yearly', 'DispatcherController@statement_yearly')->name('ride.statement.yearly');


// Static Pages - Post updates to pages.update when adding new static pages.

Route::get('/help', 'DispatcherController@help')->name('help');
Route::get('/send/push', 'DispatcherController@push')->name('push');
Route::post('/send/push', 'DispatcherController@send_push')->name('send.push');
Route::get('/privacy', 'DispatcherController@privacy')->name('privacy');
Route::get('/terms', 'DispatcherController@terms')->name('terms');
Route::get('/help', 'DispatcherController@help')->name('help');
Route::get('/offers', 'DispatcherController@offers')->name('offers');
Route::get('/about_us', 'DispatcherController@about_us')->name('about_us');
Route::post('/pages', 'DispatcherController@pages')->name('pages.update');
Route::resource('requests', 'Resource\TripResource');
Route::get('scheduled', 'Resource\TripResource@scheduled')->name('requests.scheduled');

Route::get('push', 'DispatcherController@push_index')->name('push.index');
Route::post('push', 'DispatcherController@push_store')->name('push.store');
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'provider_name',
        'image',
        'price',
        'fixed',
        'description',
        'status',
        'minute',
        'hour',
        'rental_fare',
        'rental_km_price',
        'rental_hour_price',
        'outstation_driver',
        'outstation_km',
        'distance',
        'calculator',
        'capacity',
        'night_fare',
        'peak_time_8am_11am',
        'peak_time_5pm_9pm',
        'peak_time_11pm_6am'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         'created_at', 'updated_at'
    ];
    /**
     * The services that hasmany to the service type geo fencing.
     */
    public function service_geo_fencing()
    {
        return $this->hasMany('App\ServiceTypeGeoFencings','service_type_id');
    }
    /**
     * The services that hasmany to the service type geo fencing.
     */
    public function rental_hour_package()
    {
        return $this->hasMany('App\ServiceRentalHourPackage','service_type_id');
    }
    
}

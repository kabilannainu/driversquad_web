<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceTypeGeoFencings extends Model
{

	    protected $fillable = [
        'geo_fencing_id',
        'service_type_id',
        'distance',
        'hour',
        'minute',
        'price',
        'fixed',
        'old_ranges_price',
        'city_limits'

    ];



    protected $table ='service_types_geo_fencings';
    
     /**
     * The services that hasone to the service type geo fencing.
     */
    public function geo_fencing()
    {
        return $this->belongsTo('App\GeoFencing','geo_fencing_id');
    }
}

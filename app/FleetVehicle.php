<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FleetVehicle extends Model
{
     protected $fillable = [
        'fleet_id',
        'service_id',
        'vehicle_model',
        'vehicle_number'

      
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at', 'updated_at'
    ];

    public function service()
    {
        return $this->belongsTo('App\ServiceType');
    }

}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GeoFencing extends Model
{

	use SoftDeletes;

	protected $dates = ['deleted_at'];

	/**
     * ServiceType Model Linked
     */
    public function service_geo_fencing()
    {
        return $this->hasOne('App\ServiceTypeGeoFencings','geo_fencing_id');
    }

}

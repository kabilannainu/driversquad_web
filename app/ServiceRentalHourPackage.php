<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ServiceRentalHourPackage extends Model
{
     protected $table = 'service_rental_hour_packages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'service_type_id','hour','km','price'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        	'created_at','updated_at'
    ];
}

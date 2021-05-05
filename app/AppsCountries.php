<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AppsCountries extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'country_code','country_name'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    // protected $hidden = [
    //      'created_at', 'updated_at'
    // ];

}

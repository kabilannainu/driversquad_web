<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FleetDevice extends Model
{
     protected $fillable = [
        'fleet_id',
        'udid',
        'token',
        'type',
        'mobile',
        'otp',
        'current',
        'status'
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
     * The provider assigned to the request.
     */
    public function fleet()
    {
        return $this->belongsTo('App\Fleet');
    }
}

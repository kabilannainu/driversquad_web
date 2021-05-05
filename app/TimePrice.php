<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TimePrice extends Model
{
     protected $fillable = [
        'peak_price',
        'time_id','service_id'
        
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
         'created_at', 'updated_at'
    ];


    public function time()
    {
        return $this->hasMany('App\Time','id','time_id');
    }
    public function times()
    {
        return $this->hasOne('App\Time','id','time_id');
    }

}

<?php

namespace App;

use App\Notifications\AdminResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Setting;

class Admin extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','role_id' ,'picture'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getEmailAttribute()
    {
        if(Setting::get('demo_mode', 0) == 1) {
            return substr($this->attributes['email'], 0, 3).'****'.substr($this->attributes['email'], strpos($this->attributes['email'], "@"));
        } else {
            return $this->attributes['email'];
        }
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new AdminResetPassword($token));
    }

    public function roles()
    {
        return $this->belongsTo('App\Role','role_id','id');
    }
}

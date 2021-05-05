<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfNotAdmin
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @param  string|null  $guard
	 * @return mixed
	 */
	public function handle($request, Closure $next, $guard = 'admin')
	{
	    $route = \Request::route()->getName();
		$role_id = Auth::guard($guard)->user()->role_id;

		$exclude = array(

			'admin.service.index',
			'admin.dispatcher.trips',
			'admin.requests.show',
			'admin.profile',
			'admin.password',
			'admin.help',
			'admin.geo-fencing.store',
			'admin.geo-fencing.update',
			'admin.geo-fencing.destroy',
			'admin.user.request',
			'admin.dispatcher.store',
			'admin.user.store',
			'admin.provider.store',
			'admin.service.store',
			'admin.dispatcher.store',
			'admin.dispatcher.add',
			'admin.promocode.store',
	        'admin.dispatcher.cancel',
	        'admin.dispatcher.assign',
	        'admin.dispatcher.providers',
	        'admin.requests.destroy',
	        'admin.dispatcher.users', 
			'admin.document.store',
			'admin.send.push',
			'admin.dashboard',
			'admin.settings.store',
			'admin.subadmin.store',
			'admin.dispatcher.add',
		    'admin.dispatch-manager.store',
		    'admin.dispatch-manager.update',
			'admin.provider.statement',
			'admin.provider.document.index',
		    'admin.fleet.store',
		    'admin.fleet.update',
		    'admin.fleet.destroy',
		    'admin.account-manager.store',
		    'admin.account-manager.update',
		    'admin.account-manager.destroy',
			'admin.provider.disapprove',
            'admin.provider.approve',
            'admin.destory.service',
            'admin.provider.document.store',
            'admin.provider.document.update',
            'admin.provider.document.edit',
            'admin.provider.document.destroy',
            'admin.provider.request',
            'admin.provider.update',
            'admin.user.show',
            'admin.user.edit',
            'admin.user.destroy',
            'admin.provider.document.service',
            'admin.provider.show',
            'admin.request.details',
            'estimated.fare',
           
            'admin.promocode.update',
            'admin.promocode.store',
            'admin.promocode.create',
            
            'admin.document.update',
            'admin.settings.payment.store',
            'admin.setting.store',
            'admin.pages.update',
            'admin.privacy.update',
            'admin.profile.update',
            'admin.password.update',
            'admin.user.update',
            'admin.service.update',
            'admin.dispatcher.index'
	);
		

	    if (!Auth::guard($guard)->check()) {

		  

	        return redirect('admin/login');
	    }
	    else
	    {
	      if(!in_array($route,$exclude))
	      {
		    if(Auth::guard($guard)->user()->role_id!=0)
			{
				//dd("as");
				$count = check_route($route,$role_id);

				//dd($count);
				if($count==0)
				{
					 return response()->view('errors.permission');
				}
			}
		  }
	    }

	    return $next($request);
	}
}
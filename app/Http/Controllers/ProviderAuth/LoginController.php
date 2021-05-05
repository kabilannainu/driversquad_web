<?php

namespace App\Http\Controllers\ProviderAuth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TwilioController;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Hesto\MultiAuth\Traits\LogsoutGuard;
use Illuminate\Http\Request;

use App\Provider;
use App\ProviderService;
use App\FleetVehicle;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers, LogsoutGuard {
        LogsoutGuard::logout insteadof AuthenticatesUsers;
    }

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    public $redirectTo = '/provider/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('provider.guest', ['except' => 'logout']);
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('provider.auth.login');
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('provider');
    }

    public function login_otp(Request $request)
    {

          try{

            $user = Provider::where('mobile',$request->mobile)->first();
            if(count($user)){

                $otp = mt_rand('1111','9999');

                $user->otp=$otp;
                $user->save();

                $data['otp'] = $otp;
                $data['phone'] = $request->mobile;   
                $data['message'] = 'Your Otp is '.$otp;        

                //     sendsms($request->mobile,$otp);

                (new TwilioController)->sendSms($data);

                $status = 'success';

                return $status;

            }else{

                $status = 'failure';

                return $status;

            }
            
        }catch(Exception $e){
            return response()->json(['error'=>'Something Went Wrong']);

        }
    }
    public function logout(){

        $provider = Provider::where('id',\Auth::guard('provider')->user()->id)->first();

         $provider_service = ProviderService::where('provider_id',$provider->id)->first();

         if(count($provider_service)!=0){

            if($provider->fleet!=0){
               
                   $provider_vehicle = FleetVehicle::where('fleet_id',$provider->fleet)->where('vehicle_number',$provider_service->service_number)->first();

                   $provider_vehicle->status='0';
                   $provider_vehicle->save();

                   ProviderService::where('provider_id',$provider->id)->delete();



               }
            Auth::guard('provider')->logout();

         }else{
            Auth::guard('provider')->logout();
         }

               

        

        return redirect('/');
    }

}

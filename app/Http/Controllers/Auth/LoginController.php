<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;


use Illuminate\Http\Request;
use App\Helpers\Helper;
use Auth;
use Route;
use App\UserDevice;
use App\User;
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

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        return view('user.auth.login');
    }
    // public function apiLogin(Request $request) {

        
    //     $where = array
    //     (
    //        'mobile'=> $request->username ,
    //        'device_token' => $request->device_token,
    //        'status' => 1
    //     );  

    //     $device_check = UserDevice::where($where)->count();

    //     //dd($device_check);

    //     $user_data = User::where('mobile',$request->username)->first();
    //     //dd(count($user_data));
    //     $where1 = array
    //     (
    //         'mobile'=> $request->username ,

    //         'status' => 1
    //     ); 

    //     //dd(count($user_count));

    //     if(count($user_data)!=0)
    //     {


    //         if($device_check!=0)
    //         {
    //            //dd('asd');

    //             $tokenRequest = $request->create('/oauth/token', 'POST', $request->all());
    //             $request->request->add([
    //                "client_id"     => $request->client_id,
    //                "client_secret" => $request->client_secret,
    //                "grant_type"    => 'password',
    //                "code"          => '*',
    //             ]);

    //             $response = Route::dispatch($tokenRequest);
    //             $json = (array) json_decode($response->getContent());
    //             $user = User::where('mobile' ,$request->username)
    //                          ->where('device_token' , $request->device_token)
    //                          ->first();
               
    //             $response->setContent(json_encode($json));


         


    //             UserDevice::where($where1)->update(['current' => 0]);

    //             $device_update = UserDevice::where($where)->first();
    //             $device_update->current = 1;
    //             $device_update->save();

    //             $user_data->device_token = $request->device_token;
    //             $user_data->device_id = $request->device_id;
    //             $user_data->device_type = $request->device_type;
    //             $user_data->save();
          
               

    //             return $response;
    //         }
    //         else
    //         {
    //            /* if($request->username=='7904572507')
    //             {
    //                  $otp = '123456';
    //             }
    //             else
    //             {*/
    //                  $otp = $this->otp_generate();
    //             //}

    //                   // $otp = 1234;

    //             UserDevice::where($where1)->update(['current' => 0]);
               
    //             $device['mobile'] = $request->username;
    //             $device['device_token'] = $request->device_token;
    //             $device['device_id'] = $request->device_id;
    //             $device['status'] = 0;
    //             $device['current'] = 1;
    //             $device['otp'] = $otp;
    //             UserDevice::create($device);

    //             $mobileno = $user_data->country_code.$user_data->mobile;
    //             $message = "Otp sent by jaldee for login is ".$otp;

    //             sendsms($mobileno,$otp);
    //             //Twilio::message($phone,$message);

    //             return response()->json(['error' =>"new device" ,'otp' =>$otp , 'message' => 'Otp has been sent to your mobile','status' => false], 422);
    //         } 
    //     }

    //     else
    //     {
    //            return response()->json(['error' =>"invalid_credentials" , 'message' => 'The user credentials were incorrect'], 422);
    //     }


    

    // }

   


    public function apiLogin(Request $request) {
        $this->validate($request, [
            'password' => 'required',
            'username' => 'required',
            'country_code' => 'required',
            'device_token' => 'required',
            'device_type' => 'required',

        ]);

    try{    
        $whereCondition =array(            
            'mobile' => $request->username,
             'country_code'=>$request->country_code
        );
       $userExits =  User::where($whereCondition)->first();
       if($userExits)
       {    
            $device_exist =  UserDevice::where('mobile', $request->username)->count();
            if($device_exist==0){
               $device =  new UserDevice (); 
               $device->mobile = $request->username;
               $device->device_token = $request->device_token;
               //$device->country_code = $request->country_code;
               $device->status = 1;
               $device->current = 1;
               $device->save();
            }
            $where1 = array
            (
            'mobile'=> $request->username,
            'device_token' => $request->device_token,
            'status' => 1
            ); 
            UserDevice::where($where1)->update(['current' => 0]);
            $where =array(
                'mobile' => $request->username,
                'device_token' => $request->device_token
            );
            $device =  UserDevice::where($where)->first();
            if($device){
                $device->status = 1;
                $device->current = 1;
                $device->save();
            }else{
               $device =  new UserDevice (); 
               $device->mobile = $request->username;
               $device->device_token = $request->device_token;
               $device->status = 1;
                $device->current = 1;
                $device->save();
            }
            $update=User::wheremobile($request->username)->first();
            if($update){
                $update->device_token= $request->device_token;
                $update->device_type = $request->device_type;
                $update->save();
            }
            $tokenRequest = $request->create('/oauth/token', 'POST', $request->all());
            $request->request->add([
            "client_id"     => $request->client_id,
            "client_secret" => $request->client_secret,
            "grant_type"    => 'password',
            "code"          => '*',
            ]);
            $response = Route::dispatch($tokenRequest);
            $json = (array) json_decode($response->getContent());
            $response->setContent(json_encode($json));
            return $response;
       } 
       else
       {
            return response()->json(['status' => 'false' , 'message' => 'Invalid username.' ],422);
       }
    }
    catch (Exception $e) {
       
        return response()->json(['error' => trans('api.something_went_wrong')], 500);
    }

}

    public function otp_generate()
    {
        $otp = mt_rand(1000, 9999);
    
        $count = UserDevice::where('otp',$otp)->count();
        if($count!=0)
        {
           $otp = $this->otp_generate();
        }

        return $otp;
    }
}

<?php

namespace App\Http\Controllers\ProviderAuth;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Controllers\Controller;

use Tymon\JWTAuth\Exceptions\JWTException;
use App\Notifications\ResetPasswordOTP;
use App\Http\Controllers\TwilioController;

use Auth;
use Config;
use JWTAuth;
use App\Helpers\Helper;
use Setting;
use Notification;
use Validator;
use Socialite;

use App\Provider;
use App\ProviderDevice;
use App\ProviderService;
use App\RequestFilter;
use App\ServiceType;

class TokenController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function register(Request $request)
    {
        $this->validate($request, [
                'device_id' => 'required',
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                // 'email' => 'email|max:255|unique:providers',
                'mobile' => 'required|unique:providers',
                'password' => 'required|min:6|confirmed',
                'emergency_contact1' => 'required',
                // 'child_seat' => 'required|in:1,0',
                // 'pet_allowed' => 'required|in:1,0',
            ]);

        try{

            $Provider = $request->all();
            $Provider['password'] = bcrypt($request->password);
            $Provider = Provider::create($Provider);

            if(Setting::get('demo_mode', 0) == 1) {
                $Provider->update(['status' => 'approved']);
                ProviderService::create([
                    'provider_id' => $Provider->id,
                    'service_type_id' => '1',
                    'status' => 'active',
                    'service_number' => 'TN 01 A 1234',
                    'service_model' => 'Audi R8',
                ]);
            }

            ProviderDevice::create([
                    'provider_id' => $Provider->id,
                    'udid' => $request->device_id,
                    'token' => $request->device_token,
                    'voip_token' => @$request->voip_token,
                    'mobile' => $request->mobile,
                    'type' => $request->device_type,
                    'status' => 1,
                    'current' => 1
                ]);


               Config::set('auth.providers.users.model', 'App\Provider');
               $credentials = $request->only('mobile', 'password');
               try {
                    if (! $token = JWTAuth::attempt($credentials)) {
                        return response()->json(['error' => 'The mobile or password you entered is incorrect.'], 401);
                    }
                } catch (JWTException $e) {
                    return response()->json(['error' => 'Something went wrong, Please try again later!'], 500);
                }

                $Provider = Provider::with('device')->find(Auth::user()->id);
                $Provider->access_token = $token;
                $Provider->sos = Setting::get('sos_number', '911');
                $Provider->service = ProviderService::whereprovider_id(Auth::user()->id)->get();


            return $Provider;


        } catch (QueryException $e) {

            //zdd($e->getMessage());
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Something went wrong, Please try again later!'], 500);
            }
            return abort(500);
        }
        
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function authenticate(Request $request)
    {
        $this->validate($request, [
                'device_id' => 'required',
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'mobile' => 'required',
                'country_code' => 'required',
                'password' => 'required|min:6',
            ]);
            $provider_data = Provider::where([['mobile',$request->mobile],['country_code',$request->country_code]])->first();
     if($provider_data){
        Config::set('auth.providers.users.model', 'App\Provider');

        $credentials = $request->only('mobile', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'The mobile number or password you entered is incorrect.'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Something went wrong, Please try again later!'], 500);
        }

        $User = Provider::with('device')->find(Auth::user()->id);

        $User->access_token = $token;
        $User->currency = Setting::get('currency', '$');
        $User->sos = Setting::get('sos_number', '911');
        $User->service = ProviderService::whereprovider_id(Auth::user()->id)->get();
        if($User->device) {
            ProviderDevice::where('id',$User->device->id)->update([
        
                'udid' => $request->device_id,
                'token' => $request->device_token,
                'type' => $request->device_type,
                'current' => 1
            ]);
            
        } else {
            ProviderDevice::create([
                    'provider_id' => $User->id,
                    'udid' => $request->device_id,
                    'token' => $request->device_token,
                    'mobile' => $request->mobile,
                    'type' => $request->device_type,
                    'status' => 1,
                    'current' => 1
                ]);
        }
        // $User->device = ProviderDevice::where('id',$User->device->id)->first();
        $User->device_token = $request->device_token;
        //$newotp = mt_rand(1000 ,9999);
        // $newotp = '1234';
        // $mobileno = $User->mobile;
        // sendsms($mobileno,$newotp);
        return response()->json($User);
    }
    else
    {
           return response()->json(['error' =>"invalid_credentials" , 'message' => 'The user credentials were incorrect','status' => false], 422);
    }

    }


    public function apiLogin(Request $request)
    {
            $this->validate($request, [
                'device_id' => 'required',
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'mobile' => 'required',

            ]);


        $where = array
        (
           'mobile'=> $request->mobile,
           'token' => $request->device_token,
           'status' => 1
        ); 

        $where1 = array
        (
           'mobile'=> $request->mobile,
           'status' => 1
        ); 


        Config::set('auth.providers.users.model', 'App\Provider');


        $device_check = ProviderDevice::where($where)->count();
    



        $user_data = Provider::where('mobile',$request->mobile)->first();


        if(count($user_data)!=0)
        {

            if($device_check!=0)
            {

                    $credentials = array(
                            'mobile' => $request->mobile,
                            'password' => '123456'
                    );

                    try {
                        if (! $token = JWTAuth::attempt($credentials)) {
                            return response()->json(['message' => 'The mobile number or password you entered is incorrect.' , 'error' => "invalid_credentials" ], 422);
                        }
                    } catch (JWTException $e) {
                        return response()->json(['error' =>trans('api.something_went_wrong')], 500);
                    }

                    ProviderDevice::where($where1)->update(['current' => 0]);
                    
                    $device_update = ProviderDevice::where($where)->first();
                    $device_update->current = 1;
                    $device_update->token = $request->device_token;
                    $device_update->voip_token = @$request->voip_token;
                    $device_update->udid = $request->device_id;
                    $device_update->type = $request->device_type;
                    $device_update->save();

                    $service_update = ProviderService::whereprovider_id(Auth::user()->id)->first();
                    $service_update->status = 'active'; 
                    $service_update->save();
  

                    $User = Provider::with('device')->find(Auth::user()->id);

                    $User->access_token = $token;
                    $User->currency = Setting::get('currency', '$');
                    $User->sos = Setting::get('sos_number', '911');
                    $User->service = ProviderService::whereprovider_id(Auth::user()->id)->get();
                    return response()->json($User);
            }
            else
            {
                $otp = $this->otp_generate();

                // $otp = 1234;
               
                $device['mobile'] = $request->mobile;
                $device['token'] = $request->device_token;
                $device['udid'] = $request->device_id;
                $device['type'] = $request->device_type;
                $device['voip_token'] = @$request->voip_token;
                $device['otp'] = $otp;
                $device['provider_id'] = $user_data->id;
                ProviderDevice::create($device);

                $mobileno = $user_data->country_code.$user_data->mobile;
                $message = "Otp sent by Jaldee for login is ".$otp;

                sendsms($mobileno,$otp);
                //Twilio::message($phone,$message);

                return response()->json(['otp' =>$otp , 'message' => 'Otp has been sent to your mobile','status' => false], 422);
            } 
        }

        else
        {
               return response()->json(['error' =>"invalid_credentials" , 'message' => 'The user credentials were incorrect','status' => false], 422);
        }

    }

     public function voice_sms(Request $request)
    {
        $this->validate($request, [
                
                'username' => 'required|numeric',
                
            ]);

            $mobile = $request->username;
            $mobileno = "91".$mobile."";
            
            $otp = $this->otp_generate();

            voicesms($mobileno,$otp);

            return response()->json(['otp' => $otp]);

    }


     public function verify_otp(Request $request)
    {
            $this->validate($request, [
                'otp' => 'required|numeric|exists:provider_devices,otp',
                'mobile' => 'required|numeric|exists:provider_devices,mobile',
                'device_token' => 'required|exists:provider_devices,token'
            ]);

            Config::set('auth.providers.users.model', 'App\Provider');

            try{    
                $where =array(
                    'otp' => $request->otp,
                    'mobile' => $request->mobile,
                    'token' => $request->device_token

                );
               $device =  ProviderDevice::where($where)->first();
                
               if(count($device)!=0)
               {

                $where1 = array
                (
                   'mobile'=> $request->mobile,
                   'status' => 1
                ); 


                    ProviderDevice::where($where1)->update(['current' => 0]);
                    $device->status = 1;
                    $device->current = 1;
                    $device->save();

                    $credentials = array(
                            'mobile' => $request->mobile,
                            'password' => '123456'
                    );

                    try {
                        if (! $token = JWTAuth::attempt($credentials)) {
                            return response()->json(['message' => 'The mobile number or password you entered is incorrect.' , 'error' => "invalid_credentials" ], 422);
                        }
                    } catch (JWTException $e) {
                        return response()->json(['error' =>trans('api.something_went_wrong')], 500);
                    }


                    $User = Provider::with('device')->find(Auth::user()->id);

                    $User->access_token = $token;
                    $User->currency = Setting::get('currency', '$');
                    $User->sos = Setting::get('sos_number', '911');
                    $User->service = ProviderService::whereprovider_id(Auth::user()->id)->get();
                    return response()->json($User);
          

               } 
               else
               {
                    return response()->json(['status' => 'false' , 'message' => 'Otp is Invalid' ], 422);
               }

    

            }
            catch (Exception $e) {
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            }

    }
   
    public function send_otp(Request $request)
    {
            $this->validate($request, [
                
                'username' => 'required|numeric',
                
            ]);

            $data['mobile'] = $request->country_code.$request->username;
            
            if ($request->username == '9940792094') {
                $otp = 1234;
            } else {
                $otp = $this->otp_generate();
            }

            $data['message'] = 'Your otp is '.$otp;

            (new TwilioController)->sendSms($data);

            return response()->json(['otp' => $otp]);

    }

    public function otp_generate()
    {
        $otp = mt_rand(1000, 9999);
    
        $count = ProviderDevice::where('otp',$otp)->count();
        if($count!=0)
        {
           $otp = $this->otp_generate();
        }

        return $otp;
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function logout(Request $request)
    {
        try {
            ProviderDevice::where('provider_id', $request->id)->where('token', $request->device_token)->update(['current'=> 0]);
            ProviderService::where('provider_id',$request->id)->update(['status' => 'offline']);

            $provider = $request->id;
            $LogoutOpenRequest = RequestFilter::with(['request.provider','request'])
                ->where('provider_id', $provider)
                ->whereHas('request', function($query) use ($provider){
                    $query->where('status','SEARCHING');
                    $query->where('current_provider_id','<>',$provider);
                    $query->orWhereNull('current_provider_id');
                    })->pluck('id');

            if(count($LogoutOpenRequest)>0){
                RequestFilter::whereIn('id',$LogoutOpenRequest)->delete();
            } 

               $provider = Provider::where('id',$request->id)->first();

               if($provider->fleet!=0){

                   $provider_vehicle = FleetVehicle::where('provider_id',$request->id)->first();

                   $provider_vehicle->status='0';
                   $provider_vehicle->save();

                   ProviderService::where('provider_id',$request->id)->delete();


               }
            
            return response()->json(['message' => trans('api.logout_success')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    /**
     * Forgot Password.
     *
     * @return \Illuminate\Http\Response
     */
    public function forgot_password(Request $request){

        $this->validate($request, [
            'mobile' => 'required',
            ]);

        try{  
            
            $provider = Provider::where('mobile' , $request->mobile)->first();
        if($provider){
            $otp = mt_rand(100000, 999999);

            $provider->otp = $otp;
            $provider->save();

            $data['mobile'] = $provider->country_code.$provider->mobile;
            $data['message'] = 'Your verification Code is'.$otp;

            (new TwilioController)->sendSms($data);

            return response()->json([
                'message' => 'OTP sent to your mobile!',
                'provider' => $provider
            ]);
            }
            else{
                return response()->json(['message' => 'The mobile number you entered is incorrect.' , 'error' => "invalid_credentials" ], 422);
            }
        }catch(Exception $e){
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }


    /**
     * Reset Password.
     *
     * @return \Illuminate\Http\Response
     */

    public function reset_password(Request $request){

        $this->validate($request, [
                'password' => 'required|confirmed|min:6',
                'id' => 'required|numeric|exists:providers,id'
            ]);

        try{

            $Provider = Provider::findOrFail($request->id);

            $Provider->password = bcrypt($request->password);
            $Provider->save();
            if($request->ajax()) {
                return response()->json(['message' => 'Password Updated']);
            }

        }catch (Exception $e) {
            if($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')]);
            }
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function facebookViaAPI(Request $request) { 

        $validator = Validator::make(
            $request->all(),
            [
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'accessToken'=>'required',
                //'mobile' => 'required',
                'device_id' => 'required',
                'login_by' => 'required|in:manual,facebook,google'
            ]
        );
        
        if($validator->fails()) {
            return response()->json(['status'=>false,'message' => $validator->messages()->all()]);
        }
        $user = Socialite::driver('facebook')->stateless();
        $FacebookDrive = $user->userFromToken( $request->accessToken);
       
        try{
            $FacebookSql = Provider::where('social_unique_id',$FacebookDrive->id);
            if($FacebookDrive->email !=""){
                $FacebookSql->orWhere('email',$FacebookDrive->email);
            }
            $AuthUser = $FacebookSql->first();
            if($AuthUser){ 
                $AuthUser->social_unique_id=$FacebookDrive->id;
                $AuthUser->login_by="facebook";
                $AuthUser->mobile=$request->mobile?:'';
                $AuthUser->save();  
            }else{   
                $AuthUser["email"]=$FacebookDrive->email;
                $name = explode(' ', $FacebookDrive->name, 2);
                $AuthUser["first_name"]=$name[0];
                $AuthUser["last_name"]=isset($name[1]) ? $name[1] : '';
                $AuthUser["password"]=bcrypt($FacebookDrive->id);
                $AuthUser["social_unique_id"]=$FacebookDrive->id;
                $AuthUser["avatar"]=$FacebookDrive->avatar;
                $AuthUser["mobile"]=$request->mobile?:'';
                $AuthUser["login_by"]="facebook";
                $AuthUser = Provider::create($AuthUser);

                if(Setting::get('demo_mode', 0) == 1) {
                    $AuthUser->update(['status' => 'approved']);
                    ProviderService::create([
                        'provider_id' => $AuthUser->id,
                        'service_type_id' => '1',
                        'status' => 'active',
                        'service_number' => 'TN 01 A 1234',
                        'service_model' => 'Audi R8',
                    ]);
                }
            }    
            if($AuthUser){ 
                $userToken = JWTAuth::fromUser($AuthUser);
                $User = Provider::with('service', 'device')->find($AuthUser->id);
                if($User->device) {
                    ProviderDevice::where('id',$User->device->id)->update([
                        
                        'udid' => $request->device_id,
                        'token' => $request->device_token,
                        'type' => $request->device_type,
                    ]);
                    
                } else {
                    ProviderDevice::create([
                        'provider_id' => $User->id,
                        'udid' => $request->device_id,
                        'token' => $request->device_token,
                        'type' => $request->device_type,
                    ]);
                }
                return response()->json([
                            "status" => true,
                            "token_type" => "Bearer",
                            "access_token" => $userToken,
                            'currency' => Setting::get('currency', '$'),
                            'sos' => Setting::get('sos_number', '911')
                        ]);
            }else{
                return response()->json(['status'=>false,'message' => "Invalid credentials!"]);
            }  
        } catch (Exception $e) {
            return response()->json(['status'=>false,'message' => trans('api.something_went_wrong')]);
        }
    }

    public function appleViaAPI(Request $request) { 

        $validator = Validator::make(
            $request->all(),
            [
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'social_unique_id'=>'required',
               // 'mobile' => 'required',
                'device_id' => 'required',
                'login_by' => 'required|in:manual,facebook,google,apple'
            ]
        );
        
        if($validator->fails()) {
            return response()->json(['status'=>false,'message' => $validator->messages()->all()]);
        }
       
       
        try{
            $applesignSql = Provider::where('social_unique_id',$request->social_unique_id);
           
            $AuthUser = $applesignSql->first();
            if($AuthUser){ 
                $AuthUser->social_unique_id=$request->social_unique_id;
                $AuthUser->login_by="apple";
                $AuthUser->mobile=$request->mobile?:'';
                $AuthUser->save();  
            }else{   

                $AuthUser["email"]=$request->email;
                $name = $request->name;
                $AuthUser["first_name"]=$name;
                $AuthUser["last_name"]=$name;
                $AuthUser["password"]=bcrypt($request->social_unique_id);
               // $AuthUser["referral_code"]=$NewRefferalCode;
                $AuthUser["social_unique_id"]=$request->social_unique_id;
              //  $AuthUser["avatar"]=$FacebookDrive->avatar;
                $AuthUser["mobile"]=$request->mobile?:'';
                $AuthUser["login_by"]="apple";
                $AuthUser = Provider::create($AuthUser);

                if(Setting::get('demo_mode', 0) == 1) {
                    $AuthUser->update(['status' => 'approved']);
                    ProviderService::create([
                        'provider_id' => $AuthUser->id,
                        'service_type_id' => '11',
                        'status' => 'active',
                        'service_number' => '4pp03ets',
                        'service_model' => 'Audi R8',
                    ]);
                }
            }    
            if($AuthUser){ 
                $userToken = JWTAuth::fromUser($AuthUser);
                $User = Provider::with('service', 'device')->find($AuthUser->id);
                ProviderDevice::where('provider_id', $User->id)->update(['current' => 0]);
                if($User->device) {
                    ProviderDevice::where('id',$User->device->id)->update([
                        'current' => 1,
                        'udid' => $request->device_id,
                        'token' => $request->device_token,
                        'type' => $request->device_type,
                    ]);
                    
                } else {
                    ProviderDevice::create([
                        'current' => 1,
                        'provider_id' => $User->id,
                        'udid' => $request->device_id,
                        'token' => $request->device_token,
                        'type' => $request->device_type,
                    ]);
                }
                return response()->json([
                            "status" => true,
                            "token_type" => "Bearer",
                            "access_token" => $userToken,
                            'currency' => Setting::get('currency', '$'),
                            'sos' => Setting::get('sos_number', '911')
                        ]);
            }else{
                return response()->json(['status'=>false,'message' => "بيانات المستخدم غير مطابقة!"]);
            }  
        } catch (Exception $e) {
            return response()->json(['status'=>false,'message' => trans('api.something_went_wrong')]);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function googleViaAPI(Request $request) { 

        $validator = Validator::make(
            $request->all(),
            [
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'accessToken'=>'required',
                //'mobile' => 'required',
                'device_id' => 'required',
                'login_by' => 'required|in:manual,facebook,google'
            ]
        );
        
        if($validator->fails()) {
            return response()->json(['status'=>false,'message' => $validator->messages()->all()]);
        }
        $user = Socialite::driver('google')->stateless();
        $GoogleDrive = $user->userFromToken( $request->accessToken);
       
        try{
            $GoogleSql = Provider::where('social_unique_id',$GoogleDrive->id);
            if($GoogleDrive->email !=""){
                $GoogleSql->orWhere('email',$GoogleDrive->email);
            }
            $AuthUser = $GoogleSql->first();
            if($AuthUser){
                $AuthUser->social_unique_id=$GoogleDrive->id;
                $AuthUser->mobile=$request->mobile?:'';  
                $AuthUser->login_by="google";
                $AuthUser->save();
            }else{   
                $AuthUser["email"]=$GoogleDrive->email;
                $name = explode(' ', $GoogleDrive->name, 2);
                $AuthUser["first_name"]=$name[0];
                $AuthUser["last_name"]=isset($name[1]) ? $name[1] : '';
                $AuthUser["password"]=($GoogleDrive->id);
                $AuthUser["social_unique_id"]=$GoogleDrive->id;
                $AuthUser["avatar"]=$GoogleDrive->avatar;
                $AuthUser["mobile"]=$request->mobile?:''; 
                $AuthUser["login_by"]="google";
                $AuthUser = Provider::create($AuthUser);

                if(Setting::get('demo_mode', 0) == 1) {
                    $AuthUser->update(['status' => 'approved']);
                    ProviderService::create([
                        'provider_id' => $AuthUser->id,
                        'service_type_id' => '1',
                        'status' => 'active',
                        'service_number' => 'TN 01 A 1234',
                        'service_model' => 'Audi R8',
                    ]);
                }
            }    
            if($AuthUser){
                $userToken = JWTAuth::fromUser($AuthUser);
                $User = Provider::with('service', 'device')->find($AuthUser->id);
                if($User->device) {
                    ProviderDevice::where('id',$User->device->id)->update([
                        
                        'udid' => $request->device_id,
                        'token' => $request->device_token,
                        'type' => $request->device_type,
                    ]);
                    
                } else {
                    ProviderDevice::create([
                        'provider_id' => $User->id,
                        'udid' => $request->device_id,
                        'token' => $request->device_token,
                        'type' => $request->device_type,
                    ]);
                }
                return response()->json([
                            "status" => true,
                            "token_type" => "Bearer",
                            "access_token" => $userToken,
                            'currency' => Setting::get('currency', '$'),
                            'sos' => Setting::get('sos_number', '911')
                        ]);
            }else{
                return response()->json(['status'=>false,'message' => "Invalid credentials!"]);
            }  
        } catch (Exception $e) {
            return response()->json(['status'=>false,'message' => trans('api.something_went_wrong')]);
        }
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function refresh_token(Request $request)
    {

        Config::set('auth.providers.users.model', 'App\Provider');

        $Provider = Provider::with('service', 'device')->find(Auth::user()->id);

        try {
            if (!$token = JWTAuth::fromUser($Provider)) {
                return response()->json(['error' => 'Unauthenticated'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Something went wrong'], 500);
        }

        $Provider->access_token = $token;

        return response()->json($Provider);
    }

    /**
     * Show the email availability.
     *
     * @return \Illuminate\Http\Response
     */

    public function verify(Request $request)
    {
        $this->validate($request, [
                'email' => 'required|email|max:255|unique:providers',
            ]);

        try{
            
            return response()->json(['message' => trans('api.email_available')]);

        } catch (Exception $e) {
             return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }
    /**
     * Check mobile number exists or not
     *
     * @return \Illuminate\Http\Response
     */
    public function provider_check_mobile(Request $request)
    {
        $check =Provider::wheremobile($request->username)->first(); 

        if($check)   
            return response()->json(['status' => true ]); 
        else
            return response()->json(['status' => false ]);  
    }

    public function settings(Request $request)
    {
        $serviceType = ServiceType::select('id', 'name')->get();
        $settings = [
            'serviceTypes' => $serviceType,
            'api_key' => Setting::get('map_key',''),
            'android_api_key' => Setting::get('android_map_key',''),
            'ios_api_key' => Setting::get('ios_map_key','')
        ];
        return response()->json($settings);        
       
    }
}

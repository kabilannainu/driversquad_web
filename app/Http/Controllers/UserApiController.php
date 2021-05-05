<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use DB;
use Log;
use Auth;
use Hash;
use Route;
use Storage;
use Setting;
use Exception;
use Validator;
use Notification;
use pointLocation;
use Config;

use Carbon\Carbon;
use App\Http\Controllers\SendPushNotification;
use App\Http\Controllers\TwilioController;
use App\Notifications\ResetPasswordOTP;
use App\Helpers\Helper;

use App\Card;
use App\User;
use App\Work;
use App\Time;
use App\TimePrice;
use App\Provider;
use App\Settings;
use App\Promocode;
use App\ServiceType;
use App\UserDevice;
use App\UserRequests;
use App\RequestFilter;
use App\PromocodeUsage;
use App\WalletPassbook;
use App\PromocodePassbook;
use App\ProviderService;
use App\UserRequestRating;
use App\GeoFencing;
use App\PackageType;
use App\ServiceRentalHourPackage;
use App\Http\Controllers\ProviderResources\TripController;


use Softon\Indipay\Facades\Indipay;

use App\UserRequestPayment;
use DateTime;
use DateInterval;
use DatePeriod;

class UserApiController extends Controller
{
    /**  Check Email/Mobile Availablity Of a User  **/
    public function versions()
    {
        $versions = array(
                        'ios_review' => Setting::get('ios_review'),
                    );

        return response()->json($versions);
    }


     public function login_otp(Request $request)
    {
        try{

            $user = User::where('mobile',$request->mobile)->first();
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

    public function verify(Request $request)
    {
        $this->validate($request, [
                'email' => 'required|email|unique:users',
                
            ]);

        try{
            
            return response()->json(['message' => trans('api.email_available')]);

        } catch (Exception $e) {
             return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    public function login(Request $request)
    {

        $tokenRequest = $request->create('/oauth/token', 'POST', $request->all());
        $request->request->add([
           "client_id"     => $request->client_id,
           "client_secret" => $request->client_secret,
           "grant_type"    => 'password',
           "code"          => '*',
        ]);
        $response = Route::dispatch($tokenRequest);

        $json = (array) json_decode($response->getContent());
        //      $json['status'] = true;

        $response->setContent(json_encode($json));

        
        $update = User::where('email', $request->username)->update(['device_token' => $request->device_token , 'device_id' => $request->device_id , 'device_type' => $request->device_type]);    
        
        return $response;
    }

    public function signup(Request $request)
    {
        $this->validate($request, [
                'social_unique_id' => ['required_if:login_by,facebook,google','unique:users'],
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'device_id' => 'required',
                'login_by' => 'required|in:manual,facebook,google',
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                //'email' => 'email|max:255|unique:users',
                'mobile' => 'required|unique:users',
                'password' => 'required|min:6',
                'emergency_contact1' => 'required',
            ]);

        try{
            
            $User = $request->all();

            
            $User['payment_mode'] = 'CASH';
            $User['password'] = bcrypt($request->password);
            $User = User::create($User);
            $token = $User->createToken('My APP')->accessToken;
            $User['token_type'] = 'Bearer';
            $User['access_token'] = $token;

            $device['mobile'] = $request->mobile;
            $device['device_token'] = $request->device_token;
            $device['device_id'] = $request->device_id;
            $device['status'] = 1;
            $device['current'] = 1;
            $device['country_code'] = $request->country_code;
            UserDevice::create($device);

            

            return $User;
        } catch (Exception $e) {
            
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

     public function voice_sms(Request $request)
    {
        // dd($request->username);
        $this->validate($request, [
                
                'username' => 'required|numeric',
                
            ]);

            $mobile = $request->username;
            $mobileno = "91".$mobile."";

            $otp = $this->otp_generate();

            voicesms($mobileno,$otp);

            return response()->json(['otp' => $otp]);

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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function logout(Request $request)
    {
        try {
            User::where('id', $request->id)->update(['device_id'=> '', 'device_token' => '']);
            return response()->json(['message' => trans('api.logout_success')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function change_password(Request $request){

        $this->validate($request, [
                'password' => 'required|confirmed|min:6',
                'old_password' => 'required',
            ]);

        $User = Auth::user();

        if(Hash::check($request->old_password, $User->password))
        {
            $User->password = bcrypt($request->password);
            $User->save();

            if($request->ajax()) {
                return response()->json(['message' => trans('api.user.password_updated')]);
            }else{
                return back()->with('flash_success', 'Password Updated');
            }

        } else {
            if($request->ajax()) {
                return response()->json(['error' => trans('api.user.change_password')], 500);
            }else{
                return back()->with('flash_error',trans('api.user.change_password'));
            }
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function update_location(Request $request){

        $this->validate($request, [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
            ]);

        if($user = User::find(Auth::user()->id)){

            $user->latitude = $request->latitude;
            $user->longitude = $request->longitude;
            $user->save();

            return response()->json(['message' => trans('api.user.location_updated')]);

        }else{

            return response()->json(['error' => trans('api.user.user_not_found')], 500);

        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function details(Request $request){

        $this->validate($request, [
            'device_type' => 'in:android,ios',
        ]);

        try{

            if($user = User::find(Auth::user()->id)){

                if($request->has('device_token')){
                    $user->device_token = $request->device_token;
                }

                if($request->has('device_type')){
                    $user->device_type = $request->device_type;
                }

                if($request->has('device_id')){
                    $user->device_id = $request->device_id;
                }

                $user->save();

                $user->currency = Setting::get('currency');
                $user->sos = Setting::get('sos_number', '911');
                $user->rental_content = Setting::get('rental_content', 'Dummy Content');
                $user->outstation_content = Setting::get('outstation_content', 'Dummy Content');
                return $user;

            } else {
                return response()->json(['error' => trans('api.user.user_not_found')], 500);
            }
        }
        catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function update_profile(Request $request)
    {

        $this->validate($request, [
                'first_name' => 'required|max:255',
                'last_name' => 'max:255',
                'email' => 'email|unique:users,email,'.Auth::user()->id,
                'mobile' => 'required',
                'picture' => 'mimes:jpeg,bmp,png',
            ]);

         try {

            $user = User::findOrFail(Auth::user()->id);

            if($request->has('first_name')){ 
                $user->first_name = $request->first_name;
            }
            
            if($request->has('last_name')){
                $user->last_name = $request->last_name;
            }
            
            if($request->has('email')){
                $user->email = $request->email;
            }
        
            if($request->has('mobile')){
                $user->mobile = $request->mobile;
            }

            if ($request->has('emergency_contact1')){
                $user->emergency_contact1 = $request->emergency_contact1;
            }

            if ($request->has('emergency_contact2')){
                $user->emergency_contact2 = $request->emergency_contact2;
            }
            
             if($request->has('gender')){
                $user->gender = $request->gender;
            }

            if ($request->picture != "") {
                Storage::delete($user->picture);
                $user->picture = $request->picture->store('user/profile');
            }

            $user->save();

            if($request->ajax()) {
                return response()->json($user);
            }else{
                return back()->with('flash_success', trans('api.user.profile_updated'));
            }
        }

        catch (ModelNotFoundException $e) {
             return response()->json(['error' => trans('api.user.user_not_found')], 500);
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function services() {

        if($serviceList = ServiceType::with('rental_hour_package')->get()) {
            return $serviceList;
        } else {
            return response()->json(['error' => trans('api.services_not_found')], 500);
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function service_geo_fencing(Request $request) { 

        // check with s lat lang to city polygon
        $check =$this->poly_check_request((round($request->s_latitude,6)),(round($request->s_longitude,6)));

    

        if($check=='no')
           {
               if($request->ajax()) {
                   return response()->json(['error' => 'Service is not available at this location.'], 500);
               } else {
                   return redirect('dashboard')->with('flash_error', 'Service is not available at this location.');
               }
           }

        $geo_check = $this->poly_check_new((round($request->s_latitude,6)),(round($request->s_longitude,6))); 

        $service_type_id = $request->service_type;
        $geo_fencing_service_type = GeoFencing::with(
                    ['service_geo_fencing' =>  function ($query) use ($service_type_id) {
                            $query->where('service_type_id',$service_type_id);
                   } ])->whereid($geo_check)->first(); 

         
        return response()->json(['km' => $geo_fencing_service_type->service_geo_fencing->distance,'fare' => $geo_fencing_service_type->service_geo_fencing->price,'service' => ServiceType::findOrFail($request->service_type)]); 

    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function send_request(Request $request) {
      // dd($request->all());
        $this->validate($request, [
                's_latitude' => 'required|numeric',
                //'d_latitude' => 'required|numeric',
                's_longitude' => 'required|numeric',
              //  'd_longitude' => 'required|numeric',
                'service_type' => 'required|numeric|exists:service_types,id',
                'promo_code' => 'exists:promocodes,promo_code',
                'distance' => 'required|numeric',
                'use_wallet' => 'numeric',
                'payment_mode' => 'in:CASH,CARD,PAYPAL,razorpay',
                'card_id' => ['required_if:payment_mode,CARD','exists:cards,card_id,user_id,'.Auth::user()->id],
            ]);

        Log::info('New Request from User: '.Auth::user()->id);
        Log::info('Request Details:', $request->all());

        // check with s lat lang to city polygon
        $check =$this->poly_check_request((round($request->s_latitude,6)),(round($request->s_longitude,6)));

    

        if($check=='no')
           {
               if($request->ajax()) {
                   return response()->json(['error' => 'Service is not available at this location.'], 500);
               } else {
                   return redirect('dashboard')->with('flash_error', 'Service is not available at this location.');
               }
           }

        $geo_check = $this->poly_check_new((round($request->s_latitude,6)),(round($request->s_longitude,6))); 

        $ActiveRequests = UserRequests::PendingRequest(Auth::user()->id)->count();

        if($ActiveRequests > 0) {
            if($request->ajax()) {
                return response()->json(['error' => trans('api.ride.request_inprogress')], 500);
            } else {
                return redirect('dashboard')->with('flash_error', 'Already request is in progress. Try again later');
            }
        }

        if($request->has('schedule_date') && $request->has('schedule_time')){
            $beforeschedule_time = (new Carbon("$request->schedule_date $request->schedule_time"))->subHour(1);
            $afterschedule_time = (new Carbon("$request->schedule_date $request->schedule_time"))->addHour(1);

            $CheckScheduling = UserRequests::where('status','SCHEDULED')
                            ->where('user_id', Auth::user()->id)
                            ->whereBetween('schedule_at',[$beforeschedule_time,$afterschedule_time])
                            ->count();


            if($CheckScheduling > 0){
                if($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.request_scheduled')], 500);
                }else{
                    return redirect('dashboard')->with('flash_error', 'Already request is Scheduled on this time.');
                }
            }

        }
        //// new schedule function check 
        if($request->has('schedule_date') && $request->has('schedule_time')){
            $beforeschedule_time = (new Carbon("$request->schedule_date $request->schedule_time"))->subHour(1);
            $afterschedule_time = (new Carbon("$request->schedule_date $request->schedule_time"))->addHour(1);

            $CheckScheduling = UserRequests::where('status','SCHEDULES')
                            ->where('user_id', Auth::user()->id)
                            ->whereBetween('schedule_at',[$beforeschedule_time,$afterschedule_time])
                            ->count();


            if($CheckScheduling > 0){
                if($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.request_scheduled')], 500);
                }else{
                    return redirect('dashboard')->with('flash_error', 'Already request is Scheduled on this time.');
                }
            }

        }

        $distance = Setting::get('provider_search_radius', '10');
        $latitude = $request->s_latitude;
        $longitude = $request->s_longitude;
        $service_type = $request->service_type;

        if ($request->child_seat == 1) {
        $Providers = Provider::with('service')
            ->select(DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"),'id')
            ->where('status', 'approved')
            ->where('child_seat', 1)
            ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
            ->whereHas('service', function($query) use ($service_type){
                        $query->where('status','active');
                        $query->where('service_type_id',$service_type); 
                    })
            ->orderBy('distance','asc')
            ->get();
        }elseif ($request->pet_allowed == 1) {
        $Providers = Provider::with('service')
            ->select(DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"),'id')
            ->where('status', 'approved')
            ->where('pet_allowed', 1)
            ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
            ->whereHas('service', function($query) use ($service_type){
                        $query->where('status','active');
                        $query->where('service_type_id',$service_type); 
                    })
            ->orderBy('distance','asc')
            ->get();
        }else{

        $Providers = Provider::with('service')
            ->select(DB::Raw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) AS distance"),'id')
            ->where('status', 'approved')
            ->whereRaw("(6371 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
            ->whereHas('service', function($query) use ($service_type){
                        $query->where('status','active');
                        $query->where('service_type_id',$service_type); 
                    })
            ->orderBy('distance','asc')
            ->get();
        }

    // dd($Providers);
        // List Providers who are currently busy and add them to the filter list.

        if(count($Providers) == 0) {
            if($request->ajax()) {
                // Push Notification to User
                return response()->json(['error' => trans('api.ride.no_providers_found')], 422); 
            }else{
                return back()->with('flash_success', 'No Providers Found! Please try again.');
            }
        }

        try{

             if($request->service_required=="rental"){
               
                $details = "https://maps.googleapis.com/maps/api/directions/json?origin=".$request->s_latitude.",".$request->s_longitude."&destination=".$request->s_latitude.",".$request->s_longitude."&mode=driving&key=".Setting::get('map_key');

             }else{
            
                $details = "https://maps.googleapis.com/maps/api/directions/json?origin=".$request->s_latitude.",".$request->s_longitude."&destination=".$request->d_latitude.",".$request->d_longitude."&mode=driving&key=".Setting::get('map_key');

            } 

            // if($request->service_required=="none"){


            //     $service_type_id = $request->service_type;
            //     $kilometer       = $request->distance;
            //     $geo_fencing_id  = $this->poly_check_new((round($request->s_latitude,6)),(round($request->s_longitude,6)));


            //        if($geo_fencing_id != 0)
            //         { 
            //             // $geo_fencing_service_type = GeoFencing::with(
            //             //     ['service_geo_fencing' =>  function ($query) use ($service_type_id) {
            //             //             $query->where('service_type_id',$service_type_id);
            //             //    } ])->whereid($geo_fencing_id)->first(); 
            //             $D_geo_fencing_id=$this->poly_check_new((round($request->d_latitude,6)),(round($request->d_longitude,6)));


            //             // if( $geo_fencing_service_type->service_geo_fencing->city_limits < $kilometer)
            //             if( $D_geo_fencing_id == 0)
            //             { 
                            
            //                 if($request->ajax()) {
                              
            //                     return response()->json(['error' => 'One-way fares are not available for this route.You can still travel one way, round trip fares apply.'], 422); 
            //                 }else{
            //                     return back()->with('flash_error', 'One-way fares are not available for this route.You can still travel one way, round trip fares apply.');
            //                 }

            //             }
            //         } 



            // }

            // if($request->service_required=="outstation"){


            //     $service_type_id = $request->service_type;
            //     $kilometer       = $request->distance;
            //     $geo_fencing_id  = $this->poly_check_new((round($request->s_latitude,6)),(round($request->s_longitude,6)));


            //        if($geo_fencing_id != 0)
            //         { 
            //             // $geo_fencing_service_type = GeoFencing::with(
            //             //     ['service_geo_fencing' =>  function ($query) use ($service_type_id) {
            //             //             $query->where('service_type_id',$service_type_id);
            //             //    } ])->whereid($geo_fencing_id)->first(); 
            //              $D_geo_fencing_id=$this->poly_check_new((round($request->d_latitude,6)),(round($request->d_longitude,6)));

            //             // if( $geo_fencing_service_type->service_geo_fencing->city_limits > $kilometer)
            //             if( $D_geo_fencing_id == 0)
            //             { 
                            
            //                 if($request->ajax()) {
                              
            //                     return response()->json(['error' => 'Outstation fares are not available for this route.You can still travel Normal Ride.'], 422); 
            //                 }else{
            //                     return back()->with('flash_error', 'Outstation fares are not available for this route.You can still travel Normal Ride.');
            //                 }

            //             }
            //         } 



            // }



            

            $json = curl($details);

            $details = json_decode($json, TRUE);

            $route_key = $details['routes'][0]['overview_polyline']['points'];

            $UserRequest = new UserRequests;
            $UserRequest->booking_id = Helper::generate_booking_id();
         

            $UserRequest->user_id = Auth::user()->id;
            
            if((Setting::get('manual_request',0) == 0) && (Setting::get('broadcast_request',0) == 0)){
                $UserRequest->current_provider_id = $Providers[0]->id;
            }else{
                $UserRequest->current_provider_id = 0;
            }

            $UserRequest->service_type_id = $request->service_type;
            $UserRequest->rental_hours = $request->rental_hours;
            $UserRequest->payment_mode = $request->payment_mode;

            $UserRequest->travel_time = $request->minute;
            
            if($request->has('schedule_date') && $request->has('schedule_time')){
                $UserRequest->status = 'SCHEDULES'; 
            }
            else {
                $UserRequest->assigned_at = Carbon::now();
                $UserRequest->status = 'SEARCHING'; 
            }

            if(isset($request->invoice_email))
                $UserRequest->invoice_email = $request->invoice_email; 
          
            

            $UserRequest->s_address = $request->s_address ? : "";
            $UserRequest->d_address = $request->d_address ? : "";

            $UserRequest->s_latitude = $request->s_latitude;
            $UserRequest->s_longitude = $request->s_longitude;
            $UserRequest->child_seat = $request->child_seat;
            $UserRequest->pet_allowed = $request->pet_allowed;

            if($request->service_required == 'rental'){
                $d_latitude = $request->s_latitude; 
                $d_longitude = $request->s_longitude; 
            }
            else{
                $d_latitude = $request->d_latitude;
                $d_longitude = $request->d_longitude;  
            }

            $UserRequest->d_latitude = $d_latitude;
            $UserRequest->d_longitude = $d_longitude;
            $UserRequest->distance = $request->distance;

            if(Auth::user()->wallet_balance > 0){
                $UserRequest->use_wallet = $request->use_wallet ? : 0;
            }

            if(Setting::get('track_distance', 0) == 1){
                $UserRequest->is_track = "YES";
            }

            $UserRequest->otp = mt_rand(1000 , 9999);

            // $UserRequest->assigned_at = Carbon::now();
            $UserRequest->route_key = $route_key;

            if($request->service_required == 'normal') 
                $service_required = 'none'; 
            else 
                $service_required = $request->service_required; 

           $UserRequest->service_required = $service_required;
           //Insert geo fencing id
            if($geo_check!=0)
            {
                $UserRequest->geo_fencing_id = $geo_check;
            }
 
            if($Providers->count() <= Setting::get('surge_trigger') && $Providers->count() > 0){
                $UserRequest->surge = 1;
            }

            if($request->has('schedule_date') && $request->has('schedule_time')){
                $UserRequest->schedule_at = date("Y-m-d H:i:s",strtotime("$request->schedule_date $request->schedule_time"));
                $UserRequest->is_scheduled = 'YES';
                $UserRequest->type = 'schedule';
            }

            if($request->service_required=="outstation"){

                if($request->has('leave')){

                    $UserRequest->status = 'SCHEDULES'; 

                    $UserRequest->schedule_at = date("Y-m-d H:i:s",strtotime("$request->leave"));
                    $UserRequest->is_scheduled = 'YES';
                    $UserRequest->type = 'schedule';

                
                    $UserRequest->out_leave = $request->leave;
                    $UserRequest->out_return = $request->return;
                    $UserRequest->day = $request->day;
                }

            }

             if((Setting::get('manual_request',0) == 0) && (Setting::get('broadcast_request',0) == 0)){
                Log::info('New Request id : '. $UserRequest->id .' Assigned to provider : '. $UserRequest->current_provider_id);
                $voip = true;
                (new SendPushNotification)->IncomingRequest($Providers[0]->id ,$voip); 

// \Log::info('sdsdsdsd');
// // Put your device token here (without spaces):


//       $deviceToken = '8ae47b30b000c2fc46e274aa494cd3f7e78b7185d1c293fddede2630d313264f';
// //


// // Put your private key's passphrase here:
// $passphrase = 'apple';

// // Put your alert message here:
// $message = 'My first push notification!';



// $ctx = stream_context_create();
// stream_context_set_option($ctx, 'ssl', 'local_cert', app_path().'/apns/provider/KalVoip.pem');
// stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

// // Open a connection to the APNS server
// $fp = stream_socket_client(
// //  'ssl://gateway.push.apple.com:2195', $err,
//     'ssl://gateway.sandbox.push.apple.com:2195', $err,
//     $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

// if (!$fp)
//     exit("Failed to connect: $err $errstr" . PHP_EOL);

// // echo 'Connected to APNS' . PHP_EOL;
// // Create the payload body

// $body['aps'] = array(
//                      'content-available'=> 1,
//                      'alert' => $message,
//                      'sound' => 'default',
//                      'badge' => 0,
//                      );



// // Encode the payload as JSON

// $payload = json_encode($body);

// // Build the binary notification
// $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

// // Send it to the server
// $result = fwrite($fp, $msg, strlen($msg));

// if (!$result)
// {
// \Log::info('not working');
// }
// else
// {
// \Log::info('dinesh voip token push');

// }

// // Close the connection to the server
// fclose($fp);



            }

            $UserRequest->save();


           

            // update payment mode 

            User::where('id',Auth::user()->id)->update(['payment_mode' => $request->payment_mode]);

            if($request->has('card_id')){

                Card::where('user_id',Auth::user()->id)->update(['is_default' => 0]);
                Card::where('card_id',$request->card_id)->update(['is_default' => 1]);
            }

            if($UserRequest->status != 'SCHEDULES'){
                if(Setting::get('manual_request',0) == 0){
                    foreach ($Providers as $key => $Provider) {

                        if(Setting::get('broadcast_request',0) == 1){
                           (new SendPushNotification)->IncomingRequest($Provider->id); 
                        }

                        $Filter = new RequestFilter;
                        // Send push notifications to the first provider
                        // incoming request push to provider
                        
                        $Filter->request_id = $UserRequest->id;
                        $Filter->provider_id = $Provider->id; 
                        $Filter->save();
                    }
                }
            }

            if($request->ajax()) {
                return response()->json([
                        'message' => 'New request Created!',
                        'request_id' => $UserRequest->id,
                        'current_provider' => $UserRequest->current_provider_id,
                    ]);
            }else{
                return redirect('dashboard');
            }

        } catch (Exception $e) { 
            \Log::info($e);
            if($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            }else{
                return back()->with('flash_error', 'Something went wrong while sending request. Please try again.');
            }
        }
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function cancel_request(Request $request) {

        $this->validate($request, [
            'request_id' => 'required|numeric|exists:user_requests,id,user_id,'.Auth::user()->id,
        ]);

        try{

            $UserRequest = UserRequests::findOrFail($request->request_id);

            if($UserRequest->status == 'CANCELLED')
            {
                if($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.already_cancelled')], 500); 
                }else{
                    return back()->with('flash_error', 'Request is Already Cancelled!');
                }
            }

            if(in_array($UserRequest->status, ['SEARCHING','STARTED','ARRIVED','SCHEDULES','SCHEDULED'])) {

                if($UserRequest->status != 'SEARCHING'){
                    $this->validate($request, [
                        'cancel_reason'=> 'max:255',
                    ]);
                }

                $UserRequest->status = 'CANCELLED';
                $UserRequest->cancel_reason = $request->cancel_reason;
                $UserRequest->cancelled_by = 'USER';
                $UserRequest->save();

                // user cancel ride tom push all provider side

                $Request_filter = RequestFilter::where('request_id', $UserRequest->id)->get();
                
                foreach($Request_filter as $filter)
                {
                    $user_name = Auth::user()->first_name;
                (new SendPushNotification)->RideCancelledAllProviders($filter,$user_name);

                }

                RequestFilter::where('request_id', $UserRequest->id)->delete();

                if($UserRequest->status != 'SCHEDULES'){

                    if($UserRequest->provider_id != 0){

                        ProviderService::where('provider_id',$UserRequest->provider_id)->update(['status' => 'active']);

                    }
                }

                 // Send Push Notification to User
                (new SendPushNotification)->UserCancellRide($UserRequest);

                if($request->ajax()) {
                    return response()->json(['message' => trans('api.ride.request_cancelled')]); 
                }else{
                    return redirect('dashboard')->with('flash_success','Request Cancelled Successfully');
                }

            } else {
                if($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.already_onride')], 500); 
                }else{
                    return back()->with('flash_error', 'Service Already Started!');
                }
            }
        }

        catch (ModelNotFoundException $e) {
            if($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')]);
            }else{
                return back()->with('flash_error', 'No Request Found!');
            }
        }

    }

    /**
     * Show the request status check.
     *
     * @return \Illuminate\Http\Response
     */

    public function request_status_check() {

        try{
            $check_status = ['CANCELLED', 'SCHEDULED', 'SCHEDULES'];

            $UserRequests = UserRequests::UserRequestStatusCheck(Auth::user()->id, $check_status)
                                        ->get()
                                        ->toArray();
                                        

            $search_status = ['SEARCHING','SCHEDULED','SCHEDULES'];
            $UserRequestsFilter = UserRequests::UserRequestAssignProvider(Auth::user()->id,$search_status)->get(); 

             //Log::info($UserRequestsFilter);

            $Timeout = Setting::get('provider_select_timeout', 180);

            if(!empty($UserRequestsFilter)){
                for ($i=0; $i < sizeof($UserRequestsFilter); $i++) {
                    $ExpiredTime = $Timeout - (time() - strtotime($UserRequestsFilter[$i]->assigned_at));
                    if($UserRequestsFilter[$i]->status == 'SEARCHING' && $ExpiredTime < 0) {
                        $Providertrip = new TripController();
                        $Providertrip->assign_next_provider($UserRequestsFilter[$i]->id);
                    }else if($UserRequestsFilter[$i]->status == 'SEARCHING' && $ExpiredTime > 0){
                        break;
                    }
                }
            }

            return response()->json(['data' => $UserRequests , 'sos' => Setting::get('sos_number', '911'), 'cash' => Setting::get('CASH'), 'card' => Setting::get('CARD'), 'stripe_secret_key' => Setting::get('stripe_secret_key'), 'stripe_publishable_key' => Setting::get('stripe_publishable_key')]);

        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */


    public function rate_provider(Request $request) {

        $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id,user_id,'.Auth::user()->id,
                'rating' => 'required|integer|in:1,2,3,4,5',
                'comment' => 'max:255',
            ]);
    
        $UserRequests = UserRequests::where('id' ,$request->request_id)
                ->where('status' ,'COMPLETED')
                ->where('paid', 0)
                ->first();

        if ($UserRequests) {
            if($request->ajax()){
                return response()->json(['error' => trans('api.user.not_paid')], 500);
            } else {
                return back()->with('flash_error', 'Service Already Started!');
            }
        }

        try{

            $UserRequest = UserRequests::findOrFail($request->request_id);
            
            if($UserRequest->rating == null) {
                UserRequestRating::create([
                        'provider_id' => $UserRequest->provider_id,
                        'user_id' => $UserRequest->user_id,
                        'request_id' => $UserRequest->id,
                        'user_rating' => $request->rating,
                        'user_comment' => $request->comment,
                    ]);
            } else {
                $UserRequest->rating->update([
                        'user_rating' => $request->rating,
                        'user_comment' => $request->comment,
                    ]);
            }

            $UserRequest->user_rated = 1;
            $UserRequest->save();

            $average = UserRequestRating::where('provider_id', $UserRequest->provider_id)->avg('user_rating');

            Provider::where('id',$UserRequest->provider_id)->update(['rating' => $average]);

            // Send Push Notification to Provider 
            if($request->ajax()){
                return response()->json(['message' => trans('api.ride.provider_rated')]); 
            }else{
                return redirect('dashboard')->with('flash_success', 'Driver Rated Successfully!');
            }
        } catch (Exception $e) {

            if($request->ajax()){
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            }else{
                return back()->with('flash_error', 'Something went wrong');
            }
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */


    public function modifiy_request(Request $request) {

        $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id,user_id,'.Auth::user()->id,
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'address' => 'required'
            ]);

        try{

            $UserRequest = UserRequests::findOrFail($request->request_id);
            $UserRequest->d_latitude = $request->latitude?:$UserRequest->d_latitude;
            $UserRequest->d_longitude = $request->longitude?:$UserRequest->d_longitude;
            $UserRequest->d_address =  $request->address?:$UserRequest->d_address;
            $UserRequest->save();

            // Send Push Notification to Provider 
            if($request->ajax()){
                return response()->json(['message' => trans('api.ride.request_modify_location')]); 
            }else{
                return redirect('dashboard')->with('flash_success', 'User Changed Destination Address Successfully!');
            }
        } catch (Exception $e) {
            if($request->ajax()){
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            }else{
                return back()->with('flash_error', 'Something went wrong');
            }
        }

    } 


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function trips() {
    
        try{
            $UserRequests = UserRequests::UserTrips(Auth::user()->id)->get();
            if(!empty($UserRequests)){
                $map_icon = asset('asset/img/marker-start.png');
                foreach ($UserRequests as $key => $value) {
                    $UserRequests[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?".
                            "autoscale=1".
                            "&size=600x300".
                            "&maptype=terrian".
                            "&format=png".
                            "&visual_refresh=true".
                            "&markers=icon:".$map_icon."%7C".$value->s_latitude.",".$value->s_longitude.
                            "&markers=icon:".$map_icon."%7C".$value->d_latitude.",".$value->d_longitude.
                            "&path=color:0x191919|weight:3|enc:".$value->route_key.
                            "&key=".Setting::get('map_key');
                }
            }
            return $UserRequests;
        }

        catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

       /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function estimated_fare(Request $request){
     
        $this->validate($request,[
                's_latitude' => 'required|numeric',
                's_longitude' => 'required|numeric',
               // 'd_latitude' => 'required|numeric',
                //'d_longitude' => 'required|numeric',
                'service_type' => 'required|numeric|exists:service_types,id',
            ]);

        try{

            $non_geo_price = 0;

           if($request->service_required=="rental"){
               
             $details = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$request->s_latitude.",".$request->s_longitude."&destinations=".$request->s_latitude.",".$request->s_longitude."&mode=driving&sensor=false&key=".Setting::get('map_key');

           }else{
            
            $details = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$request->s_latitude.",".$request->s_longitude."&destinations=".$request->d_latitude.",".$request->d_longitude."&mode=driving&sensor=false&key=".Setting::get('map_key');

           } 

            $json = curl($details);

           $details = json_decode($json, TRUE);

            $package_hour=0;
            $leave = 0;
            $return = 0;
            $day = 0;
            $time_charge = 0;
            if(isset($details['rows'][0]['elements']) && isset($details['rows'][0]['elements'][0])&& isset($details['rows'][0]['elements'][0]['distance']))
            {
            $meter = $details['rows'][0]['elements'][0]['distance']['value'];
            $time = $details['rows'][0]['elements'][0]['duration']['text'];
            $seconds = $details['rows'][0]['elements'][0]['duration']['value'];

            $kilometer = round($meter/1000);
            $minutes = round($seconds/60);

            $rental_hour = round($minutes/60);
            $rental = ceil($rental_hour);

//             if($request->rental_hours!=null){

//                 $package = ServiceRentalHourPackage::where('id',$request->rental_hours)->first();
//                if($package){
//                 $package_hour = $package->hour;
// //dd($package);
//                 if($rental_hour > $package->hour){ 

//                      $rental = ceil($rental_hour);
//                 }else{
//                     $rental = ceil($package->hour);
//                 }
//             }
//             else
//             {
//                 $rental = ceil($rental_hour);
//             }


//             }
            $fixed_price_only = ServiceType::findOrFail($request->service_type);
            $geo_fencing=$this->poly_check_new((round($request->s_latitude,6)),(round($request->s_longitude,6)));

            if($geo_fencing)
            {
                $service_type_id = $request->service_type;
                $geo_fencing_service_type = GeoFencing::with(
                    ['service_geo_fencing' =>  function ($query) use ($service_type_id) {
                            $query->where('service_type_id',$service_type_id);
                   } ])->whereid($geo_fencing)->first(); 
                $service_type = $geo_fencing_service_type->service_geo_fencing;
 
            //////// ------------Peak Time Calculation--------------------//////////

                 //// peak Time Variable

                $peak_time = 0; 
                $non_peak_time = 0;



                //// peak Time Variable
                $current_date = Carbon::now();

                $start_time = date('h:i A', strtotime($current_date));
      
                $time_check_start = Time::where('from_time', '<=' ,$start_time)->where('to_time', '>=' ,$start_time)->first(); 
                // dd($time_check_start);
                $time_charge=$minutes * $geo_fencing_service_type->service_geo_fencing->minute;

                if(count($time_check_start)==1){  

                  $timeprice = TimePrice::where('service_id',$request->service_type)->where('time_id',$time_check_start->id)->first();

                  if($timeprice){

                    $time_charge = $minutes * $timeprice->peak_price;

                  }

                } 


                $total_peak_minute_and_non_peak_charge = $time_charge; 

             //////// -----------------Peak Time Calculation ------------ /////////



            }
            else
            {
                $service_type = ServiceType::findOrFail($request->service_type);
            }
 
            

            $tax_percentage = Setting::get('tax_percentage');
            $commission_percentage = Setting::get('commission_percentage');
            

            $current_time = Carbon::now();

            $start_time = date('h:i A', strtotime($current_time));

            $time_check_start = Time::where('from_time', '<=' ,$start_time)->where('to_time', '>=' ,$start_time)->first();
            $travel_time = $minutes;

           
            if($geo_fencing)
            {
                $price = $fixed_price_only->fixed;
            }
            else
            {
                $price = $fixed_price_only->fixed;
            }

            $hour = $service_type->hour;
            if($fixed_price_only->calculator == 'MIN') {
                $price += $service_type->minute * $minutes;
            } else if($fixed_price_only->calculator == 'HOUR') {
                $price += $service_type->minute * 60;
            } else if($fixed_price_only->calculator == 'DISTANCE') {
                $kilmin =$kilometer-$service_type->distance>0?($kilometer-$service_type->distance):0;
                $price += ($kilmin * $service_type->price);
            } else if($fixed_price_only->calculator == 'DISTANCEMIN') {

                $price += ((($kilometer-$service_type->distance>0?($kilometer-$service_type->distance):0) * $service_type->price)) + ($service_type->minute * $minutes);

            } else if($fixed_price_only->calculator == 'DISTANCEHOUR') {
                $kilmin = $kilometer - $service_type->distance;
                $price += ($kilmin * $service_type->price) + ($rental * $hour);
            } else {
                $kilmin = $kilometer - $service_type->distance;
                $price += ($kilmin * $service_type->price);
            } 

            if($request->service_required=="rental"){

                $package = ServiceRentalHourPackage::where('id',$request->rental_hours)->first();
                $price = $package->price;

            }elseif($request->service_required=="outstation"){

                        $begin = new DateTime( $request->leave );
                        $end   = new DateTime( $request->return );

                        $total_days =  $end->diff($begin)->format("%a")+1;

//dd($total_days);
                         $leave = $request->leave;
                         $return = $request->return;
                         $day = $request->day;  

                if($day == 'round')
                {   
                        $kilometer =floatval($kilometer);
                        $outstation_base_km =floatval(Setting::get('outstation_base_km'));  
                        if($kilometer < $outstation_base_km)
                        {  
                            $outstation_base_km = Setting::get('outstation_base_km'); 
                            $kilometer = $outstation_base_km * $total_days;
                            $price = (($kilometer * $fixed_price_only->roundtrip_km) + ($fixed_price_only->outstation_driver * $total_days));
                        }
                        else
                        {
                            $kilometer = $kilometer * $total_days;
                            $price = (($kilometer * $fixed_price_only->roundtrip_km) + ($fixed_price_only->outstation_driver * $total_days));
                        }
                    
                }
                else
                {
                    $kilometer =floatval($kilometer);
                    $outstation_base_km =floatval(Setting::get('outstation_base_km'));  
                    if($kilometer < $outstation_base_km)
                    { 
                        $outstation_base_km = Setting::get('outstation_base_km'); 
                        $kilometer = $outstation_base_km * $total_days;
                        $price = (($kilometer * $fixed_price_only->outstation_km) + ($fixed_price_only->outstation_driver * $total_days));
                    }
                    else
                    {
                        $kilometer = $kilometer * $total_days;
                        $price = (($kilometer * $fixed_price_only->outstation_km) + ($fixed_price_only->outstation_driver * $total_days));
                    }
                }

            }

            if(count($time_check_start)==1){

                $timeprice = TimePrice::where('time_id',$time_check_start->id)->first();

                if(count($timeprice)==1){

                    $price+=$timeprice->peak_price * $minutes;

                }

                


            }


            $tax_price = ( $tax_percentage/100 ) * $price;
            $total = $price + $tax_price;
            $ActiveProviders = ProviderService::AvailableServiceProvider($request->service_type)->get()->pluck('provider_id');

            $distance = Setting::get('provider_search_radius', '10');
            $latitude = $request->s_latitude;
            $longitude = $request->s_longitude;

            $Providers = Provider::whereIn('id', $ActiveProviders)
                ->where('status', 'approved')
                ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                ->get();

            $surge = 0;
            
            if($Providers->count() <= Setting::get('surge_trigger') && $Providers->count() > 0){
                $surge_price = (Setting::get('surge_percentage')/100) * $total;
                $total += $surge_price;
                $surge = 1;
            }

            $city_limits = 0;
            $service_type_id =$request->service_type;
            $geo_fencing_id=$this->poly_check_new((round($request->s_latitude,6)),(round($request->s_longitude,6)));
           
            if($geo_fencing_id != 0)
            { 
                // $geo_fencing_service_type = GeoFencing::with(
                //     ['service_geo_fencing' =>  function ($query) use ($service_type_id) {
                //             $query->where('service_type_id',$service_type_id);
                //    } ])->whereid($geo_fencing_id)->first(); 

                $D_geo_fencing_id=$this->poly_check_new((round($request->d_latitude,6)),(round($request->d_longitude,6)));

                // if(!empty($geo_fencing_service_type->service_geo_fencing->city_limits) && $geo_fencing_service_type->service_geo_fencing->city_limits < $kilometer)
                if($D_geo_fencing_id ==0)
                { 
                    if($request->service_required =='outstation'){

                        $city_limits = 1; 
                    }
                    else if($request->service_required =='normal') 
                    { 
                        $city_limits = 1; 
                    }
                }
                else
                {
                    if($request->service_required =='outstation'){

                        $kilometer =floatval($kilometer);
                        $outstation_base_km =floatval(Setting::get('outstation_base_km'));  
                        if($kilometer < $outstation_base_km)
                        { 
                            $city_limits = 1;
                        }
                    }
                }
           


           $check = $this->poly_check_request((round($request->d_latitude,6)),(round($request->d_longitude,6)));

               if($check=='no')
               {
                    $non_geo_price = $geo_fencing_service_type->service_geo_fencing->non_geo_price;

               }
           


            }   


            $rental_hour_package = ServiceRentalHourPackage::whereservice_type_id($request->service_type)->get(); 
            
            if($request->rental_hours)
            {
                $rental_package = ServiceRentalHourPackage::findOrFail($request->rental_hours);
            }
            else
            {
                $rental_package = '';
            }

            if($request->service_required=="rental"){

                $total = $rental_package->price;
            }
            $time_package = TimePrice::with('times')->whereservice_id($request->service_type)->get();

            /*
            * Reported by Jeya, previously it was hardcoded. we have changed as based on surge percentage.
            */ 
            $surge_percentage = 1+(Setting::get('surge_percentage')/100)."X";

            return response()->json([
                    'estimated_fare' => round($total,2), 
                    'distance' => $kilometer,
                    'minute' => $minutes,
                    'time' => $time,
                    'surge' => $surge,
                    'surge_value' => $surge_percentage,
                    'tax_price' => $tax_price,
                    'base_price' => $fixed_price_only->fixed,
                    'service_type'=> $fixed_price_only->id ,
                    'wallet_balance' => Auth::user()->wallet_balance,
                    'city_limits' => $city_limits,
                    'service_required'=>$request->service_required,
                    'rental_hours'=>$package_hour,
                    'leave'=>$leave,
                    'return'=>$return,
                    'day'=>$day,
                    'limit_message' => Setting::get('limit_message'),
                    'non_geo_price' =>  $non_geo_price,
                    'rental_hour_package' =>  $rental_hour_package, 
                    'time_package' =>  $time_package, 
                    'rental_package' =>  $rental_package,
            ]);
        } else{
            return response()->json(['error' => 'No Service Found.'], 500);
        }

        } catch(Exception $e) {

          
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function trip_details(Request $request) {

         $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id',
            ]);
    
        try{
            $UserRequests = UserRequests::UserTripDetails(Auth::user()->id,$request->request_id)->get();
            if(!empty($UserRequests)){
                $map_icon = asset('asset/img/marker-start.png');
                foreach ($UserRequests as $key => $value) {
                    $UserRequests[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?".
                            "autoscale=1".
                            "&size=320x130".
                            "&maptype=terrian".
                            "&format=png".
                            "&visual_refresh=true".
                            "&markers=icon:".$map_icon."%7C".$value->s_latitude.",".$value->s_longitude.
                            "&markers=icon:".$map_icon."%7C".$value->d_latitude.",".$value->d_longitude.
                            "&path=color:0x191919|weight:3|enc:".$value->route_key.
                            "&key=".Setting::get('map_key');
                }
            }
            return $UserRequests;
        }

        catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    /**
     * get all promo code.
     *
     * @return \Illuminate\Http\Response
     */

    public function promocodes() {
        try{
            //$this->check_expiry();

            return PromocodeUsage::Active()
                    ->where('user_id', Auth::user()->id)
                    ->with('promocode')
                    ->get();

        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    } 


    /*public function check_expiry(){
        try{
            $Promocode = Promocode::all();
            foreach ($Promocode as $index => $promo) {
                if(date("Y-m-d") > $promo->expiration){
                    $promo->status = 'EXPIRED';
                    $promo->save();
                    PromocodeUsage::where('promocode_id', $promo->id)->update(['status' => 'EXPIRED']);
                }else{
                    PromocodeUsage::where('promocode_id', $promo->id)
                            ->where('status','<>','USED')
                            ->update(['status' => 'ADDED']);

                    PromocodePassbook::create([
                            'user_id' => Auth::user()->id,
                            'status' => 'ADDED',
                            'promocode_id' => $promo->id
                        ]);
                }
            }
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }*/


    /**
     * add promo code.
     *
     * @return \Illuminate\Http\Response
     */

    public function add_promocode(Request $request) {

        $this->validate($request, [
                'promocode' => 'required|exists:promocodes,promo_code',
            ]);

        try{

            $find_promo = Promocode::where('promo_code',$request->promocode)->first();

            if( date("Y-m-d") < date('Y-m-d',strtotime($find_promo->from_date))){

                if($request->ajax()){

                    return response()->json([
                        'message' => trans('api.promocode_not_activated'), 
                        'code' => 'promocode_not_activated'
                    ]);

                }else{
                    return back()->with('flash_error', trans('api.promocode_not_activated').' ');
                }

            }elseif($find_promo->status == 'EXPIRED' || (date("Y-m-d") > $find_promo->expiration)){

                if($request->ajax()){

                    return response()->json([
                        'message' => trans('api.promocode_expired'), 
                        'code' => 'promocode_expired'
                    ]);

                }else{
                    return back()->with('flash_error', trans('api.promocode_expired'));
                }

            }elseif(PromocodeUsage::where('promocode_id',$find_promo->id)->where('user_id', Auth::user()->id)->whereIN('status',['ADDED','USED'])->count() > 0){

                if($request->ajax()){

                    return response()->json([
                        'message' => trans('api.promocode_already_in_use'), 
                        'code' => 'promocode_already_in_use'
                        ]);

                }else{
                    return back()->with('flash_error', 'Promocode Already in use');
                }

            }else{
                $usage_count = PromocodeUsage::where('promocode_id',$find_promo->id)->whereIN('status',['ADDED','USED'])->count();
                if($find_promo->use_count <= $usage_count)
                {
                    if($request->ajax()){

                        return response()->json([
                                'message' => trans('api.promocode_limit_exist') ,
                                'code' => 'promocode_limit_exist'
                             ]); 

                    }else{
                        return back()->with('flash_success', trans('api.promocode_limit_exist'));
                    }
                }
                else
                {
                    $promo = new PromocodeUsage;
                    $promo->promocode_id = $find_promo->id;
                    $promo->user_id = Auth::user()->id;
                    $promo->status = 'ADDED';
                    $promo->save();
                    
                    $count_id = PromocodePassbook::where('promocode_id' , $find_promo->id)->count();
                    //dd($count_id); 
                    if($count_id == 0){

                       PromocodePassbook::create([
                                'user_id' => Auth::user()->id,
                                'status' => 'ADDED',
                                'promocode_id' => $find_promo->id
                            ]);
                    }
                    if($request->ajax()){

                        return response()->json([
                                'message' => trans('api.promocode_applied') ,
                                'code' => 'promocode_applied'
                             ]); 

                    }else{
                        return back()->with('flash_success', trans('api.promocode_applied'));
                    }
                } 
            }

        }

        catch (Exception $e) {
            if($request->ajax()){
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            }else{
                return back()->with('flash_error', 'Something Went Wrong');
            }
        }

    } 

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function upcoming_trips() {
    
        try{
            $UserRequests = UserRequests::UserUpcomingTrips(Auth::user()->id)->get();
            if(!empty($UserRequests)){
                $map_icon = asset('asset/img/marker-start.png');
                foreach ($UserRequests as $key => $value) {
                    $UserRequests[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?".
                            "autoscale=1".
                            "&size=600x300".
                            "&maptype=terrian".
                            "&format=png".
                            "&visual_refresh=true".
                            "&markers=icon:".$map_icon."%7C".$value->s_latitude.",".$value->s_longitude.
                            "&markers=icon:".$map_icon."%7C".$value->d_latitude.",".$value->d_longitude.
                            "&path=color:0x000000|weight:3|enc:".$value->route_key.
                            "&key=".Setting::get('map_key');
                }
            }
            return $UserRequests;
        }

        catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function upcoming_trip_details(Request $request) {

         $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id',
            ]);
    
        try{
            $UserRequests = UserRequests::UserUpcomingTripDetails(Auth::user()->id,$request->request_id)->get();
            if(!empty($UserRequests)){
                $map_icon = asset('asset/img/marker-start.png');
                foreach ($UserRequests as $key => $value) {
                    $UserRequests[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?".
                            "autoscale=1".
                            "&size=320x130".
                            "&maptype=terrian".
                            "&format=png".
                            "&visual_refresh=true".
                            "&markers=icon:".$map_icon."%7C".$value->s_latitude.",".$value->s_longitude.
                            "&markers=icon:".$map_icon."%7C".$value->d_latitude.",".$value->d_longitude.
                            "&path=color:0x000000|weight:3|enc:".$value->route_key.
                            "&key=".Setting::get('map_key');
                }
            }
            return $UserRequests;
        }

        catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
        }
    }


    /**
     * Show the nearby providers.
     *
     * @return \Illuminate\Http\Response
     */

    public function show_providers(Request $request) {

        $this->validate($request, [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'service' => 'numeric|exists:service_types,id',
            ]);

        try{

            $distance = Setting::get('provider_search_radius', '10');
            $latitude = $request->latitude;
            $longitude = $request->longitude;

            if($request->has('service')){

                $ActiveProviders = ProviderService::AvailableServiceProvider($request->service)
                                    ->get()->pluck('provider_id');

                $Providers = Provider::with('service')->whereIn('id', $ActiveProviders)
                    ->where('status', 'approved')
                    ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                    ->get();

            } else {

                $ActiveProviders = ProviderService::where('status', 'active')
                                    ->get()->pluck('provider_id');

                $Providers = Provider::with('service')->whereIn('id', $ActiveProviders)
                    ->where('status', 'approved')
                    ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                    ->get();
            }

        
            return $Providers;

        } catch (Exception $e) {
            if($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            }else{
                return back()->with('flash_error', 'Something went wrong while sending request. Please try again.');
            }
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
            
            $user = User::where('mobile' , $request->mobile)->first();
     if($user){
            $otp = mt_rand(100000, 999999);

            $user->otp = $otp;
            $user->save();

            $data['mobile'] = $user->country_code.$user->mobile;
            $data['message'] = 'Your verification Code is'.$otp;

           (new TwilioController)->sendSms($data);

            return response()->json([
                'message' => 'OTP sent to your mobile!',
                'user' => $user
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
                'id' => 'required|numeric|exists:users,id'

            ]);

        try{

            $User = User::findOrFail($request->id);
            // $UpdatedAt = date_create($User->updated_at);
            // $CurrentAt = date_create(date('Y-m-d H:i:s'));
            // $ExpiredAt = date_diff($UpdatedAt,$CurrentAt);
            // $ExpiredMin = $ExpiredAt->i;
            $User->password = bcrypt($request->password);
            $User->save();
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
     * help Details.
     *
     * @return \Illuminate\Http\Response
     */

    public function help_details(Request $request){

        try{

            if($request->ajax()) {
                return response()->json([
                    'contact_number' => Setting::get('contact_number',''), 
                    'contact_email' => Setting::get('contact_email','')
                     ]);
            }

        }catch (Exception $e) {
            if($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')]);
            }
        }
    }

    public function settings(Request $request)
    {
        $serviceType = ServiceType::select('id', 'name')->get();
        $settings = [
            'serviceTypes' => $serviceType,
            'api_key' => Setting::get('map_key',''),
            'android_api_key' => Setting::get('android_map_key',''),
            'ios_api_key' => Setting::get('ios_map_key',''),
            'stripe_secret_key' => Setting::get('stripe_secret_key',''),
            'stripe_publishable_key' => Setting::get('stripe_publishable_key',''),
            'stripe_currency' => Setting::get('stripe_currency',''),
        ];
        return response()->json($settings);        
       
    }

    /**
     * Show the wallet usage.
     *
     * @return \Illuminate\Http\Response
     */

    public function wallet_passbook(Request $request)
    {
        try{
            
            return WalletPassbook::where('user_id',Auth::user()->id)->orderBy('id','desc')->get();

        } catch (Exception $e) {
             return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }


    /**
     * Show the promo usage.
     *
     * @return \Illuminate\Http\Response
     */

    public function promo_passbook(Request $request)
    {
        try{
            
            return PromocodePassbook::where('user_id',Auth::user()->id)->with('promocode')->get();

        } catch (Exception $e) {
             
             return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function test(Request $request)
    {
         //$push =  (new SendPushNotification)->IncomingRequest($request->id); 
         $push = (new SendPushNotification)->Arrived($request->id);

         dd($push);
    }


    public function chat_push(Request $request)
    {
        $push = (new SendPushNotification)->sendPushToProvider($request->id,$request->message);
    }

     /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function pricing_logic($id)
    {
       //return $id;
       $logic = ServiceType::select('calculator')->where('id',$id)->first();
       return $logic;

    }

    public function fare(Request $request){

        \Log::info('Estimate', $request->all());
        $this->validate($request,[
                's_latitude' => 'required|numeric',
                's_longitude' => 'required|numeric',
                'd_latitude' => 'required|numeric',
                'd_longitude' => 'required|numeric',
                'service_type' => 'required|numeric|exists:service_types,id',
            ]);

        try{

            $details = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$request->s_latitude.",".$request->s_longitude."&destinations=".$request->d_latitude.",".$request->d_longitude."&mode=driving&sensor=false&key=".Setting::get('map_key');

             

            $json = curl($details);

            $details = json_decode($json, TRUE);


            $meter = $details['rows'][0]['elements'][0]['distance']['value'];
            $time = $details['rows'][0]['elements'][0]['duration']['text'];
            $seconds = $details['rows'][0]['elements'][0]['duration']['value'];
            $miles = round($meter/1000);
            $kilometer = round($miles * 0.621371);
            $minutes = round($seconds/60);


            $rental = ceil($request->rental_hours);

            $fixed_price_only = ServiceType::findOrFail($request->service_type);
            $geo_fencing=$this->poly_check_new((round($request->s_latitude,6)),(round($request->s_longitude,6)));

            if($geo_fencing)
            {
                $service_type_id = $request->service_type;
                $geo_fencing_service_type = GeoFencing::with(
                    ['service_geo_fencing' =>  function ($query) use ($service_type_id) {
                            $query->where('service_type_id',$service_type_id);
                   } ])->whereid($geo_fencing)->first(); 
                $service_type = $geo_fencing_service_type->service_geo_fencing;
            }
            else
            {
                $service_type = ServiceType::findOrFail($request->service_type);
            }

            $tax_percentage = Setting::get('tax_percentage');
            $commission_percentage = Setting::get('commission_percentage');
            // $service_type = ServiceType::findOrFail($request->service_type);
            
            // $price = $service_type->fixed;

            if($geo_fencing)
            {
                $price = $fixed_price_only->fixed;
            }
            else
            {
                $price = $fixed_price_only->fixed;
            }

            $hour = $service_type->hour;

            // if($service_type->calculator == 'MIN') {
            //     $price += $service_type->minute * $minutes;
            // } else if($service_type->calculator == 'HOUR') {
            //     $price += $service_type->minute * 60;
            // } else if($service_type->calculator == 'DISTANCE') {
            //     $price += ($kilometer * $service_type->price);
            // } else if($service_type->calculator == 'DISTANCEMIN') {
            //     $price += ($kilometer * $service_type->price) + ($service_type->minute * $minutes);
            // } else if($service_type->calculator == 'DISTANCEHOUR') {
            //     $price += ($kilometer * $service_type->price) + ($rental * $hour);
            // } else {
            //     $price += ($kilometer * $service_type->price);
            // }

            if($fixed_price_only->calculator == 'MIN') {
                $price += $service_type->minute * $minutes;
            } else if($fixed_price_only->calculator == 'HOUR') {
                $price += $service_type->minute * 60;
            } else if($fixed_price_only->calculator == 'DISTANCE') {
                $kilmin =$kilometer-$service_type->distance>0?($kilometer-$service_type->distance):0;
                $price += ($kilmin * $service_type->price);
            } else if($fixed_price_only->calculator == 'DISTANCEMIN') {

                $price += ((($kilometer-$service_type->distance>0?($kilometer-$service_type->distance):0) * $service_type->price)) + ($service_type->minute * $minutes);

            } else if($fixed_price_only->calculator == 'DISTANCEHOUR') {
                $kilmin = $kilometer - $service_type->distance;
                $price += ($kilmin * $service_type->price) + ($rental * $hour);
            } else {
                $kilmin = $kilometer - $service_type->distance;
                $price += ($kilmin * $service_type->price);
            } 

            $tax_price = ( $tax_percentage/100 ) * $price;
            $total = $price + $tax_price;



            $distance = Setting::get('provider_search_radius', '10');
            $latitude = $request->s_latitude;
            $longitude = $request->s_longitude;

           
            $surge = 0;


            $city_limits = 0;

            $required_service=0;

            $day = 0;

            $day =$request->day;


            $service_type_id =$request->service_type;
            $geo_fencing_id=$this->poly_check_new((round($request->s_latitude,6)),(round($request->s_longitude,6)));

           
            if($geo_fencing_id != 0)
            { 
                // $geo_fencing_service_type = GeoFencing::with(
                //     ['service_geo_fencing' =>  function ($query) use ($service_type_id) {
                //             $query->where('service_type_id',$service_type_id);
                //    } ])->whereid($geo_fencing_id)->first(); 
                
                $D_geo_fencing_id=$this->poly_check_new((round($request->d_latitude,6)),(round($request->d_longitude,6)));


                // if( $geo_fencing_service_type->service_geo_fencing->city_limits < $kilometer)
                if($D_geo_fencing_id == 0)
                { 
                    $city_limits = 1;

                    if($request->service_required !='normal'){

                        $required_service=1;

                    }
 
                }
            } 
            else
            {
                $city_limits = 1;
            }  
            
                $rental_hour_package = ServiceRentalHourPackage::whereservice_type_id($request->service_type)->get();

         
                return response()->json([
                    'estimated_fare' => round($total,2), 
                    'distance' => $kilometer,
                    'time' => $time,
                    'tax_price' => $tax_price,
                    'base_price' => $service_type->fixed,
                    'city_limits' => $city_limits,
                    'required_service'=>$required_service,
                    'geo_fencing_id' => $geo_fencing_id,
                    'limit_message' => Setting::get('limit_message'),
                    'rental_hour_package' => $rental_hour_package,
                ]);
            
            

        } catch(Exception $e) {



             return response()->json(['error' =>$e->getMessage()], 500);
        }
    }

    /**
     * Show the wallet usage.
     *
     * @return \Illuminate\Http\Response
     */

    /*public function check(Request $request)
    {

        $this->validate($request, [
                'name' => 'required',
                'age' => 'required',
                'work' => 'required',
            ]);
         return Work::create(request(['name', 'age' ,'work']));
    }*/



public function poly_check_new($s_latitude,$s_longitude)
{
    
           $range_data = GeoFencing::get();
           //dd($range_data);

           $yes = $no =  [];

           $longitude_x = $s_latitude;
           
           $latitude_y =  $s_longitude;
           if(count($range_data)!=0)
           {
           foreach($range_data as $ranges)
           {

               $vertices_x = $vertices_y = [];

              // $ranges  = Setting::get('service_range');
              
               $range_values = json_decode($ranges['ranges'],true);
               //dd($range_values);
               if($range_values!="")
               {
               foreach($range_values as $range ){

                   $vertices_x[] = $range['lat'];

                   $vertices_y[] = $range['lng'];

               }
    
               $points_polygon = count($vertices_x) - 1; 
               
                if (is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)){

                   $yes[] = $ranges['id'];
                }else{
                       $no[] = 0;
                }
               
            }

           }

           if(count($yes)!=0)
                {
                return $yes[0];
                }
                else
                {
                return 0;
                }
            }
            else
            {
            return 0;
            }
  }

public function poly_check_request($s_latitude,$s_longitude)
{
    
           $range_data = GeoFencing::get();
           //dd($range_data);

           $yes = $no =   [];

           $longitude_x = $s_latitude;
           
           $latitude_y =  $s_longitude;

    if(count($range_data)!=0){
           foreach($range_data as $ranges)
           {
            if(!empty($ranges)){
  
              // $ranges  = Setting::get('service_range');

                $vertices_x = $vertices_y = [];

                $range_values = json_decode($ranges['ranges'],true);
                //dd($range_values);
		if(count($range_values)>0){
                foreach($range_values as $range ){
                   $vertices_x[] = $range['lat'];
                   $vertices_y[] = $range['lng'];
                }
		}


               $points_polygon = count($vertices_x) - 1; 

                if (is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)){
                    $yes[] =$ranges['id'];
                }else{

                      $no[] = 0;
                }

            }

        
           }
        }

                if(count($yes)!=0)
                {
                return 'yes';
                }
                else
                {
                return 'no';
                }

  }



public function verify_otp(Request $request)
    {
            $this->validate($request, [
                'otp' => 'required|numeric|exists:user_devices,otp',
                'username' => 'required|numeric|exists:user_devices,mobile',
                'device_token' => 'required|exists:user_devices,device_token'
            ]);

            try{    
                $where =array(
                    'otp' => $request->otp,
                    'mobile' => $request->username,
                    'device_token' => $request->device_token

                );
               $device =  UserDevice::where($where)->first();
                
               if(count($device)!=0)
               {



                    $where1 = array
                    (
                    'mobile'=> $request->username,
                    'device_token' => $request->device_token,
                    'status' => 1
                    ); 


                    UserDevice::where($where1)->update(['current' => 0]);

                    $device->status = 1;
                    $device->current = 1;
                    $device->save();

                    $update=User::wheremobile($request->username)->first();

                    if($update){
                        $update->device_token= $request->device_token;
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
                    //      $json['status'] = true;
                    $response->setContent(json_encode($json));
                    return $response;

                  //  return response()->json(['status' => 'true' , 'message' => 'Otp verified Successfully']);
               } 
               else
               {
                    return response()->json(['status' => 'false' , 'message' => 'Otp is Invalid' ]);
               }

    

            }
            catch (Exception $e) {
               
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
            }

    }
     /**
     * Show the CC avanue payment for wallet
     *
     * @return \Illuminate\Http\Response
     */
    public function ccavanue_wallet(Request $request)
    {

        $tid=rand(10,10000000);

         /* All Required Parameters by your Gateway */
      
          $parameters = [
          
            'tid' => $tid,
            
            'order_id' => '123456',
            
            'amount' => $request->amount,
            
          ];
          
          // gateway = CCAvenue / PayUMoney / EBS / Citrus / InstaMojo / ZapakPay / Mocker
          
          $order = Indipay::gateway('CCAvenue')->prepare($parameters);
          return Indipay::process($order);

    }
    /**
     * Show the CC avanue payment for wallet
     *
     * @return \Illuminate\Http\Response
     */
    public function ccavanue_trip(Request $request)
    {
        $tid=rand(10,10000000);
         /* All Required Parameters by your Gateway */
      
          $parameters = [
          
            'tid' => $tid,
            
            'order_id' => '123456',
            
            'amount' => '1',
            
          ];
          
          // gateway = CCAvenue / PayUMoney / EBS / Citrus / InstaMojo / ZapakPay / Mocker
          
          $order = Indipay::gateway('CCAvenue')->prepare($parameters);
          return Indipay::process($order);

    }

    /**
     * Show the CC avanue payment for wallet response web
     *
     * @return \Illuminate\Http\Response
     */
    public function ccavanue_web_response(Request $request)
    { 
         // For default Gateway
        $response = Indipay::response($request);
        
        // For Otherthan Default Gateway
        $response = Indipay::gateway('CCAvenue')->response($request);

        if($response['order_status'] == 'Success')
        {
            $update=UserRequests::findOrFail($response['order_id']);
            $update->paid=1;
            $update->save();

            $updatepayment=UserRequestPayment::whererequest_id($response['order_id'])->first();
            $updatepayment->payment_id=$response['tracking_id'];
            $updatepayment->save();

            return redirect('dashboard')->with('flash_success', $response['order_status']);

        }
        else if($response['order_status'] == 'Aborted')
        {
            return redirect('dashboard')->with('flash_error', $response['order_status']);
        }
        else if($response['order_status'] == 'Failure')
        {
            return redirect('dashboard')->with('flash_error', $response['order_status']);
        }
        else 
        {
            return redirect('dashboard')->with('flash_error', $response['order_status']);
        }

        

    }
    /**
     * Show the CC avanue payment for wallet cancel response web
     *
     * @return \Illuminate\Http\Response
     */
    public function ccavanue_web_cancel_response(Request $request)
    {

          // For default Gateway
        $response = Indipay::response($request);
        
        // For Otherthan Default Gateway
        $response = Indipay::gateway('CCAvenue')->response($request);

        if($response['order_status'] == 'Aborted')
        {
            return redirect('dashboard')->with('flash_error', $response['order_status']);
        }
        else if($response['order_status'] == 'Failure')
        {
            return redirect('dashboard')->with('flash_error', $response['order_status']);
        }
        else
        {
            return redirect('dashboard')->with('flash_error', $response['order_status']);
        }

    }

    /**
     * Show the CC avanue payment for wallet response web
     *
     * @return \Illuminate\Http\Response
     */
    public function ccavanue_response(Request $request)
    { 
         // For default Gateway
        $response = Indipay::response($request);
        
        // For Otherthan Default Gateway
        $response = Indipay::gateway('CCAvenue')->response($request);

        if($response['order_status'] == 'Success')
        {
            $update=UserRequests::findOrFail($response['order_id']);
            $update->paid=1;
            $update->save();

            $updatepayment=UserRequestPayment::whererequest_id($response['order_id'])->first();
            $updatepayment->payment_id=$response['tracking_id'];
            $updatepayment->save();

            return response()->json(['status' => $response['order_status'] ]);
             
        }
        else if($response['order_status'] == 'Aborted')
        { 
            return response()->json(['status' => $response['order_status'] ]);
        }
        else if($response['order_status'] == 'Failure')
        { 
            return response()->json(['status' => $response['order_status'] ]);
        }
        else 
        { 
            return response()->json(['status' => $response['order_status'] ]);
        }

        

    }
    /**
     * Show the CC avanue payment for wallet cancel response web
     *
     * @return \Illuminate\Http\Response
     */
    public function ccavanue_cancel_response(Request $request)
    {

          // For default Gateway
        $response = Indipay::response($request);
        
        // For Otherthan Default Gateway
        $response = Indipay::gateway('CCAvenue')->response($request);

        if($response['order_status'] == 'Aborted')
        {
            return response()->json(['status' => $response['order_status'] ]);
        }
        else if($response['order_status'] == 'Failure')
        {
            return response()->json(['status' => $response['order_status'] ]);
        }
        else
        {
            return response()->json(['status' => $response['order_status'] ]);
        }

    }
     /**
     * Check mobile number exists or not
     *
     * @return \Illuminate\Http\Response
     */
    public function user_check_mobile(Request $request)
    {
        $check =User::wheremobile($request->username)->first(); 

        if($check)   
            return response()->json(['status' => true ]); 
        else
            return response()->json(['status' => false ]);  
    }


    public function package()
    {
        $package = PackageType::all();
        
        return $package;
    }




}

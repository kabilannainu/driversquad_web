<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
  
use Log;
use Auth;
use Setting;
use Exception;
use \Carbon\Carbon;
use App\Helpers\Helper;
use App\Http\Controllers\SendPushNotification;

use App\User;

use App\Dispatcher;
use App\RequestFilter;
use App\Fleet;
use App\Admin;
use App\Provider;
use App\UserPayment;
use App\ServiceType;
use App\UserRequests;
use App\ProviderService;
use App\UserRequestRating;
use App\UserRequestPayment;
use App\CustomPush;

use App\GeoFencing;


class DispatcherController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('admin');
        // $this->middleware('demo', ['only' => ['profile_update', 'password_update']]);
    }

    
    /**
     * Dispatcher Panel.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if(Auth::guard('admin')->user()){
            return view('admin.dispatcher');
        }elseif(Auth::guard('dispatcher')->user()){
            return view('dispatcher.dispatcher');
        }
    }

    /**
     * Display a listing of the active trips in the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function trips(Request $request)
    {
        $Trips = UserRequests::with('user', 'provider')
                    ->orderBy('id','desc');

        if($request->type == "SEARCHING"){
            $Trips = $Trips->where('status',$request->type)->orwhere('status','SCHEDULES')->orwhere('status','SCHEDULED');
        }else if($request->type == "CANCELLED"){
            $Trips = $Trips->where('status',$request->type);
        }
        
        $Trips =  $Trips->paginate(10);

        return $Trips;
    }

    /**
     * Display a listing of the users in the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function users(Request $request)
    {
        $Users = new User;

        if($request->has('mobile')) {
            $Users->where('mobile', 'like', $request->mobile."%");
        }

        if($request->has('first_name')) {
            $Users->where('first_name', 'like', $request->first_name."%");
        }

        if($request->has('last_name')) {
            $Users->where('last_name', 'like', $request->last_name."%");
        }

        if($request->has('email')) {
            $Users->where('email', 'like', $request->email."%");
        }

        return $Users->paginate(10);
    }

    /**
     * Display a listing of the active trips in the application.
     *
     * @return \Illuminate\Http\Response
     */
    public function providers(Request $request)
    {
        $Providers = new Provider;

        if($request->has('latitude') && $request->has('longitude')) {
            $ActiveProviders = ProviderService::AvailableServiceProvider($request->service_type)
                    ->get()
                    ->pluck('provider_id');

            $distance = Setting::get('provider_search_radius', '10');
            $latitude = $request->latitude;
            $longitude = $request->longitude;

            $Providers = Provider::whereIn('id', $ActiveProviders)
                ->where('status', 'approved')
                ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                ->with('service', 'service.service_type')
                ->paginate(1000);

            return $Providers;
        }

        return $Providers;
    }

    /**
     * Create manual request.
     *
     * @return \Illuminate\Http\Response
     */
    public function assign($request_id, $provider_id)
    {
        try {
            $Request = UserRequests::findOrFail($request_id);
            $Provider = Provider::findOrFail($provider_id);

            $Request->provider_id = $Provider->id;
            
            if($Request->status =='SCHEDULES' || $Request->status =='SCHEDULED')
            {  }
            else
            {
                $Request->status = 'STARTED';
            }

            $Request->current_provider_id = $Provider->id;
            $Request->save();

            // ProviderService::where('provider_id',$Request->provider_id)->update(['status' =>'riding']);

            (new SendPushNotification)->IncomingRequest($Request->current_provider_id);

            try {
                RequestFilter::where('request_id', $Request->id)
                    ->where('provider_id', $Provider->id)
                    ->firstOrFail();
            } catch (Exception $e) {
                $Filter = new RequestFilter;
                $Filter->request_id = $Request->id;
                $Filter->provider_id = $Provider->id; 
                $Filter->status = 0;
                $Filter->save();
            }

            if(Auth::guard('admin')->user()){
                return redirect()
                        ->route('admin.dispatcher.index')
                        ->with('flash_success', 'Request Assigned to Provider!');

            }elseif(Auth::guard('dispatcher')->user()){
                return redirect()
                        ->route('dispatcher.index')
                        ->with('flash_success', 'Request Assigned to Provider!');

            }

        } catch (Exception $e) {
            if(Auth::guard('admin')->user()){
                return redirect()->route('admin.dispatcher.index')->with('flash_error', 'Something Went Wrong!');
            }elseif(Auth::guard('dispatcher')->user()){
                return redirect()->route('dispatcher.index')->with('flash_error', 'Something Went Wrong!');
            }
        }
    }
    public function estimated_fare(Request $request)
    {
       
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

            $meter = $details['rows'][0]['elements'][0]['distance']['value'];
            $time = $details['rows'][0]['elements'][0]['duration']['text'];
            $seconds = $details['rows'][0]['elements'][0]['duration']['value'];

            $kilometer = round($meter/1000);
            $minutes = round($seconds/60);

            $rental_hour = round($minutes/60);

            if($request->rental_hours!=null){

                $package = PackageType::where('id',$request->rental_hours)->first();
                $package_hour = $package->package_hour;
//dd($package);
                if($rental_hour > $package->package_hour){


                     $rental = ceil($rental_hour);
                }else{
                    $rental = ceil($package->package_hour);
                }


            }
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
            if($service_type->calculator == 'MIN') {
                $price += $service_type->minute * $minutes;
            } else if($service_type->calculator == 'HOUR') {
                $price += $service_type->minute * 60;
            } else if($service_type->calculator == 'DISTANCE') {
                $kilmin = $kilometer - $service_type->distance;
                $price += ($kilmin * $service_type->price);
            } else if($service_type->calculator == 'DISTANCEMIN') {

                $price += ((($kilometer-$service_type->distance>0?($kilometer-$service_type->distance):0) * $service_type->price)) + ($service_type->minute * $minutes);

            } else if($service_type->calculator == 'DISTANCEHOUR') {
                $kilmin = $kilometer - $service_type->distance;
                $price += ($kilmin * $service_type->price) + ($rental * $hour);
            } else {
                $kilmin = $kilometer - $service_type->distance;
                $price += ($kilmin * $service_type->price);
            }


            if($request->service_required=="rental"){

                $price = $rental * $fixed_price_only->rental_fare;

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
                    $kilometer = $kilometer * 2;
                    $price = (($kilometer * $fixed_price_only->roundtrip_km) + ($fixed_price_only->outstation_driver * $total_days));
                }
                else
                {
                     $price = (($kilometer * $fixed_price_only->outstation_km) + ($fixed_price_only->outstation_driver));
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
                $geo_fencing_service_type = GeoFencing::with(
                    ['service_geo_fencing' =>  function ($query) use ($service_type_id) {
                            $query->where('service_type_id',$service_type_id);
                   } ])->whereid($geo_fencing_id)->first(); 

                if(!empty($geo_fencing_service_type->service_geo_fencing->city_limits) && $geo_fencing_service_type->service_geo_fencing->city_limits < $kilometer)
                { 
                    $city_limits = 1;
                }
           


               $check = $this->poly_check_request((round($request->d_latitude,6)),(round($request->d_longitude,6)));

               if($check=='no')
               {
                    $non_geo_price = $geo_fencing_service_type->service_geo_fencing->non_geo_price;

               }
           


            }   





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
                    'city_limits' => $city_limits,
                    'service_required'=>$request->service_required,
                    'rental_hours'=>$package_hour,
                    'leave'=>$leave,
                    'return'=>$return,
                    'day'=>$day,
                    'limit_message' => Setting::get('limit_message'),
                    'non_geo_price' =>  $non_geo_price
            ]);

        } catch(Exception $e) {

          
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    /**
     * Create manual request.
     *
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request) {

        $this->validate($request, [
                's_latitude' => 'required|numeric',
                's_longitude' => 'required|numeric',
                'd_latitude' => 'required|numeric',
                'd_longitude' => 'required|numeric',
                'service_type' => 'required|numeric|exists:service_types,id',
                'distance' => 'required|numeric',
            ]);

        try {
            $User = User::where('mobile', $request->mobile)->firstOrFail();
        } catch (Exception $e) {
            try {
                $User = User::where('email', $request->email)->firstOrFail();
            } catch (Exception $e) { 

                $User = User::create([
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'email' => $request->email,
                    'mobile' => $request->mobile,
                    'password' => bcrypt($request->mobile),
                    'payment_mode' => 'CASH'
                ]);
            }
        }

        if($request->has('schedule_time')){
            try {
                $CheckScheduling = UserRequests::whereIn('status', ['SCHEDULES','SCHEDULED'])
                        ->where('user_id', $User->id)
                        ->where('schedule_at', '>', strtotime($request->schedule_time." - 1 hour"))
                        ->where('schedule_at', '<', strtotime($request->schedule_time." + 1 hour"))
                        ->firstOrFail();
                
                if($request->ajax()) {
                    return response()->json(['error' => trans('api.ride.request_scheduled')], 500);
                } else {
                    return redirect('dashboard')->with('flash_error', 'Already request is Scheduled on this time.');
                }

            } catch (Exception $e) {
                // Do Nothing
            }
        }

        try{
            $geo_fencing=$this->poly_check_new_estimate((round($request->s_latitude,6)),(round($request->s_longitude,6)));
            if($geo_fencing)
            {

            $details = "https://maps.googleapis.com/maps/api/directions/json?origin=".$request->s_latitude.",".$request->s_longitude."&destination=".$request->d_latitude.",".$request->d_longitude."&mode=driving&key=".Setting::get('map_key');

            $json = curl($details);

            $details = json_decode($json, TRUE);

            $route_key = $details['routes'][0]['overview_polyline']['points'];

            $UserRequest = new UserRequests;
            $UserRequest->booking_id = Helper::generate_booking_id();
            $UserRequest->user_id = $User->id;
            $UserRequest->current_provider_id = 0;
            $UserRequest->service_type_id = $request->service_type;
            $UserRequest->payment_mode = 'CASH';
            
            // $UserRequest->status = 'SEARCHING'; /// newly comment

            //// ///  nwely added

            if($request->has('schedule_time')){
                if($request->has('provider_auto_assign'))
                    $UserRequest->status = 'SCHEDULES'; 
                else
                    $UserRequest->status = 'SCHEDULED';  
            }
            else {
                $UserRequest->assigned_at = Carbon::now();
                $UserRequest->status = 'SEARCHING'; 
            }

            ///  end nwely added

            $UserRequest->s_address = $request->s_address ? : "";
            $UserRequest->s_latitude = $request->s_latitude;
            $UserRequest->s_longitude = $request->s_longitude;

            $UserRequest->d_address = $request->d_address ? : "";
            $UserRequest->d_latitude = $request->d_latitude;
            $UserRequest->d_longitude = $request->d_longitude;
            $UserRequest->route_key = $route_key;

            $UserRequest->distance = $request->distance;

            $UserRequest->geo_fencing_id = $geo_fencing;

            // $UserRequest->assigned_at = Carbon::now(); /// newly comment

            $UserRequest->use_wallet = 0;
            $UserRequest->surge = 0;        // Surge is not necessary while adding a manual dispatch

            if($request->has('schedule_time')) {
                $UserRequest->schedule_at = Carbon::parse($request->schedule_time);
                $UserRequest->is_scheduled = 'YES';  /// nwely added
                $UserRequest->type = 'schedule'; ///  nwely added 
            }

            $UserRequest->save();

            if($request->has('provider_auto_assign')) {
                $ActiveProviders = ProviderService::AvailableServiceProvider($request->service_type)
                        ->get()
                        ->pluck('provider_id');

                $distance = Setting::get('provider_search_radius', '10');
                $latitude = $request->s_latitude;
                $longitude = $request->s_longitude;

                $Providers = Provider::whereIn('id', $ActiveProviders)
                    ->where('status', 'approved')
                    ->whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                    ->get();

                // List Providers who are currently busy and add them to the filter list.

                if(count($Providers) == 0) {
                    if($request->ajax()) {
                        // Push Notification to User
                        return response()->json(['message' => trans('api.ride.no_providers_found')]); 
                    } else {
                        return back()->with('flash_success', 'No Providers Found! Please try again.');
                    }
                }

                // $Providers[0]->service()->update(['status' => 'riding']); // newly comment

                // $UserRequest->current_provider_id = $Providers[0]->id; 

                if((Setting::get('manual_request',0) == 0) && (Setting::get('broadcast_request',0) == 0)){
                    $UserRequest->current_provider_id = $Providers[0]->id;
                }else{
                    $UserRequest->current_provider_id = 0;
                    $UserRequest->broadcast = 'YES';
                }

                $UserRequest->save();

                Log::info('New Dispatch : ' . $UserRequest->id);
                Log::info('Assigned Provider : ' . $UserRequest->current_provider_id);

                // Incoming request push to provider
                // (new SendPushNotification)->IncomingRequest($UserRequest->current_provider_id);

                foreach ($Providers as $key => $Provider) {

                        // Incoming request push to provider
                       (new SendPushNotification)->IncomingRequest($Provider->id); 
                        
                    $Filter = new RequestFilter;
                    $Filter->request_id = $UserRequest->id;
                    $Filter->provider_id = $Provider->id; 
                    $Filter->save();
                }
            } 
            }
            else
            {
                if($request->ajax()) {
                    return response()->json(['error' => 'Service is not available at this location. ', 'message' => $e], 500);
                } else {
                    return back()->with('flash_error', 'Service is not available at this location.');
                }
            }

            if($request->ajax()) {
                return $UserRequest;
            } else {
                return redirect('dashboard');
            }

        } catch (Exception $e) {
            if($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong'), 'message' => $e], 500);
            }else{
                return back()->with('flash_error', 'Something went wrong while sending request. Please try again.');
            }
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function profile()
    {
        return view('dispatcher.account.profile');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function profile_update(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|max:255',
            'mobile' => 'required|digits_between:6,13',
        ]);

        try{
            $dispatcher = Auth::guard('dispatcher')->user();
            $dispatcher->name = $request->name;
            $dispatcher->mobile = $request->mobile;
            $dispatcher->save();

            return redirect()->back()->with('flash_success','Profile Updated');
        }

        catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function password()
    {
        return view('dispatcher.account.change-password');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function password_update(Request $request)
    {
        $this->validate($request,[
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        try {

           $Dispatcher = Dispatcher::find(Auth::guard('dispatcher')->user()->id);

            if(password_verify($request->old_password, $Dispatcher->password))
            {
                $Dispatcher->password = bcrypt($request->password);
                $Dispatcher->save();

                return redirect()->back()->with('flash_success','Password Updated');
            }
        } catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    }



    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function cancel(Request $request) {

        $this->validate($request, [
            'request_id' => 'required|numeric|exists:user_requests,id',
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

            if(in_array($UserRequest->status, ['SEARCHING','STARTED','ARRIVED','SCHEDULED','SCHEDULES'])) {


                $UserRequest->status = 'CANCELLED';
                $UserRequest->cancel_reason = "Cancelled by Admin";
                $UserRequest->cancelled_by = 'NONE';
                $UserRequest->save();

                RequestFilter::where('request_id', $UserRequest->id)->delete();

                if($UserRequest->status != 'SCHEDULED' || $UserRequest->status != 'SCHEDULES'){

                    if($UserRequest->provider_id != 0){

                        ProviderService::where('provider_id',$UserRequest->provider_id)->update(['status' => 'active']);

                    }
                }

                 // Send Push Notification to User
                (new SendPushNotification)->UserCancellRide($UserRequest);
                (new SendPushNotification)->ProviderCancellRide($UserRequest);

                if($request->ajax()) {
                    return response()->json(['message' => trans('api.ride.ride_cancelled')]); 
                }else{
                    return back()->with('flash_success','Request Cancelled Successfully');
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





///// admin function







    /**
     * Dashboard.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        try{

            $rides = UserRequests::has('user')->orderBy('id','desc')->get();
            $cancel_rides = UserRequests::where('status','CANCELLED');
            $scheduled_rides = UserRequests::where('status','SCHEDULED')->count();
            $user_cancelled = $cancel_rides->where('cancelled_by','USER')->count();
            $provider_cancelled = $cancel_rides->where('cancelled_by','PROVIDER')->count();
            $cancel_rides = $cancel_rides->count();
            $service = ServiceType::count();
            $fleet = Fleet::count();
            $revenue = UserRequestPayment::sum('total');
            $providers = Provider::take(10)->orderBy('rating','desc')->get();

            return view('dispatcher.dashboard',compact('providers','fleet','scheduled_rides','service','rides','user_cancelled','provider_cancelled','cancel_rides','revenue'));
        }
        catch(Exception $e){
            return redirect()->route('dispatcher.user.index')->with('flash_error','Something Went Wrong with Dashboard!');
        }
    }

     /**
     * Heat Map.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function heatmap()
    {
        try{
            $rides = UserRequests::has('user')->orderBy('id','desc')->get();
            $providers = Provider::take(10)->orderBy('rating','desc')->get();
            return view('dispatcher.heatmap',compact('providers','rides'));
        }
        catch(Exception $e){
            return redirect()->route('dispatcher.user.index')->with('flash_error','Something Went Wrong with Dashboard!');
        }
    }

    /**
     * Map of all Users and Drivers.
     *
     * @return \Illuminate\Http\Response
     */
    public function map_index(Request $request)
    { 
        if(isset($request->name))
            $name = $request->name;
        else
            $name = ''; 
        return view('dispatcher.map.index',compact('name'));
    }

    /**
     * Map of all Users and Drivers.
     *
     * @return \Illuminate\Http\Response
     */
    public function map_ajax(Request $request)
    {
        try {

             if($request->name)
            {
                $Providers = Provider::where('latitude', '!=', 0)
                    ->where('longitude', '!=', 0)
                    ->where('first_name', 'like', '%' . $request->name . '%')
                    ->with('service')
                    ->get(); 
                $Users = User::where('latitude', '!=', 0)
                    ->where('longitude', '!=', 0)
                    ->where('first_name', 'like', '%' . $request->name . '%') 
                    ->get();
            } 
            else
            {
                $Providers = Provider::where('latitude', '!=', 0)
                    ->where('longitude', '!=', 0) 
                    ->with('service')
                    ->get();
                $Users = User::where('latitude', '!=', 0)
                    ->where('longitude', '!=', 0)
                    ->get();
            }

            for ($i=0; $i < sizeof($Users); $i++) { 
                $Users[$i]->status = 'user';
            }

            $All = $Users->merge($Providers);

            return $All;

        } catch (Exception $e) {
            return [];
        }
    } 

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function payment()
    {
        try {
             $payments = UserRequests::where('paid', 1)
                    ->has('user')
                    ->has('provider')
                    ->has('payment')
                    ->orderBy('user_requests.created_at','desc')
                    ->get();
            
            return view('dispatcher.payment.payment-history', compact('payments'));
        } catch (Exception $e) { 
             return back()->with('flash_error','Something Went Wrong!');
        }
    }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param  \App\Provider  $provider
    //  * @return \Illuminate\Http\Response
    //  */
    // public function help()
    // {
    //     try {
    //         $str = file_get_contents('http://appoets.com/help.json');
    //         $Data = json_decode($str, true);
    //         return view('admin.help', compact('Data'));
    //     } catch (Exception $e) {
    //          return back()->with('flash_error','Something Went Wrong!');
    //     }
    // }

    /**
     * User Rating.
     *
     * @return \Illuminate\Http\Response
     */
    public function user_review()
    {
        try {
            $Reviews = UserRequestRating::where('user_id', '!=', 0)->with('user', 'provider')->get();
            return view('dispatcher.review.user_review',compact('Reviews'));

        } catch(Exception $e) {
            return redirect()->route('dispatcher.setting')->with('flash_error','Something Went Wrong!');
        }
    }

    /**
     * Provider Rating.
     *
     * @return \Illuminate\Http\Response
     */
    public function provider_review()
    {
        try {
            $Reviews = UserRequestRating::where('provider_id','!=',0)->with('user','provider')->get();
            return view('dispatcher.review.provider_review',compact('Reviews'));
        } catch(Exception $e) {
            return redirect()->route('dispatcher.setting')->with('flash_error','Something Went Wrong!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ProviderService
     * @return \Illuminate\Http\Response
     */
    public function destory_provider_service($id){
        try {
            ProviderService::find($id)->delete();
            return back()->with('message', 'Service deleted successfully');
        } catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    }

    /**
     * Testing page for push notifications.
     *
     * @return \Illuminate\Http\Response
     */
    public function push_index()
    {

        $data = \PushNotification::app('IOSUser')
            ->to('3911e9870e7c42566b032266916db1f6af3af1d78da0b52ab230e81d38541afa')
            ->send('Hello World, i`m a push message');
        dd($data);
    }

    /**
     * Testing page for push notifications.
     *
     * @return \Illuminate\Http\Response
     */
    public function push_store(Request $request)
    {
        try {
            ProviderService::find($id)->delete();
            return back()->with('message', 'Service deleted successfully');
        } catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    }

    /**
     * privacy.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */

    public function privacy(){
        return view('dispatcher.pages.static')
            ->with('title',"Privacy Page")
            ->with('page', "privacy");
    }
      /**
     * Help.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */

    public function help(){
        return view('dispatcher.pages.static')
            ->with('title',"Help Page")
            ->with('page', "help");
    }
      /**
     * Terms and condition.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */

    public function terms(){
        return view('dispatcher.pages.terms')
            ->with('title',"Terms & Condition Page")
            ->with('page', "terms");
    }

    public function offers(){
        return view('dispatcher.pages.offers')
            ->with('title',"Offers")
            ->with('page', "offers");
    }


     public function about_us(){
        return view('dispatcher.pages.about_us')
            ->with('title',"About Us")
            ->with('page', "about_us");
    }

    /**
     * pages.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function pages(Request $request){
        $this->validate($request, [
                'page' => 'required',
                'content' => 'required',
            ]);

        Setting::set($request->page, $request->content);
        Setting::save();

        return back()->with('flash_success', 'Content Updated!');
    }

    /**
     * account statements.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function statement($type = 'individual'){

        try{

            $page = 'Ride Statement';

            if($type == 'individual'){
                $page = 'Provider Ride Statement';
            }elseif($type == 'today'){
                $page = 'Today Statement - '. date('d M Y');
            }elseif($type == 'monthly'){
                $page = 'This Month Statement - '. date('F');
            }elseif($type == 'yearly'){
                $page = 'This Year Statement - '. date('Y');
            }

            $rides = UserRequests::with('payment')->orderBy('id','desc');
            $cancel_rides = UserRequests::where('status','CANCELLED');
            $revenue = UserRequestPayment::select(\DB::raw(
                           'SUM(ROUND(fixed) + ROUND(distance)) as overall, SUM(ROUND(commision)) as commission' 
                       ));

            if($type == 'today'){

                $rides->where('created_at', '>=', Carbon::today());
                $cancel_rides->where('created_at', '>=', Carbon::today());
                $revenue->where('created_at', '>=', Carbon::today());

            }elseif($type == 'monthly'){

                $rides->where('created_at', '>=', Carbon::now()->month);
                $cancel_rides->where('created_at', '>=', Carbon::now()->month);
                $revenue->where('created_at', '>=', Carbon::now()->month);

            }elseif($type == 'yearly'){

                $rides->where('created_at', '>=', Carbon::now()->year);
                $cancel_rides->where('created_at', '>=', Carbon::now()->year);
                $revenue->where('created_at', '>=', Carbon::now()->year);

            }

            $rides = $rides->get();
            $cancel_rides = $cancel_rides->count();
            $revenue = $revenue->get();

            return view('dispatcher.providers.statement', compact('rides','cancel_rides','revenue'))
                    ->with('page',$page);

        } catch (Exception $e) {
            return back()->with('flash_error','Something Went Wrong!');
        }
    }


    /**
     * account statements today.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function statement_today(){
        return $this->statement('today');
    }

    /**
     * account statements monthly.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function statement_monthly(){
        return $this->statement('monthly');
    }

     /**
     * account statements monthly.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function statement_yearly(){
        return $this->statement('yearly');
    }


    /**
     * account statements.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function statement_provider(){

        try{

            $Providers = Provider::all();

            foreach($Providers as $index => $Provider){

                $Rides = UserRequests::where('provider_id',$Provider->id)
                            ->where('status','<>','CANCELLED')
                            ->get()->pluck('id');

                $Providers[$index]->rides_count = $Rides->count();

                $Providers[$index]->payment = UserRequestPayment::whereIn('request_id', $Rides)
                                ->select(\DB::raw(
                                   'SUM(ROUND(provider_pay)) as overall, SUM(ROUND(provider_commission)) as commission' 
                                ))->get();
            }

            return view('dispatcher.providers.provider-statement', compact('Providers'))->with('page','Providers Statement');

        } catch (Exception $e) {
            return back()->with('flash_error','Something Went Wrong!');
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function translation(){

        try{
            return view('dispatcher.translation');
        }

        catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function push(){

        try{
            $Pushes = CustomPush::orderBy('id','desc')->get();
            return view('dispatcher.push',compact('Pushes'));
        }

        catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    }


    /**
     * pages.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function send_push(Request $request){


        $this->validate($request, [
                'send_to' => 'required|in:ALL,USERS,PROVIDERS',
                'user_condition' => ['required_if:send_to,USERS','in:ACTIVE,LOCATION,RIDES,AMOUNT'],
                'provider_condition' => ['required_if:send_to,PROVIDERS','in:ACTIVE,LOCATION,RIDES,AMOUNT'],
                'user_active' => ['required_if:user_condition,ACTIVE','in:HOUR,WEEK,MONTH'],
                'user_rides' => 'required_if:user_condition,RIDES',
                'user_location' => 'required_if:user_condition,LOCATION',
                'user_amount' => 'required_if:user_condition,AMOUNT',
                'provider_active' => ['required_if:provider_condition,ACTIVE','in:HOUR,WEEK,MONTH'],
                'provider_rides' => 'required_if:provider_condition,RIDES',
                'provider_location' => 'required_if:provider_condition,LOCATION',
                'provider_amount' => 'required_if:provider_condition,AMOUNT',
                'message' => 'required|max:100',
            ]);

        try{

            $CustomPush = new CustomPush;
            $CustomPush->send_to = $request->send_to;
            $CustomPush->message = $request->message;

            if($request->send_to == 'USERS'){

                $CustomPush->condition = $request->user_condition;

                if($request->user_condition == 'ACTIVE'){
                    $CustomPush->condition_data = $request->user_active;
                }elseif($request->user_condition == 'LOCATION'){
                    $CustomPush->condition_data = $request->user_location;
                }elseif($request->user_condition == 'RIDES'){
                    $CustomPush->condition_data = $request->user_rides;
                }elseif($request->user_condition == 'AMOUNT'){
                    $CustomPush->condition_data = $request->user_amount;
                }

            }elseif($request->send_to == 'PROVIDERS'){

                $CustomPush->condition = $request->provider_condition;

                if($request->provider_condition == 'ACTIVE'){
                    $CustomPush->condition_data = $request->provider_active;
                }elseif($request->provider_condition == 'LOCATION'){
                    $CustomPush->condition_data = $request->provider_location;
                }elseif($request->provider_condition == 'RIDES'){
                    $CustomPush->condition_data = $request->provider_rides;
                }elseif($request->provider_condition == 'AMOUNT'){
                    $CustomPush->condition_data = $request->provider_amount;
                }
            }

            if($request->has('schedule_date') && $request->has('schedule_time')){
                $CustomPush->schedule_at = date("Y-m-d H:i:s",strtotime("$request->schedule_date $request->schedule_time"));
            }

            $CustomPush->save();

            if($CustomPush->schedule_at == ''){
                $this->SendCustomPush($CustomPush->id);
            }

            return back()->with('flash_success', 'Message Sent to all '.$request->segment);
        }

        catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    }


    public function SendCustomPush($CustomPush){

        try{

            \Log::notice("Starting Custom Push");

            $Push = CustomPush::findOrFail($CustomPush);

            if($Push->send_to == 'USERS'){

                $Users = [];

                if($Push->condition == 'ACTIVE'){

                    if($Push->condition_data == 'HOUR'){

                        $Users = User::whereHas('trips', function($query) {
                            $query->where('created_at','>=',Carbon::now()->subHour());
                        })->get();
                        
                    }elseif($Push->condition_data == 'WEEK'){

                        $Users = User::whereHas('trips', function($query){
                            $query->where('created_at','>=',Carbon::now()->subWeek());
                        })->get();

                    }elseif($Push->condition_data == 'MONTH'){

                        $Users = User::whereHas('trips', function($query){
                            $query->where('created_at','>=',Carbon::now()->subMonth());
                        })->get();

                    }

                }elseif($Push->condition == 'RIDES'){

                    $Users = User::whereHas('trips', function($query) use ($Push){
                                $query->where('status','COMPLETED');
                                $query->groupBy('id');
                                $query->havingRaw('COUNT(*) >= '.$Push->condition_data);
                            })->get();


                }elseif($Push->condition == 'LOCATION'){

                    $Location = explode(',', $Push->condition_data);

                    $distance = Setting::get('provider_search_radius', '10');
                    $latitude = $Location[0];
                    $longitude = $Location[1];

                    $Users = User::whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                            ->get();

                }


                foreach ($Users as $key => $user) {
                    (new SendPushNotification)->sendPushToUser($user->id, $Push->message);
                }

            }elseif($Push->send_to == 'PROVIDERS'){


                $Providers = [];

                if($Push->condition == 'ACTIVE'){

                    if($Push->condition_data == 'HOUR'){

                        $Providers = Provider::whereHas('trips', function($query){
                            $query->where('created_at','>=',Carbon::now()->subHour());
                        })->get();
                        
                    }elseif($Push->condition_data == 'WEEK'){

                        $Providers = Provider::whereHas('trips', function($query){
                            $query->where('created_at','>=',Carbon::now()->subWeek());
                        })->get();

                    }elseif($Push->condition_data == 'MONTH'){

                        $Providers = Provider::whereHas('trips', function($query){
                            $query->where('created_at','>=',Carbon::now()->subMonth());
                        })->get();

                    }

                }elseif($Push->condition == 'RIDES'){

                    $Providers = Provider::whereHas('trips', function($query) use ($Push){
                               $query->where('status','COMPLETED');
                                $query->groupBy('id');
                                $query->havingRaw('COUNT(*) >= '.$Push->condition_data);
                            })->get();

                }elseif($Push->condition == 'LOCATION'){

                    $Location = explode(',', $Push->condition_data);

                    $distance = Setting::get('provider_search_radius', '10');
                    $latitude = $Location[0];
                    $longitude = $Location[1];

                    $Providers = Provider::whereRaw("(1.609344 * 3956 * acos( cos( radians('$latitude') ) * cos( radians(latitude) ) * cos( radians(longitude) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians(latitude) ) ) ) <= $distance")
                            ->get();

                }


                foreach ($Providers as $key => $provider) {
                    (new SendPushNotification)->sendPushToProvider($provider->id, $Push->message);
                }

            }elseif($Push->send_to == 'ALL'){

                $Users = User::all();
                foreach ($Users as $key => $user) {
                    (new SendPushNotification)->sendPushToUser($user->id, $Push->message);
                }

                $Providers = Provider::all();
                foreach ($Providers as $key => $provider) {
                    (new SendPushNotification)->sendPushToProvider($provider->id, $Push->message);
                }

            }
        }

        catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function service(){

        $services = ServiceType::all();
        return $services;
         
    }
    public function poly_check_new_estimate($s_latitude,$s_longitude)
{
    
           $range_data = GeoFencing::get();
           //dd($range_data);

           $yes = $no =  [];

           $longitude_x = $s_latitude;
           
           $latitude_y =  $s_longitude;

           foreach($range_data as $ranges)
           {

               $vertices_x = $vertices_y = [];

              // $ranges  = Setting::get('service_range');
              
               $range_values = json_decode($ranges['ranges'],true);
               //dd($range_values);
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

           if(count($yes)!=0)
                {
                return $yes[0];
                }
                else
                {
                return 0;
                }

  }

  
public function poly_check_new($s_latitude,$s_longitude)
{
    
           $range_data = GeoFencing::get();
           //dd($range_data);

           $yes = $no =  [];

           $longitude_x = $s_latitude;
           
           $latitude_y =  $s_longitude;

           foreach($range_data as $ranges)
           {

               $vertices_x = $vertices_y = [];

              // $ranges  = Setting::get('service_range');
              
               $range_values = json_decode($ranges['ranges'],true);
               //dd($range_values);
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

           if(count($yes)!=0)
                {
                return $yes[0];
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

    
           foreach($range_data as $ranges)
           {

  
              // $ranges  = Setting::get('service_range');

                $vertices_x = $vertices_y = [];

                $range_values = json_decode($ranges['ranges'],true);
                //dd($range_values);
                foreach($range_values as $range ){
                   $vertices_x[] = $range['lat'];
                   $vertices_y[] = $range['lng'];
                }


               $points_polygon = count($vertices_x) - 1; 

                if (is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)){
                    $yes[] =$ranges['id'];
                }else{

                      $no[] = 0;
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













}

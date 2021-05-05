<?php

namespace App\Http\Controllers\ProviderResources;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Auth;
use Log;
use Setting;
use Carbon\Carbon;
use App\Helpers\Helper;
use App\Http\Controllers\SendPushNotification;

use App\User;
use App\Admin;
use App\Promocode;
use App\UserRequests;
use App\RequestFilter;
use App\PromocodeUsage;
use App\PromocodePassbook;
use App\ProviderService;
use App\UserRequestRating;
use App\UserRequestPayment;
use App\ServiceType;
use App\WalletPassbook;
use App\GeoFencing;
use App\FleetVehicle;
use pointLocation;
use App\Provider;
use App\ServiceRentalHourPackage;

use DB;
use App\Time;
use App\TimePrice;

use DateTime;
use Location\Coordinate;
use Location\Distance\Vincenty;

class TripController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try{
            if($request->ajax()) {
                $Provider = Auth::user();
            } else {
                $Provider = Auth::guard('provider')->user();
            }

            $provider = $Provider->id;

            $AfterAssignProvider = RequestFilter::with(['request.user', 'request.payment', 'request.service_type'])
                ->where('provider_id', $provider)
                ->whereHas('request', function($query) use ($provider) {
                        $query->where('status','<>', 'CANCELLED');
                        $query->where('status','<>', 'SCHEDULES');
                        $query->where('status','<>', 'SCHEDULED');
                        //$query->where('provider_id', $provider );
                        $query->where('current_provider_id', $provider);
                    });


            $BeforeAssignProvider = RequestFilter::with(['request.user', 'request.payment', 'request.service_type'])
                    ->where('provider_id', $provider)
                    ->whereHas('request', function($query) use ($provider){
                        $query->where('status','<>', 'CANCELLED');
                        $query->where('status','<>', 'SCHEDULES');
                        $query->where('status','<>', 'SCHEDULED');
                        $query->when(Setting::get('broadcast_request') == 1, function ($q) {
                            $q->where('current_provider_id',0);
                        });
                        $query->when(Setting::get('broadcast_request') == 0, function ($q) use ($provider){
                            $q->where('current_provider_id',$provider);
                        });
                        
                    });
                
            $IncomingRequests = $BeforeAssignProvider->union($AfterAssignProvider)->get();
            
            if(!empty($request->latitude)) {
                $Provider->update([
                        'latitude' => $request->latitude,
                        'longitude' => $request->longitude,
                ]);
                
                //update provider service hold status
                DB::table('provider_services')->where('provider_id',$Provider->id)->where('status','hold')->update(['status' =>'active']);
            }

            if(Setting::get('manual_request',0) == 0){

                $Timeout = Setting::get('provider_select_timeout', 180);
                    if(!empty($IncomingRequests)){
                        for ($i=0; $i < sizeof($IncomingRequests); $i++) {
                            $IncomingRequests[$i]->time_left_to_respond = $Timeout - (time() - strtotime($IncomingRequests[$i]->request->assigned_at));
                            if($IncomingRequests[$i]->request->status == 'SEARCHING' && $IncomingRequests[$i]->time_left_to_respond < 0) {
                                if(Setting::get('broadcast_request',0) == 1){
                                    $this->assign_destroy($IncomingRequests[$i]->request->id);
                                }else{
                                    $this->assign_next_provider($IncomingRequests[$i]->request->id);
                                }
                            }
                        }
                    }

            }


            $Response = [
                    'account_status' => $Provider->status,
                    'service_status' => $Provider->service ? Auth::user()->service->status : 'offline',
                    'requests' => $IncomingRequests,
                ];

            return $Response;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Something went wrong']);
        }
    }

    /**
     * Calculate distance between two coordinates.
     * 
     * @return \Illuminate\Http\Response
     */

    public function calculate_distance(Request $request, $id){
        $this->validate($request, [
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric'
            ]);
        try{

            if($request->ajax()) {
                $Provider = Auth::user();
            } else {
                $Provider = Auth::guard('provider')->user();
            }

            $UserRequest = UserRequests::where('status','PICKEDUP')
                            ->where('provider_id',$Provider->id)
                            ->find($id);

            if($UserRequest && ($request->latitude && $request->longitude)){

                Log::info(" Live ---- REQUEST ID:".$UserRequest->id."==SOURCE LATITUDE:".$UserRequest->track_latitude."==SOURCE LONGITUDE:".$UserRequest->track_longitude."==Destination lat :".$request->latitude."==Destination lang :".$request->longitude);
            
                if($UserRequest->track_latitude && $UserRequest->track_longitude){

                    $coordinate1 = new Coordinate($UserRequest->track_latitude, $UserRequest->track_longitude); /** Set Distance Calculation Source Coordinates ****/
                    $coordinate2 = new Coordinate($request->latitude, $request->longitude); /** Set Distance calculation Destination Coordinates ****/

                    $calculator = new Vincenty();

                    /***Distance between two coordinates using spherical algorithm (library as mjaschen/phpgeo) ***/ 

                    $mydistance = $calculator->getDistance($coordinate1, $coordinate2); 

                    $meters = round($mydistance);

                    Log::info("REQUEST ID:".$UserRequest->id."==BETWEEN TWO COORDINATES DISTANCE:".$meters." (m)");

                    if($meters){
                        $currentdate=\Carbon\Carbon::now();
                        if($UserRequest->updated_at != $currentdate) {
                            /*** If traveled distance riched houndred meters means to be the source coordinates ***/
                            $traveldistance = round(($meters/1000),8);
                            Log::info("travelled distance:".$traveldistance);
                            $calulatedistance = $UserRequest->track_distance + $traveldistance;

                            Log::info("calculate distance:".$calulatedistance);
                            
                            $UserRequest->track_distance  = $calulatedistance;
                            $UserRequest->distance        = $calulatedistance;
                            $UserRequest->track_latitude  = $request->latitude;
                            $UserRequest->track_longitude = $request->longitude;
                            $UserRequest->save(); 
                        }
                    }
                }else if(!$UserRequest->track_latitude && !$UserRequest->track_longitude) {
                    Log::info('check first lat lang');
                    $UserRequest->distance             = 0;
                    $UserRequest->track_latitude      = $request->latitude;
                    $UserRequest->track_longitude     = $request->longitude;
                    $UserRequest->save();
                }
            }
            return $UserRequest;
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Something went wrong']);
        }
    }

    /**
     * Cancel given request.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancel(Request $request)
    {
        $this->validate($request, [
            'cancel_reason'=> 'max:255',
        ]);
        
        try{

            $UserRequest = UserRequests::findOrFail($request->id);
            $Cancellable = ['SEARCHING', 'ACCEPTED', 'ARRIVED', 'STARTED', 'CREATED','SCHEDULED'];

            if(!in_array($UserRequest->status, $Cancellable)) {
                return back()->with(['flash_error' => 'Cannot cancel request at this stage!']);
            }

            $UserRequest->status = "CANCELLED";
            $UserRequest->cancel_reason = $request->cancel_reason;
            $UserRequest->cancelled_by = "PROVIDER";
            $UserRequest->save();

             RequestFilter::where('request_id', $UserRequest->id)->delete();

             ProviderService::where('provider_id',$UserRequest->provider_id)->update(['status' =>'active']);

             // Send Push Notification to User
            (new SendPushNotification)->ProviderCancellRide($UserRequest);

            return $UserRequest;

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Something went wrong']);
        }


    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function rate(Request $request, $id)
    {

        $this->validate($request, [
                'rating' => 'required|integer|in:1,2,3,4,5',
                'comment' => 'max:255',
            ]);
    
        try {

            $UserRequest = UserRequests::where('id', $id)
                ->where('status', 'COMPLETED')
                ->firstOrFail();

            if($UserRequest->rating == null) {
                UserRequestRating::create([
                        'provider_id' => $UserRequest->provider_id,
                        'user_id' => $UserRequest->user_id,
                        'request_id' => $UserRequest->id,
                        'provider_rating' => $request->rating,
                        'provider_comment' => $request->comment,
                    ]);
            } else {
                $UserRequest->rating->update([
                        'provider_rating' => $request->rating,
                        'provider_comment' => $request->comment,
                    ]);
            }

            $UserRequest->update(['provider_rated' => 1]);

            // Delete from filter so that it doesn't show up in status checks.
            RequestFilter::where('request_id', $id)->delete();

            ProviderService::where('provider_id',$UserRequest->provider_id)->update(['status' =>'active']);

            // Send Push Notification to Provider 
            $average = UserRequestRating::where('provider_id', $UserRequest->provider_id)->avg('provider_rating');

            $UserRequest->user->update(['rating' => $average]);
             (new SendPushNotification)->Rate($UserRequest);

            return response()->json(['message' => 'Request Completed!']);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Request not yet completed!'], 500);
        }
    }
    /**
     * Get the trip history of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function request_rides(Request $request)
    {
        $req = $request->request_id;
        $provider =Auth::user()->id;


        try {
            if($request->ajax()) {

                 $query = UserRequests::query();
                 $query->when(request('type') == 'past' , function ($q) use ($req){
                      $q->when(request('request_id') != null  , function ($p) use ($req) {
                        $p->where('id' , $req);
                      });
                      $q->where('status', 'COMPLETED');
                      $q->where('provider_id', Auth::user()->id);
                 });
                 $query->when(request('type') == 'upcoming' , function ($q) use ($req){
                      $q->when(request('request_id') != null  , function ($p) use ($req) {
                        $p->where('id' , $req);
                      });
                      $q->where('is_scheduled', 'YES');
                      $q->where('provider_id', Auth::user()->id);
                 });
                 $Jobs = $query->orderBy('created_at','desc')
                               ->with('payment','service_type','user','rating')
                               ->get();


               if(!empty($Jobs)){
                $map_icon_start = asset('asset/img/marker-car.png');
                $map_icon_end = asset('asset/img/map-marker-red.png');
                foreach ($Jobs as $key => $value) {
                    $Jobs[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?".
                            "autoscale=1".
                            "&size=600x300".
                            "&maptype=terrian".
                            "&format=png".
                            "&visual_refresh=true".
                            "&markers=icon:".$map_icon_start."%7C".$value->s_latitude.",".$value->s_longitude.
                            "&markers=icon:".$map_icon_end."%7C".$value->d_latitude.",".$value->d_longitude.
                            "&path=color:0x000000|weight:3|enc:".$value->route_key.
                            "&key=".Setting::get('map_key');
                }
            }
            return $Jobs;
        }
        
        } catch (Exception $e) {
            
        }
    }

    /**
     * Get the trip history of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function scheduled(Request $request)
    {
        
        try{

            $Jobs = UserRequests::where('provider_id', Auth::user()->id)
                    ->where('status' , 'SCHEDULED')
                    ->where('is_scheduled', 'YES')
                    ->with('payment','service_type')
                    ->get();

            if(!empty($Jobs)){
                $map_icon_start = asset('asset/img/marker-start.png');
                $map_icon_end = asset('asset/img/marker-end.png');
                foreach ($Jobs as $key => $value) {
                    $Jobs[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?".
                            "autoscale=1".
                            "&size=600x300".
                            "&maptype=terrian".
                            "&format=png".
                            "&visual_refresh=true".
                            "&markers=icon:".$map_icon_start."%7C".$value->s_latitude.",".$value->s_longitude.
                            "&markers=icon:".$map_icon_end."%7C".$value->d_latitude.",".$value->d_longitude.
                            "&path=color:0x000000|weight:3|enc:".$value->route_key.
                            "&key=".Setting::get('map_key');
                }
            }

            return $Jobs;
            
        } catch(Exception $e) {
            return response()->json(['error' => "Something Went Wrong"]);
        }
    }

    /**
     * Get the trip history of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function history(Request $request)
    {
        if($request->ajax()) {

            $Jobs = UserRequests::where('provider_id', Auth::user()->id)
                    ->where('status', 'COMPLETED')
                    ->orderBy('created_at','desc')
                    ->with('payment','service_type')
                    ->get();

            if(!empty($Jobs)){
                $map_icon_start = asset('asset/img/marker-start.png');
                $map_icon_end = asset('asset/img/marker-end.png');
                foreach ($Jobs as $key => $value) {
                    $Jobs[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?".
                            "autoscale=1".
                            "&size=600x300".
                            "&maptype=terrian".
                            "&format=png".
                            "&visual_refresh=true".
                            "&markers=icon:".$map_icon_start."%7C".$value->s_latitude.",".$value->s_longitude.
                            "&markers=icon:".$map_icon_end."%7C".$value->d_latitude.",".$value->d_longitude.
                            "&path=color:0x000000|weight:3|enc:".$value->route_key.
                            "&key=".Setting::get('map_key');
                }
            }
            return $Jobs;
        }
        $Jobs = UserRequests::where('provider_id', Auth::guard('provider')->user()->id)->with('user', 'service_type', 'payment', 'rating')->get();
        return view('provider.trip.index', compact('Jobs'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function accept(Request $request, $id)
    {
        try {

            $current_time=Carbon::now()->format('H:i:s');

            $UserRequest = UserRequests::with('user')->findOrFail($id);

            if($UserRequest->status != "SEARCHING") {
                return response()->json(['error' => 'Request already under progress!']);
            }
            
            $UserRequest->provider_id = Auth::user()->id;

            if(Setting::get('broadcast_request',0) == 1){
               $UserRequest->current_provider_id = Auth::user()->id; 
            }

            if($UserRequest->schedule_at != ""){

                $beforeschedule_time = strtotime($UserRequest->schedule_at."- 1 hour");
                $afterschedule_time = strtotime($UserRequest->schedule_at."+ 1 hour");

                $CheckScheduling = UserRequests::where('status','SCHEDULES')
                            ->where('provider_id', Auth::user()->id)
                            ->whereBetween('schedule_at',[$beforeschedule_time,$afterschedule_time])
                            ->count();

                if($CheckScheduling > 0 ){
                    if($request->ajax()) {
                        return response()->json(['error' => trans('api.ride.request_already_scheduled')]);
                    }else{
                        return redirect('dashboard')->with('flash_error', 'If the ride is already scheduled then we cannot schedule/request another ride for the after 1 hour or before 1 hour');
                    }
                }

                RequestFilter::where('request_id',$UserRequest->id)->where('provider_id',Auth::user()->id)->update(['status' => 2]);

                $UserRequest->status = "SCHEDULES";
                $UserRequest->save();

            }else{


                $UserRequest->status = "STARTED";
                $UserRequest->arrival_estimate_time = @$_REQUEST['arrival_time']>0 ? @$_REQUEST['arrival_time'] : 10; //Value less than 0 make it as default 10
                $UserRequest->driver_accept_time = $current_time;               
                
                
                $UserRequest->save();


                ProviderService::where('provider_id',$UserRequest->provider_id)->update(['status' =>'riding']);

                $Filters = RequestFilter::where('request_id', $UserRequest->id)->where('provider_id', '!=', Auth::user()->id)->get();
                // dd($Filters->toArray());
                foreach ($Filters as $Filter) {
                $provider= Auth::user();
                (new SendPushNotification)->RideAcceptedRemainProviders($Filter,$provider);

                    $Filter->delete();
                }
            }

            $UnwantedRequest = RequestFilter::where('request_id','!=' ,$UserRequest->id)
                                ->where('provider_id',Auth::user()->id )
                                ->whereHas('request', function($query){
                                    $query->where('status','<>','SCHEDULES');
                                });

            if($UnwantedRequest->count() > 0){
                $UnwantedRequest->delete();
            }  

            // Send Push Notification to User
            (new SendPushNotification)->RideAccepted($UserRequest);

            return $UserRequest;

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to accept, Please try again later']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Connection Error']);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
              'status' => 'required|in:ACCEPTED,STARTED,ARRIVED,PICKEDUP,DROPPED,PAYMENT,COMPLETED',
           ]);

        try{
            $current_time=Carbon::now()->format('H:i:s');

            $UserRequest = UserRequests::with('user')->findOrFail($id);

            if($request->status == 'DROPPED' && $UserRequest->payment_mode != 'CASH') {
                // $UserRequest->status = 'COMPLETED';
                $UserRequest->paid = 0;
                (new SendPushNotification)->Complete($UserRequest);
            } else if ($request->status == 'COMPLETED' && $UserRequest->payment_mode == 'CASH') {
                $UserRequest->status = $request->status;
                $UserRequest->paid = 1;
                    
                    if($UserRequest->ride_option == 'Instant')
                    {
                        $UserRequest->user_rated = 1;
                    }
                
                (new SendPushNotification)->Complete($UserRequest);
            } 
            else if($request->status == 'ARRIVED'){
                $UserRequest->status = $request->status;
                $UserRequest->driver_reached_time = $current_time;   

                //-----Calcualting eta_discount
                $accept_time = strtotime($UserRequest->driver_accept_time);
                $reached_time = strtotime($current_time);
                $diff = $reached_time - $accept_time;
                $estimate_mins =$UserRequest->arrival_estimate_time;
                $diff_mins=abs($diff/60);
                if($diff_mins>$estimate_mins){
                    $mins_discount=Setting::get('eta_discount',0);
                    $delay_time=round($diff_mins-$estimate_mins);
                    $eta_discount=$delay_time*$mins_discount;

                    if($UserRequest->driver_accept_time)
                    {
                        $UserRequest->eta_discount = $eta_discount;
                    }
                    else
                    {
                        $UserRequest->eta_discount = 0;
                    }
                }

                (new SendPushNotification)->Arrived($UserRequest);
            }else {
                $UserRequest->status = $request->status;                
            }

            if($request->status == 'PICKEDUP'){
                if($UserRequest->is_track == "YES"){
                   $UserRequest->distance  = 0; 
                }
                $UserRequest->started_at = Carbon::now();
                (new SendPushNotification)->Pickedup($UserRequest);
            }

            $UserRequest->save();

            if($request->status == 'DROPPED') {
                if($UserRequest->is_track == "YES"){
                    $UserRequest->d_latitude = $request->latitude?:$UserRequest->d_latitude;
                    $UserRequest->d_longitude = $request->longitude?:$UserRequest->d_longitude;
                    $UserRequest->d_address =  $request->address?:$UserRequest->d_address;

                    if($request->latitude)
                    {
                         $details = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$UserRequest->s_latitude.",".$UserRequest->s_longitude."&destinations=".$request->latitude.",".$request->longitude."&mode=driving&sensor=false&key=".Setting::get('map_key');


                        $json = curl($details);

                        $details = json_decode($json, TRUE);

                        $meter = $details['rows'][0]['elements'][0]['distance']['value'];
                        $time = $details['rows'][0]['elements'][0]['duration']['text'];
                        $seconds = $details['rows'][0]['elements'][0]['duration']['value'];

                        $kilometer = round($meter/1000);
                        $minutes = round($seconds/60);

                        $UserRequest->distance = $kilometer;
                        // $UserRequest->travel_time = $minutes;
                        $UserRequest->geo_fencing_distance = $kilometer;
                        // $UserRequest->geo_time = $minutes;
                    } 
                }
                else
                { 

                    $UserRequest->d_latitude = $request->latitude?:$UserRequest->d_latitude;
                    $UserRequest->d_longitude = $request->longitude?:$UserRequest->d_longitude;
                    $UserRequest->d_address =  $request->address?:$UserRequest->d_address;

                    $UserRequest->save();


                        $details = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$UserRequest->s_latitude.",".$UserRequest->s_longitude."&destinations=".$UserRequest->d_latitude.",".$UserRequest->d_longitude."&mode=driving&sensor=false&key=".Setting::get('map_key');

                        $json = curl($details);

                        $details = json_decode($json, TRUE);

                        $meter = $details['rows'][0]['elements'][0]['distance']['value'];
                        $time = $details['rows'][0]['elements'][0]['duration']['text'];
                        $seconds = $details['rows'][0]['elements'][0]['duration']['value'];

                        $kilometer = round($meter/1000);
                        $minutes = round($seconds/60);

                        $UserRequest->distance = $kilometer;
                        // $UserRequest->travel_time = $minutes;
                        $UserRequest->geo_fencing_distance = $kilometer;
                        // $UserRequest->geo_time = $minutes;
                     

                }
                $UserRequest->finished_at = Carbon::now();

                $StartedDate  = date_create($UserRequest->started_at);
                $FinisedDate  = Carbon::now();
                $TimeInterval = date_diff($StartedDate,$FinisedDate); 
                $MintuesTime  = ($TimeInterval->h*60) + $TimeInterval->i;
                $UserRequest->travel_time = $MintuesTime;
                $UserRequest->geo_time = $MintuesTime;
		$UserRequest->status = $request->status;
                $UserRequest->save();
                $UserRequest->with('user')->findOrFail($id);
                $UserRequest->invoice = $this->invoice($id, $request);
               
                (new SendPushNotification)->Dropped($UserRequest);

                if($UserRequest->invoice_email!='')
                {
                    Helper::site_sendmail($UserRequest);
                }

                
            }

           
            // Send Push Notification to User
       
            return $UserRequest;

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to update, Please try again later']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Connection Error']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   public function destroy($id)
  {
     $UserRequest = UserRequests::find($id);

     $requestdelete = RequestFilter::where('request_id' , $id)
                                     ->where('provider_id' , Auth::user()->id)
                                     ->delete();

     try {
         if(Setting::get('broadcast_request') == 1){
             return response()->json(['message' => 'Request Rejected Successfully']);
         }else{
             $this->assign_next_provider($UserRequest->id);
             return $UserRequest->with('user')->get();
         }

     } catch (ModelNotFoundException $e) {
         return response()->json(['error' => 'Unable to reject, Please try again later']);
     } catch (Exception $e) {
         return response()->json(['error' => 'Connection Error']);
     }
  }

  public function test(Request $request)
    {
         //$push =  (new SendPushNotification)->IncomingRequest($request->id); 
         $push = (new SendPushNotification)->Arrived($request->user_id);

         dd($push);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function assign_destroy($id)
    {
        $UserRequest = UserRequests::find($id);
        try {
            UserRequests::where('id', $UserRequest->id)->update(['status' => 'CANCELLED']);
            // No longer need request specific rows from RequestMeta
            RequestFilter::where('request_id', $UserRequest->id)->delete();
            //  request push to user provider not available
            (new SendPushNotification)->ProviderNotAvailable($UserRequest->user_id);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Unable to reject, Please try again later']);
        } catch (Exception $e) {
            return response()->json(['error' => 'Connection Error']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

    public function assign_next_provider($request_id) {

        try {
            $UserRequest = UserRequests::findOrFail($request_id);
        } catch (ModelNotFoundException $e) {
            // Cancelled between update.
            return false;
        }

        $RequestFilter = RequestFilter::where('provider_id', $UserRequest->current_provider_id)
            ->where('request_id', $UserRequest->id)
            ->delete();

        try {

            $next_provider = RequestFilter::where('request_id', $UserRequest->id)
                            ->orderBy('id')
                            ->firstOrFail();

            $UserRequest->current_provider_id = $next_provider->provider_id;
            $UserRequest->assigned_at = Carbon::now();
            $UserRequest->save();

            // incoming request push to provider
            (new SendPushNotification)->IncomingRequest($next_provider->provider_id);
            
        } catch (ModelNotFoundException $e) {

            UserRequests::where('id', $UserRequest->id)->update(['status' => 'CANCELLED']);

            // No longer need request specific rows from RequestMeta
            RequestFilter::where('request_id', $UserRequest->id)->delete();

            //  request push to user provider not available
            (new SendPushNotification)->ProviderNotAvailable($UserRequest->user_id);
        }
    }

    public function invoice($request_id, $request)
    {

        try {

            $driver_beta=0;
 
            $UserRequest = UserRequests::findOrFail($request_id);
            $tax_percentage = Setting::get('tax_percentage');
            $commission_percentage = Setting::get('commission_percentage');
            $provider_commission_percentage = Setting::get('provider_commission_percentage');
            $service_type = ServiceType::findOrFail($UserRequest->service_type_id); 


            $service_type_id = $UserRequest->service_type_id;
            $geo_fencing_service_type = GeoFencing::with(
                    ['service_geo_fencing' =>  function ($query) use ($service_type_id) {
                            $query->where('service_type_id',$service_type_id);
                   } ])->whereid($UserRequest->geo_fencing_id)->first();


            //////// ------------Peak Time Calculation--------------------//////////

                 //// peak Time Variable

                $peak_time = 0; 
                $non_peak_time_charge = 0;



                //// peak Time Variable


                $start_time = date('h:i A', strtotime($UserRequest->started_at));

                 $finish_time = date('h:i A', strtotime($UserRequest->finished_at));

                 $travel_time = Carbon::parse($start_time)->diffInMinutes($finish_time);
      
                $time_check_start = Time::where('from_time', '<=' ,$start_time)->where('to_time', '>=' ,$start_time)->first();

                $time_check_finish = Time::where('from_time', '<=' ,$finish_time)->where('to_time', '>=' ,$finish_time)->first();
                
                $non_peak_time_charge=$geo_fencing_service_type->service_geo_fencing->minute;


                if(count($time_check_start)==1 && count($time_check_finish)==0){

                  $f = Carbon::createFromFormat('h:i A', $time_check_start->to_time)->format('h:i:s');
                  $s = Carbon::createFromFormat('h:i A', $start_time)->format('h:i:s');


                  $s = Carbon::parse($s)->diffInMinutes($f);

                  $non_peak_minute = $travel_time - $s;

                  $non_peak_time_charge=$non_peak_minute * $geo_fencing_service_type->service_geo_fencing->minute;

                  $timeprice = TimePrice::where('service_id',$UserRequest->service_type_id)->where('time_id',$time_check_start->id)->first();

                  if($timeprice){

                    $peak_time = $s * $timeprice->peak_price;

                  }else{
                    $peak_time = 0;
                  }

                }elseif(count($time_check_finish)==1 && count($time_check_start)==0){


                  $s = Carbon::createFromFormat('h:i A', $time_check_finish->from_time)->format('h:i:s');
                  $f = Carbon::createFromFormat('h:i A', $finish_time)->format('h:i:s');

                  $s = Carbon::parse($s)->diffInMinutes($f);

                  $non_peak_minute = $travel_time - $s;

                  $non_peak_time_charge=$non_peak_minute * $geo_fencing_service_type->service_geo_fencing->minute;

                  $timeprice = TimePrice::where('service_id',$UserRequest->service_type_id)->where('time_id',$time_check_finish->id)->first();

                  if($timeprice){
                    $peak_time = $s * $timeprice->peak_price;

                  }else{
                    $peak_time = 0;
                  }

                }elseif(count($time_check_start)==1 && count($time_check_finish)==1){

                    $s = Carbon::createFromFormat('h:i A', $start_time)->format('h:i:s');
                    $f = Carbon::createFromFormat('h:i A', $finish_time)->format('h:i:s');

                   $s = Carbon::parse($s)->diffInMinutes($f);


                  $non_peak_time_charge=0;

                   $timeprice = TimePrice::where('service_id',$UserRequest->service_type_id)->where('time_id',$time_check_finish->id)->first();

                      if($timeprice){
                        $peak_time = $s * $timeprice->peak_price;


                      }else{
                        $peak_time = 0;
                      }




                }else{

                    $non_peak_time_charge=$geo_fencing_service_type->service_geo_fencing->minute*$travel_time;
                    $peak_time = 0;

                }


                $total_peak_minute_charge = $peak_time+$non_peak_time_charge;
//dd($peak_time);
             //////// -----------------Peak Time Calculation ------------ /////////



            // $service_type_id = $UserRequest->service_type_id;

            $geo_price = 0;
            $percent_cal = 0;
            $non_geo_price = 0;

            $geo_min_price = 0;
            $non_geo_min_price = 0;

            $geo_distance_price = 0;
            $non_geo_distance_price = 0;

            $new_non_geo_price = 0;
            $return_travel_fare = 0;

           


            if($UserRequest->geo_fencing_id)
            {


                // $geo_fencing_service_type = GeoFencing::with(
                //     ['service_geo_fencing' =>  function ($query) use ($service_type_id) {
                //             $query->where('service_type_id',$service_type_id);
                //    } ])->whereid($UserRequest->geo_fencing_id)->first();

                /// Fully Geo Fencing flow calculation

               // if total distance and geo fencing is equals

                if($UserRequest->geo_fencing_distance==$UserRequest->distance)
                {
 

                    $ride_kilometer = $UserRequest->distance;
                    $free_kilometer = $geo_fencing_service_type->service_geo_fencing->distance;
                    $kilometer = $ride_kilometer - $free_kilometer;

                    if($kilometer < 0)
                        {
                            $kilometer = 0;
                        }
                    
                    $minutes = $UserRequest->travel_time;
           
                    $hour_min = $minutes/60 ;
                    //$hour_min = $UserRequest->rental_hours;
                    $hour = ceil($hour_min);

                    $Fixed = $service_type->fixed;
                    $Distance = 0;
                    $Discount = 0; // Promo Code discounts should be added here.
                    $Wallet = 0;
                    $Surge = 0;
                    $ProviderCommission = 0;
                    $ProviderPay = 0;

                    if($service_type->calculator == 'MIN') {
                        // $Distance = $geo_fencing_service_type->service_geo_fencing->minute * $minutes;
                        // $geo_min_price = $Distance; 
                        $Distance = $total_peak_minute_charge;
                        $geo_min_price = $total_peak_minute_charge; 
                    } else if($service_type->calculator == 'HOUR') {
                        $Distance = $geo_fencing_service_type->service_geo_fencing->minute * $hour;
                    } else if($service_type->calculator == 'DISTANCE') {
                        $Distance = ($kilometer * $geo_fencing_service_type->service_geo_fencing->price);
                        $geo_distance_price = $Distance;
                    } else if($service_type->calculator == 'DISTANCEMIN') {
                        // $Distance = ($kilometer * $geo_fencing_service_type->service_geo_fencing->price) + ($geo_fencing_service_type->service_geo_fencing->minute * $minutes);
                        // $geo_min_price = ($geo_fencing_service_type->service_geo_fencing->minute * $minutes);

                        $Distance = ($kilometer * $geo_fencing_service_type->service_geo_fencing->price) + ($total_peak_minute_charge);
                        $geo_min_price = $total_peak_minute_charge;

                        $geo_distance_price = $kilometer * $geo_fencing_service_type->service_geo_fencing->price;
  

                    } else if($service_type->calculator == 'DISTANCEHOUR') {
                        $Distance = ($kilometer * $geo_fencing_service_type->service_geo_fencing->price) + ($geo_fencing_service_type->service_geo_fencing->minute * $hour);
                    } else {
                        $Distance = ($kilometer * $geo_fencing_service_type->service_geo_fencing->price);
                    }

                    $geo_price = $Distance;


                    // $check =$this->poly_check_final_latlng((round($UserRequest->d_latitude,6)),(round($UserRequest->d_longitude,6)));

                    // if($check == 'no')
                    // {
                    //     $new_non_geo_price = $geo_fencing_service_type->service_geo_fencing->non_geo_price;
                    // }


                     $Distance_fare = $kilometer * $geo_fencing_service_type->service_geo_fencing->price ;
                     $Minute_fare = $geo_fencing_service_type->service_geo_fencing->minute * $minutes ;

                }


                // If geo fencing is not equals to distance

                else
                { 
                    /// Half Geo Fencing flow calculation
                    $Distance_fare =0;
                    $Minute_fare =0;


                    //dd($UserRequest->geo_fencing_distance);
                    if($UserRequest->geo_fencing_distance)
                    {


                        $ride_kilometer_geo = $UserRequest->geo_fencing_distance;
                     
                        $free_kilometer_geo = $geo_fencing_service_type->service_geo_fencing->distance;

                        //dd($free_kilometer_geo );

                        $kilometer_geo = $ride_kilometer_geo - $free_kilometer_geo;

                        if($kilometer_geo < 0)
                        {
                            $kilometer_geo = 0;
                        }
                        
                        $minutes_geo = $UserRequest->geo_time;
               
                        $hour_min_geo = $minutes_geo/60 ;
                        //$hour_min = $UserRequest->rental_hours;
                        $hour_geo = ceil($hour_min_geo);

                        $Fixed_geo = $service_type->fixed;
                        $Distance_geo = 0;
                        $Discount_geo = 0; // Promo Code discounts should be added here.
                        $Wallet_geo = 0;
                        $Surge_geo = 0;
                        $ProviderCommission_geo = 0;
                        $ProviderPay_geo = 0;

                        if($service_type->calculator == 'MIN') {
                            // $Distance_geo = $geo_fencing_service_type->service_geo_fencing->minute * $minutes_geo;
                            // $geo_min_price = $Distance_geo; 

                            $Distance_geo = $total_peak_minute_charge;
                            $geo_min_price = $total_peak_minute_charge; 


                        } else if($service_type->calculator == 'HOUR') {
                            $Distance_geo = $geo_fencing_service_type->service_geo_fencing->minute * $hour_geo;
                        } else if($service_type->calculator == 'DISTANCE') {
                            $Distance_geo = ($kilometer_geo * $geo_fencing_service_type->service_geo_fencing->price);
                            $geo_distance_price = $Distance_geo;
                        } else if($service_type->calculator == 'DISTANCEMIN') {
                            // $Distance_geo = ($kilometer_geo * $geo_fencing_service_type->service_geo_fencing->price) + ($geo_fencing_service_type->service_geo_fencing->minute * $minutes_geo);

                            // $geo_min_price = ($geo_fencing_service_type->service_geo_fencing->minute * $minutes_geo);

                            $Distance_geo = ($kilometer_geo * $geo_fencing_service_type->service_geo_fencing->price) + ($total_peak_minute_charge);

                            $geo_min_price = $total_peak_minute_charge;

                            $geo_distance_price = $kilometer_geo * $geo_fencing_service_type->service_geo_fencing->price;

                        } else if($service_type->calculator == 'DISTANCEHOUR') {
                            $Distance_geo = ($kilometer_geo * $geo_fencing_service_type->service_geo_fencing->price) + ($geo_fencing_service_type->service_geo_fencing->minute * $hour_geo);
                        } else {
                            $Distance_geo = ($kilometer_geo * $geo_fencing_service_type->service_geo_fencing->price);
                        }

                        $geo_price = $Distance_geo; 




                         $Distance_fare = $kilometer_geo * $geo_fencing_service_type->service_geo_fencing->price ;
                         $Minute_fare = $geo_fencing_service_type->service_geo_fencing->minute * $minutes_geo ;
                        
                    }

                  
                    /// Half Non Geo Fencing flow calculation

                    $ride_kilometer = $UserRequest->distance - $UserRequest->geo_fencing_distance; 
                    $kilometer = $ride_kilometer;

                    if($kilometer < 0)
                    {
                        $kilometer = 0;
                    }
                    
                    $minutes = $UserRequest->travel_time - $UserRequest->geo_time;

                    if($minutes<0)
                    {
                        $minutes =0;
                    }
           
                    $hour_min = $minutes/60 ;
                    //$hour_min = $UserRequest->rental_hours;
                    $hour = ceil($hour_min);

                     
                    $Fixed = $service_type->fixed;
                    $Distance = 0;
                    $Discount = 0; // Promo Code discounts should be added here.
                    $Wallet = 0;
                    $Surge = 0;
                    $ProviderCommission = 0;
                    $ProviderPay = 0;

                    if($service_type->calculator == 'MIN') {

                        // $Distance = $service_type->minute * $minutes;
                        // $non_geo_min_price = $Distance;

                        $Distance = $total_peak_minute_charge;
                        $non_geo_min_price = $total_peak_minute_charge;

                    } else if($service_type->calculator == 'HOUR') {
                        $Distance = $service_type->minute * $hour;
                    } else if($service_type->calculator == 'DISTANCE') {
                        $Distance = ($kilometer * $service_type->price);
                        $non_geo_distance_price = $Distance;
                    } else if($service_type->calculator == 'DISTANCEMIN') {
                        // $Distance = ($kilometer * $service_type->price) + ($service_type->minute * $minutes);
                        // $non_geo_min_price = ($service_type->minute * $minutes);

                        $Distance = ($kilometer * $service_type->price) + ($total_peak_minute_charge);
                        $non_geo_min_price = ($total_peak_minute_charge);

                        $non_geo_distance_price = $kilometer * $service_type->price;

                    } else if($service_type->calculator == 'DISTANCEHOUR') {
                        $Distance = ($kilometer * $service_type->price) + ($service_type->minute * $hour);
                    } else {
                        $Distance = ($kilometer * $service_type->price);
                    }
                    
                    $non_geo_price = $Distance;

                    $Distance_fare += $kilometer * $service_type->price ;
                    $Minute_fare += $service_type->minute * $minutes ; 
                }

                //Return Travel Fare

                $total_ride_price = ($non_geo_price + $geo_price + $Fixed);

                // if(!empty($geo_fencing_service_type->service_geo_fencing->city_limits) && $geo_fencing_service_type->service_geo_fencing->city_limits < $UserRequest->distance)
                // {
                //     $return_travel_fare = $total_ride_price;    
                //     $total_ride_price = $total_ride_price*2;
                // }

            }
            else
            {
                /// Normal flow calculation

                $ride_kilometer = $UserRequest->distance;
                $free_kilometer = $service_type->distance;
                $kilometer = $ride_kilometer - $free_kilometer;
                
                if($kilometer < 0)
                {
                    $kilometer = 0;
                }

                $minutes = $UserRequest->travel_time;
       
                $hour_min = $minutes/60 ;
                //$hour_min = $UserRequest->rental_hours;
                $hour = ceil($hour_min);



                 
                $Fixed = $service_type->fixed;
                $Distance = 0;
                $Discount = 0; // Promo Code discounts should be added here.
                $Wallet = 0;
                $Surge = 0;
                $ProviderCommission = 0;
                $ProviderPay = 0;

                if($service_type->calculator == 'MIN') {
                    // $Distance = $service_type->minute * $minutes;
                    // $non_geo_min_price = ($service_type->minute * $minutes);

                    $Distance = $total_peak_minute_charge;
                    $non_geo_min_price = ($total_peak_minute_charge);

                } else if($service_type->calculator == 'HOUR') {
                    $Distance = $service_type->minute * $hour;
                } else if($service_type->calculator == 'DISTANCE') {
                    $Distance = ($kilometer * $service_type->price);
                    $non_geo_distance_price = $Distance;
                } else if($service_type->calculator == 'DISTANCEMIN') {
                    // $Distance = ($kilometer * $service_type->price) + ($service_type->minute * $minutes);
                    // $non_geo_min_price = ($service_type->minute * $minutes);

                    $Distance = ($kilometer * $service_type->price) + ($total_peak_minute_charge);
                    $non_geo_min_price = ($total_peak_minute_charge);

                    $non_geo_distance_price = $kilometer * $service_type->price;
                } else if($service_type->calculator == 'DISTANCEHOUR') {
                    $Distance = ($kilometer * $service_type->price) + ($service_type->minute * $hour);
                } else {
                    $Distance = ($kilometer * $service_type->price);
                }

                $Distance_fare = $kilometer * $service_type->price ;
                $Minute_fare = $service_type->minute * $minutes ;

                $non_geo_price = $Distance; 



                $total_ride_price = ($non_geo_price + $geo_price + $Fixed);

                

            }



            
            // $total_ride_price = ($non_geo_price + $geo_price + $Fixed);

            // if(!empty($service_type->between_km) && $service_type->between_km < $UserRequest->distance)
            // {
            //     $return_travel_fare = $total_ride_price;    
            //     $total_ride_price = $total_ride_price*2;
            // }


         $dis = 0;
        
            if($UserRequest->service_required=="rental"){
                $rental_hour_package = ServiceRentalHourPackage::findOrFail($UserRequest->rental_hours);
                if($rental_hour_package->hour < $hour || $rental_hour_package->km < $UserRequest->distance)
                {   
                    $extra_hour_price = 0;
                    $extra_km_price = 0;
                    if($rental_hour_package->hour < $hour)
                    {
                        $extra_hour_price = ($service_type->rental_hour_price*($hour-$rental_hour_package->hour));
                    }
                    if($rental_hour_package->km < $UserRequest->distance)
                    {
                        $extra_km_price = ($service_type->rental_km_price*($UserRequest->distance-$rental_hour_package->km));
                    }

                    $total_ride_price = $extra_hour_price+$extra_km_price+$rental_hour_package->price;
                    
                    
                } 
                else
                {
                    $total_ride_price = $rental_hour_package->price;
                }
                // $total_ride_price = $hour * $service_type->rental_fare;
                $dis = $total_ride_price;

            }elseif($UserRequest->service_required=="outstation"){

                $StartedDate  = date_create($UserRequest->started_at);
                $FinisedDate  = date_create($UserRequest->finished_at);
                $TimeInterval = date_diff($StartedDate,$FinisedDate);
                $TimeInterval = $TimeInterval->days+1;

                

                $total_ride_price = (($kilometer * $service_type->outstation_km)+($service_type->outstation_driver * $TimeInterval));
                $dis = $kilometer * $service_type->outstation_km;

                $driver_beta = $service_type->outstation_driver * $TimeInterval;

            }
             //Reduced total with ETA discount    
             $total_ride_price = $total_ride_price-$UserRequest->eta_discount;

             $Commision = $total_ride_price * ( $commission_percentage/100 );
             $Tax = $total_ride_price * ( $tax_percentage/100 );
             $ProviderCommission = $total_ride_price * ( $provider_commission_percentage/100 );
             $ProviderPay = $total_ride_price - $ProviderCommission;


            $current_time = date("h:i a");
            $sunrise = "8:00 am";
            $sunset = "10:00 pm";

            $date1 = DateTime::createFromFormat('H:i a', $current_time);
            $date2 = DateTime::createFromFormat('H:i a', $sunrise);
            $date3 = DateTime::createFromFormat('H:i a', $sunset);

          
            if ($date1 > $date2 && $date1 < $date3)
            {
                $Total = $total_ride_price + $Tax;  
            }
            else
            {
                $service_percentage =  $service_type->night_fare;
                
                $Total = $total_ride_price + $Tax;
                $percent_cal =  number_format(($Total * ($service_percentage/100)),2);
                $Total =  $percent_cal + $Total ;
        
            }









            if($PromocodeUsage = PromocodeUsage::with('promocode')->where('user_id',$UserRequest->user_id)->where('status','ADDED')->first())
            {
                if($Promocode = Promocode::find($PromocodeUsage->promocode_id)){
                    $Discount = $Promocode->discount;
                    $PromocodeUsage->status ='USED';
                    $PromocodeUsage->save();

                    PromocodePassbook::create([
                            'user_id' => Auth::user()->id,
                            'status' => 'USED',
                            'promocode_id' => $PromocodeUsage->promocode_id
                        ]);
                }

                if($PromocodeUsage->promocode->discount_type=='amount'){
                
                 
                   // $Total = $total_ride_price + $Tax;
                   // $payable_amount = $total_ride_price + $Tax - $Discount;
                    $payable_amount = $Total - $Discount;

                }else{

                   // $Total = $total_ride_price + $Tax;
                    $payable_amount = ($Total)-(($Fixed + $Distance + $Tax) * ($Discount/100));
                    $Discount = (($Total) * ($Discount/100));

                }

            }else{
                
               // $Total = $total_ride_price + $Tax;
                $payable_amount = $Total - $Discount;
            }

            
            if($UserRequest->surge){
                $Surge = (Setting::get('surge_percentage')/100) * $payable_amount;
                $Total += $Surge;
                $payable_amount += $Surge;
            }

            if($Total < 0){
                $Total = 0.00; // prevent from negative value
                $payable_amount = 0.00;
            }


            $total_min_price =0;
            $total_distance_price =0;

            $Payment = new UserRequestPayment;
            $Payment->request_id = $UserRequest->id;
            $Payment->night_fare = $percent_cal;
            $Payment->percentage = $service_type->night_fare;
            //eta_discount added
            $Payment->eta_discount = $UserRequest->eta_discount;

            $Payment->driver_beta =$driver_beta;

            /*
            * Reported by Jeya, We are adding the surge price with Base price of Service Type.
            */ 
            $Payment->fixed = $Fixed + $Surge; 
            
            if($geo_min_price){
            $Payment->geo_fencing_minute  = $geo_min_price;
            $total_min_price = $geo_min_price;
            }
            if($non_geo_min_price){
            $Payment->non_geo_fencing_minute  = $non_geo_min_price;
            $total_min_price += $non_geo_min_price;
             }

            $Payment->minute  = $total_min_price;
            
            if($UserRequest->service_required=="rental"){
                $Payment->minute =$dis;
                $Payment->extra_hour_price =$extra_hour_price;
                $Payment->extra_km_price =$extra_km_price;
            }


            $Payment->commision = $Commision;
            $Payment->surge = $Surge;
            if($geo_distance_price){
            $Payment->geo_fencing_total = $geo_distance_price;
            $total_distance_price = $geo_distance_price;
            }
            if($non_geo_distance_price){
            $Payment->none_geo_fencing_total = $non_geo_distance_price;
            $total_distance_price += $non_geo_distance_price;
            }

            if($new_non_geo_price){
            $Payment->non_geo_price  = $new_non_geo_price;
             }

            $Payment->distance = $total_distance_price;

            if($UserRequest->service_required=="outstation"){
                $Payment->distance =$dis;
            }
            
            if($return_travel_fare)
            {
            $Payment->return_travel_fare = $return_travel_fare;
            }

            $Payment->total = $Total;
            $Payment->provider_commission = $ProviderCommission;
            $Payment->provider_pay = $ProviderPay;
            if($Discount != 0 && $PromocodeUsage){
                $Payment->promocode_id = $PromocodeUsage->promocode_id;
            }
            $Payment->discount = $Discount;

            if($Discount  == ($Fixed + $Distance + $Tax)){
                $UserRequest->paid = 1;
            }

            if($UserRequest->use_wallet == 1 && $payable_amount > 0){

                $User = User::find($UserRequest->user_id);

                $Wallet = $User->wallet_balance;

                if($Wallet != 0){

                    if($payable_amount > $Wallet) {

                        $Payment->wallet = $Wallet;
                        $Payable = $payable_amount - $Wallet;
                        User::where('id',$UserRequest->user_id)->update(['wallet_balance' => 0 ]);
                        $Payment->payable = abs($Payable);

                        WalletPassbook::create([
                          'user_id' => $UserRequest->user_id,
                          'amount' => $Wallet,
                          'status' => 'DEBITED',
                          'via' => 'TRIP',
                        ]);

                        // charged wallet money push 
                        (new SendPushNotification)->ChargedWalletMoney($UserRequest->user_id,currency($Wallet));

                    } else {

                        $Payment->payable = 0;
                        $WalletBalance = $Wallet - $payable_amount;
                        User::where('id',$UserRequest->user_id)->update(['wallet_balance' => $WalletBalance]);
                        $Payment->wallet = $payable_amount;
                        
                        $Payment->payment_id = 'WALLET';
                        $Payment->payment_mode = $UserRequest->payment_mode;

                        $UserRequest->paid = 1;
                        $UserRequest->status = 'COMPLETED';
                        $UserRequest->save();

                        WalletPassbook::create([
                          'user_id' => $UserRequest->user_id,
                          'amount' => $payable_amount,
                          'status' => 'DEBITED',
                          'via' => 'TRIP',
                        ]);

                        // charged wallet money push 
                        (new SendPushNotification)->ChargedWalletMoney($UserRequest->user_id,currency($payable_amount));
                    }

                }

            } else {
                $Payment->total = abs($Total);
                $Payment->payable = abs($payable_amount);
                
            }

            $Payment->peak_price = $total_peak_minute_charge;

            $Payment->tax = $Tax;
            $Payment->save();

            if($UserRequest->payment_mode != 'CASH' && $request->status == 'DROPPED') {
                $UserRequest->status = 'COMPLETED';
                $UserRequest->save();
            }

            return $Payment;


        } catch (ModelNotFoundException $e) {
            return false;
        }
    }

    /**
     * Get the trip history details of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function history_details(Request $request)
    {
        $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id',
            ]);

        if($request->ajax()) {
            
            $Jobs = UserRequests::where('id',$request->request_id)
                                ->where('provider_id', Auth::user()->id)
                                ->with('payment','service_type','user','rating')
                                ->get();
            if(!empty($Jobs)){
                $map_icon_start = asset('asset/img/marker-start.png');
                $map_icon_end = asset('asset/img/marker-end.png');
                foreach ($Jobs as $key => $value) {
                    $Jobs[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?".
                            "autoscale=1".
                            "&size=600x300".
                            "&maptype=terrian".
                            "&format=png".
                            "&visual_refresh=true".
                            "&markers=icon:".$map_icon_start."%7C".$value->s_latitude.",".$value->s_longitude.
                            "&markers=icon:".$map_icon_end."%7C".$value->d_latitude.",".$value->d_longitude.
                            "&path=color:0x000000|weight:3|enc:".$value->route_key.
                            "&key=".Setting::get('map_key');
                }
            }

            return $Jobs[0];
        }

    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function upcoming_trips() {
    
        try{
            $UserRequests = UserRequests::ProviderUpcomingRequest(Auth::user()->id)->get();
            if(!empty($UserRequests)){
                $map_icon = asset('asset/marker.png');
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
     * Get the trip history details of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function upcoming_details(Request $request)
    {
        $this->validate($request, [
                'request_id' => 'required|integer|exists:user_requests,id',
            ]);

        if($request->ajax()) {
            
            $Jobs = UserRequests::where('id',$request->request_id)
                                ->where('provider_id', Auth::user()->id)
                                ->with('service_type','user','payment')
                                ->get();
            if(!empty($Jobs)){
                $map_icon_start = asset('asset/img/marker-start.png');
                $map_icon_end = asset('asset/img/marker-end.png');
                foreach ($Jobs as $key => $value) {
                    $Jobs[$key]->static_map = "https://maps.googleapis.com/maps/api/staticmap?".
                            "autoscale=1".
                            "&size=600x300".
                            "&maptype=terrian".
                            "&format=png".
                            "&visual_refresh=true".
                            "&markers=icon:".$map_icon_start."%7C".$value->s_latitude.",".$value->s_longitude.
                            "&markers=icon:".$map_icon_end."%7C".$value->d_latitude.",".$value->d_longitude.
                            "&path=color:0x000000|weight:3|enc:".$value->route_key.
                            "&key=".Setting::get('map_key');
                }
            }

            return $Jobs[0];
        }

    }

    /**
     * Get the trip history details of the provider
     *
     * @return \Illuminate\Http\Response
     */
    public function summary(Request $request)
    {
        try{
            if($request->ajax()) {
                $rides = UserRequests::where('provider_id', Auth::user()->id)->count();
                $revenue_total = UserRequestPayment::whereHas('request', function($query) use ($request) {
                                $query->where('provider_id', Auth::user()->id);
                            })
                        ->sum('total');
                 $revenue_commission = UserRequestPayment::whereHas('request', function($query) use ($request) {
                                $query->where('provider_id', Auth::user()->id);
                            })
                        ->sum('provider_commission');
                $tax = UserRequestPayment::whereHas('request', function($query) use ($request) {
                        $query->where('provider_id', Auth::user()->id);
                    })
                ->sum('tax');  

                 $revenue =  $revenue_total - $revenue_commission - $tax;              

                $cancel_rides = UserRequests::where('status','CANCELLED')->where('provider_id', Auth::user()->id)->count();
                $scheduled_rides = UserRequests::where('status','SCHEDULED')->where('provider_id', Auth::user()->id)->count();

                return response()->json([
                    'rides' => $rides, 
                    'revenue' => $revenue,
                    'cancel_rides' => $cancel_rides,
                    'scheduled_rides' => $scheduled_rides,
                ]);
            }

        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')]);
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

    /**
     * Geo Fencing live track updation
     *
     * @return \Illuminate\Http\Response
     */

    public function geo_fencing_live_track(Request $request){

        try{
            
            if($request->ajax()) {
 
            $Userrequest = UserRequests::with('provider')->findOrFail($request->request_id);
            
            $Userrequest->provider->update(["latitude" => $request->latitude,"longitude" => $request->longitude]);

                if($Userrequest->geo_fencing_id !=0)
                {

                //Check live latitute and langitute to geo fencing data

                $geo_check = $this->poly_check_new((round($request->latitude,6)),(round($request->longitude,6)),$Userrequest->geo_fencing_id);


                if($geo_check=="inside")
                { 

                // Get live track kilometer for provider

                // $details = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$Userrequest->s_latitude.",".$Userrequest->s_longitude."&destinations=".$request->latitude.",".$request->longitude."&mode=driving&sensor=false&key=".Setting::get('map_key');

                // $json = curl($details);

                // $details = json_decode($json, TRUE);

                // $meter = $details['rows'][0]['elements'][0]['distance']['value'];
                // $time = $details['rows'][0]['elements'][0]['duration']['text'];
                // $seconds = $details['rows'][0]['elements'][0]['duration']['value'];

                // $kilometer = round($meter/1000);
                // $minutes = round($seconds/60);

                //// Calculate distance and geo fencin distance as same function used because of same kilometer coming
                    Log::info(" Geo  ---- REQUEST ID:".$Userrequest->id."==SOURCE LATITUDE:".$Userrequest->track_latitude."==SOURCE LONGITUDE:".$Userrequest->track_longitude."==Destination lat :".$request->latitude."==Destination lang :".$request->longitude);

                    $coordinate1 = new Coordinate($Userrequest->track_latitude, $Userrequest->track_longitude); /** Set Distance Calculation Source Coordinates ****/
                    $coordinate2 = new Coordinate($request->latitude, $request->longitude); /** Set Distance calculation Destination Coordinates ****/

                    $calculator = new Vincenty();

                    /**Distance between two coordinates using spherical algorithm (library as mjaschen/phpgeo) **/

                    $mydistance = $calculator->getDistance($coordinate1, $coordinate2); 

                    $meters = round($mydistance);

                    if($Userrequest->track_latitude){
                        $currentdate=\Carbon\Carbon::now();
                        if($Userrequest->updated_at != $currentdate) {
                            if($meters >= 100){

                                /*** If traveled distance riched houndred meters means to be the source coordinates ***/
                                $traveldistance = round(($meters/1000),8);
                                Log::info('geo Tranvel distance'.$traveldistance);

                                $calulatedistance = $Userrequest->geo_fencing_distance + $traveldistance;
                                Log::info('geo total distance'.$calulatedistance);

                                 $Userrequest->geo_fencing_distance = $calulatedistance;
          
                           
                            }
                            else
                            {
                                $traveldistance = round(($meters/1000),8);
                                Log::info('geo Tranvel distance'.$traveldistance);

                                $calulatedistance = $Userrequest->geo_fencing_distance + $traveldistance;
                                Log::info('geo total distance'.$calulatedistance);

                                $Userrequest->geo_fencing_distance = $calulatedistance;
                            } 
                        }
                     }

                $start_geo_time= $Userrequest->started_at;
                $finish_geo_time=\Carbon\Carbon::now(); 

                $totalDuration = $finish_geo_time->diffInMinutes($start_geo_time);

                $Userrequest->geo_time = $totalDuration;
                $Userrequest->save(); 

                }


                }

                return response()->json(['Request' => $Userrequest]);

            }
               

        }catch (Exception $e) {
            if($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')]);
            }
        }
    }
    public function poly_check($latitude,$longitude,$geo_fencing_id)
       {
        //dd($s_latitude . $s_longitude);

           $range_data = GeoFencing::findOrFail($geo_fencing_id);
           //dd($range_data);

           //$yes = $no = [];

           // foreach($range_data as $ranges)
           // {

              // $ranges  = Setting::get('service_range');
               $polygon = [];
               $range_values = json_decode($range_data->ranges,true);
               //dd($range_values);
               foreach($range_values as $range ){

                   $polygon[] = $range['lat']." ".$range['lng'];
               }
               // /dd($polygon);

               $pointLocation = new pointLocation();


               $points = array("$latitude $longitude");
              // dd($points);
               foreach($points as $key => $point) {

                   if($pointLocation->pointInPolygon($point, $polygon) == 'vertex'){
                     return "vertex";
                    }elseif($pointLocation->pointInPolygon($point, $polygon) == 'inside'){
                        return "inside";
                    }else{
                       return "outside";
                    }
               }


           // }

        } 



    public function poly_check_new($latitude,$longitude,$geo_fencing_id)
       {
        //dd($s_latitude . $s_longitude);

           $range_data = GeoFencing::findOrFail($geo_fencing_id);

               $polygon = [];
               $range_values = json_decode($range_data->ranges,true);

                $yes = $no =  $vertices_x = $vertices_y = [];

                $longitude_x = $latitude;

                $latitude_y =  $longitude;
         
               foreach($range_values as $range ){

                    $vertices_x[] = $range['lat'];

                    $vertices_y[] = $range['lng'];

               }
           

              // $pointLocation = new pointLocation();
               
               $points_polygon = count($vertices_x) - 1; 

  
               if (is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)){
                        return "inside";
                }else{
                       return "outside";
                }
               




        } 

        public function poly_check_final_latlng($s_latitude,$s_longitude)
        {
            
                   $range_data = GeoFencing::get();
                   //dd($range_data);

                   $yes = $no =   [];

                   $longitude_x = $s_latitude;
                   
                   $latitude_y =  $s_longitude;
 
                   foreach($range_data as $ranges)
                   {

                      // if($ranges->city_name=='Chennai'){
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

         

    public function instant_ride(Request $request)
    {
        try
        {
            // $otp = $this->otp_generate();

            $Provider = Auth::user()->mobile;
            $alternate_mobile=$request->mobile;  
            // if($Provider)
            // {
            // sendsms($Provider,$otp);
            // }
            // if($request->mobile)
            // {
            // sendsms($alternate_mobile,$otp);
            // }
            
            $random = $this->otp_generate();
            $email='instantride'.$random.'@instantride.com';

            
                $User = User::where('mobile', $request->mobile)->orwhere('email', $email)->first();
                
                if(empty($User))
                {  
                        $User = User::create([
                        'first_name' => $request->name,
                        'last_name' => 'Inst-User', 
                        'mobile' => $request->mobile,
                        'email' => $email,
                        'password' => bcrypt('123456'),
                        'payment_mode' => 'CASH',
                        // 'otp' => $otp
                        ]);
                }
                // else
                // { 
                //     $User->otp =$otp;
                //     $User->save();
                // }


           $provider = ProviderService::where('provider_id',\Auth::user()->id)->first();

            $service_type = $provider->service_type_id;

            $non_geo_price = 0;

            
            $details = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$request->s_latitude.",".$request->s_longitude."&destinations=".$request->d_latitude.",".$request->d_longitude."&mode=driving&sensor=false&key=".Setting::get('map_key');

           

            

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

            $fixed_price_only = ServiceType::findOrFail($service_type);
            $geo_fencing=$this->poly_check_new_estimate((round($request->s_latitude,6)),(round($request->s_longitude,6)));

            if($geo_fencing)
            {
                $service_type_id = $service_type;
                $geo_fencing_service_type = GeoFencing::with(
                    ['service_geo_fencing' =>  function ($query) use ($service_type_id) {
                            $query->where('service_type_id',$service_type_id);
                   } ])->whereid($geo_fencing)->first(); 
                $service_type = $geo_fencing_service_type->service_geo_fencing;
            }
            else
            {
                $service_type = ServiceType::findOrFail($service_type);
            }


            $tax_percentage = Setting::get('tax_percentage');
            $commission_percentage = Setting::get('commission_percentage');
            // $service_type = ServiceType::findOrFail($service_type);

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
            
            // $price = $service_type->fixed;
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

                $price = (($kilometer * $fixed_price_only->outstation_km) + ($fixed_price_only->outstation_driver * $total_days));

                if($day == 'round')
                {
                    $price = $price * 2;
                    $kilometer = $kilometer * 2;
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
            $ActiveProviders = ProviderService::AvailableServiceProvider($service_type)->get()->pluck('provider_id');

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
            $service_type_id =$service_type;
            $geo_fencing_id=$this->poly_check_new_estimate((round($request->s_latitude,6)),(round($request->s_longitude,6)));
           
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
                    $non_geo_price = isset($geo_fencing_service_type->service_geo_fencing)?$geo_fencing_service_type->service_geo_fencing->non_geo_price:'';

               }
           


            }   





            /*
            * Reported by Jeya, previously it was hardcoded. we have changed as based on surge percentage.
            */ 
            $surge_percentage = 1+(Setting::get('surge_percentage')/100)."X";




            return response()->json(['user' => $User,'estimated_fare' => round($total,2)]);
       
        }catch (Exception $e) {
            if($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')]);
            }
        }

    }
    public function otp_generate()
    {
        $otp = mt_rand(1000, 9999); 

        return $otp;
    }

    /////------------------ Instant ride now request from driver------------ //////

    public function instant_ride_now(Request $request)
    {
        $this->validate($request, [
                's_latitude' => 'required|numeric',
                's_longitude' => 'required|numeric',
                's_address' => 'required',
                'd_latitude' => 'required|numeric',
                'd_longitude' => 'required|numeric',
                'd_address' => 'required',
                'user_id' => 'required', 
            ]);
 

        try{

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

            $geo_check = $this->poly_check_new_estimate((round($request->s_latitude,6)),(round($request->s_longitude,6))); 

            $provider =Provider::with('service')->findOrFail(Auth::user()->id);

            $details = "https://maps.googleapis.com/maps/api/directions/json?origin=".$request->s_latitude.",".$request->s_longitude."&destination=".$request->d_latitude.",".$request->d_longitude."&mode=driving&key=".Setting::get('map_key');

            $json = curl($details);

            $details = json_decode($json, TRUE);

            $route_key = $details['routes'][0]['overview_polyline']['points'];

            $UserRequest = new UserRequests;
            $UserRequest->booking_id = Helper::generate_booking_id();
            $UserRequest->user_id = $request->user_id;
            $UserRequest->ride_option = "Instant";
            $UserRequest->service_type_id = $provider->service->service_type_id;
            $UserRequest->payment_mode = 'CASH'; 

            //// ///  nwely added 
 
                $UserRequest->status = 'ARRIVED'; 
            
            ///  end nwely added

            $UserRequest->s_address = $request->s_address ? : "";
            $UserRequest->s_latitude = $request->s_latitude;
            $UserRequest->s_longitude = $request->s_longitude;

            $UserRequest->d_address = $request->d_address ? : "";
            $UserRequest->d_latitude = $request->d_latitude;
            $UserRequest->d_longitude = $request->d_longitude;
            $UserRequest->route_key = $route_key;

            $UserRequest->distance = 0;

            $UserRequest->assigned_at = Carbon::now(); /// newly comment

            $UserRequest->use_wallet = 0;
            $UserRequest->surge = 0;        // Surge is not necessary while adding a manual dispatch
            // $UserRequest->otp = mt_rand(1000, 9999);

            //Insert geo fencing id
            if($geo_check!=0)
            {
                $UserRequest->geo_fencing_id = $geo_check;
            }
   
            $UserRequest->provider_id = Auth::user()->id; 
            $UserRequest->current_provider_id = Auth::user()->id; 

            $UserRequest->save();

            sendmessage($UserRequest->id);
            
            if($UserRequest){
                $provider_service = ProviderService::whereprovider_id(Auth::user()->id)->get();
                foreach ($provider_service as $key => $value) {
                     $update = ProviderService::findOrFail($value->id);
                     $update->status='riding';
                     $update->save();
                }
            }
            
                // Incoming request push to provider
               // (new SendPushNotification)->IncomingRequest(Auth::user()->id); 
                    
                $Filter = new RequestFilter;
                $Filter->request_id = $UserRequest->id;
                $Filter->provider_id = Auth::user()->id; 
                $Filter->save();

              
            if($request->ajax()) {
                return $UserRequest;
            } else {
                return redirect('dashboard');
            }

        } catch (Exception $e) {
            //dd($e);
            if($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong'), 'message' => $e], 500);
            }else{
                return back()->with('flash_error', 'Something went wrong while sending request. Please try again.');
            }
        }

    }
    /////------------------ End Instant ride now request from driver------------ //////


        public function instant_ride_estimate(Request $request){
     
        $this->validate($request,[
                's_latitude' => 'required|numeric',
                's_longitude' => 'required|numeric',
                'd_latitude' => 'required|numeric',
                'd_longitude' => 'required|numeric',
               // 'service_type' => 'required|numeric|exists:service_types,id',
            ]);

        try{


            $provider = ProviderService::where('provider_id',\Auth::user()->id)->first();

            $service_type = $provider->service_type_id;

            $non_geo_price = 0;

            
            $details = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=".$request->s_latitude.",".$request->s_longitude."&destinations=".$request->d_latitude.",".$request->d_longitude."&mode=driving&sensor=false&key=".Setting::get('map_key');

           

            

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
            $geo_fencing=$this->poly_check_new_estimate((round($request->s_latitude,6)),(round($request->s_longitude,6)));

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
            
            // $price = $service_type->fixed;
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

                $price = (($kilometer * $fixed_price_only->outstation_km) + ($fixed_price_only->outstation_driver * $total_days));

                if($day == 'round')
                {
                    $price = $price * 2;
                    $kilometer = $kilometer * 2;
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
                    'service_type'=> $fixed_price_only->id,
                    'wallet_balance' => Auth::user()->wallet_balance,
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
		if(count($range_values)>0){
               foreach($range_values as $range ){

                   $vertices_x[] = $range['lat'];

                   $vertices_y[] = $range['lng'];

               }}
    
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
                }}


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


  public function select_vehicle(Request $request,$id){

    try{

        $provider_vehicle = FleetVehicle::where('id',$id)->first();
//dd($provider_vehicle);
        ProviderService::create([
                    'provider_id' => \Auth::user()->id,
                    'service_type_id' => $provider_vehicle->service_id,
                    'status' => 'active',
                    'service_number' => $provider_vehicle->vehicle_number,
                    'service_model' => $provider_vehicle->vehicle_model,
                ]);

        Provider::where('id',\Auth::user()->id)->update(['status'=>'approved']);

        $provider_vehicle->status ='1';
        $provider_vehicle->save();

        if($request->ajax()) {
                return response()->json(['message' => 'Vehicle Added Successfully']);
            } else {
                 return redirect('/provider')->with('flash_success','Vehicle Added Successfully');
            }




    }catch(Exception $e){

        if($request->ajax()) {
                return response()->json(['error' => 'Something Went Wrong'],500);
            } else {
                 return back()->with('flash_error', $e->getMessage());
            }


    }

  }
    

}

<?php

namespace App\Http\Controllers\Resource;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Setting;
use Exception;
use App\Helpers\Helper;

use App\Time;
use App\TimePrice;
use App\ServiceType; 
use App\GeoFencing;
use App\ServiceTypeGeoFencings;
use App\ServiceRentalHourPackage;

class ServiceResource extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => [ 'store', 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $services = ServiceType::all();

        $geofencing = GeoFencing::all(); 
        if($request->ajax()) {
            return response()->json(['services' => $services,'geofencing' => $geofencing]);

        } else {
            return view('admin.service.index', compact('services','geofencing'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $geofencing = GeoFencing::all(); 
        $wc =  Time::get();
        return view('admin.service.create', compact('geofencing','wc'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    { 
        //dd($request->all());
        $this->validate($request, [
            'name' => 'required|max:255',
            'provider_name' => 'required|max:255',
            // 'capacity' => 'required|numeric',
            'fixed' => 'required|numeric',
            // 'price' => 'required|numeric',
            // 'minute' => 'required|numeric',
            'outstation_driver' => 'required|numeric',
            'rental_fare' => 'required|numeric',
            'outstation_km' => 'required|numeric',
            'roundtrip_km' => 'required|numeric',
            // 'distance' => 'required|numeric',
            'calculator' => 'required|in:MIN,HOUR,DISTANCE,DISTANCEMIN,DISTANCEHOUR',
            'image' => 'mimes:ico,png'
        ]);

        try {
            $service = $request->all();

            if($request->hasFile('image')) {
                $service['image'] = Helper::upload_picture($request->image);
            }
            $service['price'] = 0;
            $service['minute'] = 0;
            $service['distance'] = 0;
            $service = ServiceType::create($service);
//dd($request->all());
            if($request->peak_price){

                foreach ($request->peak_price as $key => $value) {    
                    $service_map = TimePrice::create(['service_id'=>$service->id,'time_id'=>$key,'peak_price'=>$request->peak_price[$key]]);
                }

            }

            if($service)
            {
                foreach ($request->geo_fencing as $key => $value)
                {
                        $insert = new ServiceTypeGeoFencings();
                        $insert->geo_fencing_id=$key;
                        $insert->service_type_id=$service->id;
                        $insert->distance= $value['distance'];
                        $insert->price=$value['price'];
                        $insert->non_geo_price=0;
                        $insert->minute=$value['minute'];
                        $insert->hour=0;
                        $insert->city_limits=$value['city_limits'];
                        $insert->fixed=$request->fixed;
                        $insert->old_ranges_price=0;
                        $insert->save();

                }
                
            }

            return back()->with('flash_success','Service Type Saved Successfully');
        } catch (Exception $e) {
   
            return back()->with('flash_error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\ServiceType  $serviceType
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            return ServiceType::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Service Type Not Found');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ServiceType  $serviceType
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $service = ServiceType::with('service_geo_fencing','service_geo_fencing.geo_fencing')->findOrFail($id);
            $geofencing = GeoFencing::all(); 
            $ids=[];

            $wc = TimePrice::with('time')->where('service_id',$id)->get();
$wc_new = 0;  
         if(count($wc) > 0){ 
                foreach ($wc as $key => $w) {
                   
                     $ids[] = $w->time[0]->id;
                }

          
            $wc_new =  Time::whereNotIn('id',$ids)->get();
            }

            $packages = ServiceRentalHourPackage::all();

         //   dd($service_fencing);
            return view('admin.service.edit',compact('service','geofencing','service_fencing','wc','wc_new','packages'));
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Service Type Not Found');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ServiceType  $serviceType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
     // dd($request);   
        $this->validate($request, [
            'name' => 'required|max:255',
            'provider_name' => 'required|max:255',
            'fixed' => 'required|numeric',
            // 'price' => 'required|numeric',
            'image' => 'mimes:ico,png'
        ]);

        try {

            $service = ServiceType::with('service_geo_fencing')->findOrFail($id);

            if($request->hasFile('image')) {
                if($service->image) {
                    Helper::delete_picture($service->image);
                }
                $service->image = Helper::upload_picture($request->image);
            }

            $service->name = $request->name;
            $service->provider_name = $request->provider_name;
            $service->fixed = $request->fixed;
            $service->price = 0; 
            $service->minute = 0;
            $service->hour = 0;
            $service->night_fare = $request->night_fare;
            $service->distance = 0; 
            $service->calculator = $request->calculator;
            $service->capacity = $request->capacity;


            $service->rental_fare = $request->rental_fare;
            $service->rental_km_price = $request->rental_km_price;
            $service->rental_hour_price = $request->rental_hour_price;
            $service->outstation_driver = $request->outstation_driver; 
            $service->outstation_km = $request->outstation_km;
            $service->roundtrip_km = $request->roundtrip_km;


            $service->peak_time_8am_11am = 0;
            $service->peak_time_5pm_9pm = 0;
            $service->peak_time_11pm_6am = 0;
            $service->save();

            if($request->peak_update == 1 && $request->peak_price){

                foreach ($request->peak_price as $key => $value) {    
                    $TimePrice = TimePrice::where('service_id',$id)
                                                ->where('time_id',$key) 
                                                ->update(['peak_price'=>$request->peak_price[$key] ]);
                }
                

            }
            if($request->peak_new == 0 && $request->peak_new_price != 0){

                foreach ($request->peak_new_price as $key => $value) {    
                    $service_map = TimePrice::create(['service_id'=>$service->id,'time_id'=>$key,'peak_price'=>$request->peak_new_price[$key]]);
                }

            }

 

                foreach ($request->geo_fencing as $key => $value)
                {

                    if($value['id'])
                    {

                        $update = ServiceTypeGeoFencings::findOrFail($value['id']); 
                        $update->distance=$value['distance'];
                        $update->price=$value['price'];
                        $update->non_geo_price=0;
                        $update->city_limits=$value['city_limits'];
                        $update->minute=$value['minute'];
                        $update->hour=0;
                        $update->old_ranges_price=0;
              
                        $update->save(); 
                    }
                    else
                    {
                    
                        $insert = new ServiceTypeGeoFencings();
                        $insert->geo_fencing_id=$key;
                        $insert->service_type_id=$service->id;
                        $insert->distance= $value['distance'];
                        $insert->non_geo_price=0;
                        $insert->city_limits=$value['city_limits'];
                        $insert->price=$value['price'];
                        $insert->minute=$value['minute'];
                        $insert->hour=0;
                        $insert->old_ranges_price=0;
                        $insert->save();
                    }

                }

            
            return redirect()->route('admin.service.index')->with('flash_success', 'Service Type Updated Successfully');    
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Service Type Not Found');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ServiceType  $serviceType
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
        try {
            ServiceType::find($id)->delete();

            TimePrice::where('service_id',$id)->delete();
            
            return back()->with('message', 'Service Type deleted successfully');
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Service Type Not Found');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Service Type Not Found');
        }
    }
}

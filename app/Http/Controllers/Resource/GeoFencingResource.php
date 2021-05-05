<?php

namespace App\Http\Controllers\Resource;
 
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Setting;
use Exception;
use App\Helpers\Helper;
use App\GeoFencing;
use App\ServiceType;
use App\ServiceTypeGeoFencings;

class GeoFencingResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $geo_fencings = GeoFencing::all();
        return view('admin.geo-fencing.index',compact('geo_fencings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.geo-fencing.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'city_name' => 'required|unique:geo_fencings,deleted_at,NULL,city_name|max:255',
            'ranges' => 'required'
        ]);

        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

        try {

        $insert = new GeoFencing;
        $insert->city_name = $request->city_name;
        $insert->ranges = $request->ranges;
        $insert->save();


        $services  = ServiceType::get();
        if(count($services)!=0)
        {

            foreach($services as $list_services)
            {

                $fencings['geo_fencing_id'] = $insert->id;
                $fencings['service_type_id'] = $list_services->id;
                $fencings['distance'] = $list_services->distance;
                $fencings['hour'] = $list_services->hour;
                $fencings['minute'] = $list_services->minute;
                $fencings['price'] = $list_services->price;
                $fencings['fixed'] = $list_services->fixed ? $list_services->fixed : 0;
                $fencings['old_ranges_price'] = 0.00;
                ServiceTypeGeoFencings::create($fencings);
            }
        }
        
        return redirect('admin/geo-fencing')->with('flash_success','Added successfully');

         } catch (Exception $e) {
   
            return back()->with('flash_error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $show = GeoFencing::findOrFail($id);
        return view('admin.geo-fencing.edit',compact('show'));
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
            'city_name' => 'required|max:255',
            'ranges' => 'required'
        ]);

        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

        try {
            
        $update = GeoFencing::findOrFail($id);
        $update->city_name = $request->city_name;
        $update->ranges = $request->ranges;
        $update->save();
        return redirect('admin/geo-fencing')->with('flash_success','Updated successfully');

         } catch (Exception $e) {
   
            return back()->with('flash_error', $e->getMessage());
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
        $destroy = GeoFencing::findOrFail($id);
        $destroy->delete();
        return back()->with('flash_success','Deleted successfully');
    }
}

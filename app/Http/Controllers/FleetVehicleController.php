<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\FleetVehicle;
use App\ServiceType;

class FleetVehicleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $fleet_vehicle = FleetVehicle::with('service')->where('fleet_id',\Auth::user()->id)->get();

        if($request->ajax()) {
            return response()->json(['fleet_vehicle' => $fleet_vehicle]);
        } else {
            return view('fleet.vehicle.index', compact('fleet_vehicle'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {   
        $service = ServiceType::get();
        return view('fleet.vehicle.create', compact('service'));
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
           'service_type' => 'required|numeric|exists:service_types,id',           
        ]);

        try{

            $fleet_data = $request->all();
            $fleet_data['fleet_id'] = \Auth::user()->id;
            $fleet_data['service_id'] = $request->service_type;
            $fleet_vehicle = FleetVehicle::create($fleet_data);

            if($request->ajax()) {
                return response()->json(['message' => 'Vehicle Saved Successfully']);
            } else {
                return back()->with('flash_success','Vehicle Type Saved Successfully');
            }

        }catch(Exception $e){

              if($request->ajax()) {
                return response()->json(['error' => 'Something Went Wrong'],500);
            } else {
                 return back()->with('flash_error', $e->getMessage());
            }

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
        try {

            $vehicle = FleetVehicle::findOrFail($id);

            $service = ServiceType::get();

             
                return view('fleet.vehicle.edit',compact('vehicle','service'));
           
                                            
            

        } catch (ModelNotFoundException $e) {

          
            return back()->with('flash_error', $e->getMessage());
         

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
    {  //dd($request);
       try{
            $fleet_vehicle = FleetVehicle::findOrFail($id);

            if($request->vehicle_model)
                $fleet_vehicle->vehicle_model = $request->vehicle_model;

            if($request->vehicle_number)
                $fleet_vehicle->vehicle_number = $request->vehicle_number;

            if($request->service_type)
                $fleet_vehicle->service_id = $request->service_type;


            $fleet_vehicle->save();

            if($request->ajax()) {
                return response()->json(['message' => 'Vehicle updated Successfully']);
            } else {
                return back()->with('flash_success','Vehicle Type updated Successfully');
            }

        }catch(Exception $e){

            if($request->ajax()) {
                return response()->json(['error' => 'Something Went Wrong'],500);
            } else {
                 return back()->with('flash_error', $e->getMessage());
            }

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
        try{

            FleetVehicle::find($id)->delete();

         
            return back()->with('flash_success','Vehicle Type Deleted Successfully');
            

        }catch(Exception $e){

           
                 return back()->with('flash_error', $e->getMessage());
          

        }
    }


    public function destroy_api(Request $request)
    {
        try{

            FleetVehicle::find($id)->delete();

            if($request->ajax()) {
                return response()->json(['message' => 'Vehicle Deleted Successfully']);
            } else {
                return back()->with('flash_success','Vehicle Type Deleted Successfully');
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

<?php

namespace App\Http\Controllers\Resource;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\ServiceRentalHourPackage;


class ServiceRentalHourPackageController extends Controller
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
        $package = ServiceRentalHourPackage::whereservice_type_id($request->service_type_id)->get();
        if($request->ajax()) {
            return $package;
        } else {
            return view('admin.servicerentalhourpackage.index',compact('package'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.servicerentalhourpackage.create');
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
        'service_type_id' => 'required|numeric',
        'hour' => 'required|numeric',
        'km' => 'required|numeric',
        'price' => 'required|numeric',
    
        ]); 

        try{
            
            $package = ServiceRentalHourPackage::create([
                        'service_type_id'=>$request->service_type_id,
                        'price'=>$request->price,
                        'km'=>$request->km,
                        'hour'=>$request->hour,
                    ]);
        

            return back()->with('flash_success','PackageType Saved Successfully');

        } catch (Exception $e) {
            return back()->with('flash_error', 'PackageType Not Found');
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
        try {
            return ServiceRentalHourPackage::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'PackageType Not Found');
        }
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

            $package = ServiceRentalHourPackage::findOrFail($id);
                                            
            return view('admin.servicerentalhourpackage.edit',compact('package'));

        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'PackageType Not Found');
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
        'hour' => 'required|numeric',
        'km' => 'required|numeric',
        'price' => 'required|numeric',
    
        ]); 

        try {

            $package = ServiceRentalHourPackage::findOrFail($id);
            
            $package->hour = $request->hour;
            $package->km = $request->km; 
            $package->price = $request->price; 

            $package->save();
        
        
            return redirect('admin/servicerentalhourpackage?service_type_id='.$package->service_type_id)->with('flash_success', 'Package Updated Successfully');    
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_errors', 'Package Not Found');
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
        try {

            ServiceRentalHourPackage::find($id)->delete();
            return back()->with('flash_success', 'Package deleted successfully');
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Package Not Found');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Package Not Found');
        }
    }
}

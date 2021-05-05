<?php

namespace App\Http\Controllers\Resource;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Setting;
use Exception;
use App\Helpers\Helper;

use App\PackageType;

class PackageTypeResource extends Controller
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
        $package = PackageType::all();
        if($request->ajax()) {
            return $package;
        } else {
            return view('admin.packagetype.index', compact('package'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
        return view('admin.packagetype.create');
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
            'package_hour' => 'required|numeric',
            'package_km' => 'required|numeric',
        
            ]);

            try{
            
            $package = PackageType::create([
                        'package_hour'=>$request->package_hour,
                        'package_km'=>$request->package_km,
                    ]);
        

            return back()->with('flash_success','PackageType Saved Successfully');

        } catch (Exception $e) {
            return back()->with('flash_error', 'PackageType Not Found');
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
            return PackageType::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'PackageType Not Found');
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

            $package = PackageType::findOrFail($id);
                                            
            return view('admin.packagetype.edit',compact('package'));

        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'PackageType Not Found');
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
        
        $this->validate($request, [
            'package_hour' => 'required|numeric',
            'package_km' => 'required|numeric',
            ]);
   

        try {

            $package = PackageType::findOrFail($id);

            $package->package_hour = $request->package_hour;

            $package->package_km = $request->package_km;          

            $package->save();
        
        
            return redirect()->route('admin.packagetype.index')->with('flash_success', 'PackageType Updated Successfully');    
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_errors', 'PackageType Not Found');
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

            PackageType::find($id)->delete();
            return back()->with('message', 'Package Type deleted successfully');
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Package Type Not Found');
        } catch (Exception $e) {
            return back()->with('flash_error', 'Package Type Not Found');
        }
    }
}
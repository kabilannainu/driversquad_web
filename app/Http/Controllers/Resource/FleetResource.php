<?php

namespace App\Http\Controllers\Resource;

use App\Fleet;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Exception;
use Setting;
use Auth;

class FleetResource extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => [ 'update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $fleets = Fleet::orderBy('created_at' , 'desc')->get();

            if(Auth::guard('admin')->user()){
                return view('admin.fleet.index', compact('fleets'));
            }elseif(Auth::guard('dispatcher')->user()){
                return view('dispatcher.fleet.index', compact('fleets'));
            }   
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
            if(Auth::guard('admin')->user()){
                return view('admin.fleet.create');
            }elseif(Auth::guard('dispatcher')->user()){
                return view('dispatcher.fleet.create');
            } 
        
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
            'name' => 'required|max:255',
            'company' => 'required|max:255',
            'email' => 'required|unique:fleets,email|email|max:255',
            'mobile' => 'digits_between:6,13',
            'logo' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'password' => 'required|min:6|confirmed',
        ]);

        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

        try{

            $fleet = $request->all();
            $fleet['password'] = bcrypt($request->password);
            if($request->hasFile('logo')) {
                $fleet['logo'] = $request->logo->store('fleet');
            }

            $fleet = Fleet::create($fleet);

            return back()->with('flash_success','Fleet Details Saved Successfully');

        } 

        catch (Exception $e) {
            return back()->with('flash_error', 'Fleet Not Found');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Fleet  $fleet
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Fleet  $fleet
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $fleet = Fleet::findOrFail($id);

            if(Auth::guard('admin')->user()){
                return view('admin.fleet.edit',compact('fleet'));
            }elseif(Auth::guard('dispatcher')->user()){
                return view('dispatcher.fleet.edit',compact('fleet'));
            } 
            
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Fleet  $fleet
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
        $this->validate($request, [
            'name' => 'required|max:255',
            'company' => 'required|max:255',
            'mobile' => 'digits_between:6,13',
            'logo' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

        try {

            $fleet = Fleet::findOrFail($id);

            if($request->hasFile('logo')) {
                \Storage::delete($fleet->logo);
                $fleet->logo = $request->logo->store('fleet');
            }

            $fleet->name = $request->name;
            $fleet->company = $request->company;
            $fleet->mobile = $request->mobile;
            $fleet->save();

            if(Auth::guard('admin')->user()){
                return redirect()->route('admin.fleet.index')->with('flash_success', 'Fleet Updated Successfully'); 
            }elseif(Auth::guard('dispatcher')->user()){
                return redirect()->route('dispatcher.fleet.index')->with('flash_success', 'Fleet Updated Successfully'); 
            } 
            
               
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Fleet Not Found');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Fleet  $Fleet
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
        try {
            if(Setting::get('demo_mode', 0) == 1) {
                return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
            }
            
            Fleet::find($id)->delete();
            return back()->with('message', 'Fleet deleted successfully');
        } 
        catch (Exception $e) {
            return back()->with('flash_error', 'Fleet Not Found');
        }
    }

}

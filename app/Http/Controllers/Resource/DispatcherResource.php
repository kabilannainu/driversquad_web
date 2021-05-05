<?php

namespace App\Http\Controllers\Resource;

use App\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Exception;
use Setting;
use Auth;

class DispatcherResource extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => ['update', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dispatchers = Dispatcher::orderBy('created_at' , 'desc')->get();

        if(Auth::guard('admin')->user()){
            return view('admin.dispatcher.index', compact('dispatchers')); 
        }elseif(Auth::guard('dispatcher')->user()){
            return view('dispatcher.dispatcher.index', compact('dispatchers')); 
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
            return view('admin.dispatcher.create');
        }elseif(Auth::guard('dispatcher')->user()){
            return view('dispatcher.dispatcher.create'); 
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
            'mobile' => 'digits_between:6,13',
            'email' => 'required|unique:dispatchers,email|email|max:255',
            'password' => 'required|min:6|confirmed',
        ]);

        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

        try{

            $Dispatcher = $request->all();
            $Dispatcher['password'] = bcrypt($request->password);

            $Dispatcher = Dispatcher::create($Dispatcher);

            return back()->with('flash_success','Dispatcher Details Saved Successfully');

        } 

        catch (Exception $e) {
            return back()->with('flash_error', 'Dispatcher Not Found');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Dispatcher  $dispatcher
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Dispatcher  $dispatcher
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $dispatcher = Dispatcher::findOrFail($id);

            if(Auth::guard('admin')->user()){
                return view('admin.dispatcher.edit',compact('dispatcher'));
            }elseif(Auth::guard('dispatcher')->user()){
                return view('dispatcher.dispatcher.edit',compact('dispatcher')); 
            } 
            
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Dispatcher  $dispatcher
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'name' => 'required|max:255',
            'mobile' => 'digits_between:6,13',
        ]);

        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

        try {

            $dispatcher = Dispatcher::findOrFail($id);
            $dispatcher->name = $request->name;
            $dispatcher->mobile = $request->mobile;
            $dispatcher->save();

            if(Auth::guard('admin')->user()){
                return view('admin.dispatcher.edit',compact('dispatcher'));
            }elseif(Auth::guard('dispatcher')->user()){
                return view('dispatcher.dispatcher.edit',compact('dispatcher')); 
            } 
                
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Dispatcher Not Found');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Dispatcher  $dispatcher
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        try {
            if(Setting::get('demo_mode', 0) == 1) {
                return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
            }
            
            Dispatcher::find($id)->delete();
            return back()->with('message', 'Dispatcher deleted successfully');
        } 
        catch (Exception $e) {
            return back()->with('flash_error', 'Dispatcher Not Found');
        }
    }

}

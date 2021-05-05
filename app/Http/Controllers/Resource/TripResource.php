<?php

namespace App\Http\Controllers\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\UserRequests;
use App\UserRequestRating;
use Auth;
use Setting;

class TripResource extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {

            $requests = UserRequests::RequestHistory()->paginate(10);

            $page=10;
            if($request->has('page'))
            {
                $page =$request->page.'0';
            }

            if(Auth::guard('admin')->user()){
               return view('admin.request.index', compact('requests','page'));
            }elseif(Auth::guard('dispatcher')->user()){
                return view('dispatcher.request.index', compact('requests'));
            }

            
        } catch (Exception $e) {
            return back()->with('flash_error','Something Went Wrong!');
        }
    }

    public function Fleetindex()
    {
        try {
            $requests = UserRequests::RequestHistory()
                        ->whereHas('provider', function($query) {
                            $query->where('fleet', Auth::user()->id );
                        })->get();
 
            if(Auth::guard('admin')->user()){
                return view('fleet.request.index', compact('requests'));
            }elseif(Auth::guard('dispatcher')->user()){
                return view('fleet.request.index', compact('requests'));
            }
            elseif(Auth::guard('fleet')->user()){
                return view('fleet.request.index', compact('requests'));
            }

            
        } catch (Exception $e) {
            dd($e);
            return back()->with('flash_error','Something Went Wrong!');
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function scheduled()
    {
        try{
            $requests = UserRequests::where('status' , 'SCHEDULED')
                        ->RequestHistory()
                        ->get();

            if(Auth::guard('admin')->user()){
                return view('admin.request.scheduled', compact('requests'));
            }elseif(Auth::guard('dispatcher')->user()){
                return view('dispatcher.request.scheduled', compact('requests'));
            }

            
        } catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function Fleetscheduled()
    {
        try{
            $requests = UserRequests::where('status' , 'SCHEDULED')
                         ->whereHas('provider', function($query) {
                            $query->where('fleet', Auth::user()->id );
                        })
                        ->get();

            if(Auth::guard('admin')->user()){
                return view('fleet.request.scheduled', compact('requests'));
            }elseif(Auth::guard('dispatcher')->user()){
                return view('fleet.request.scheduled', compact('requests'));
            }elseif(Auth::guard('fleet')->user()){
                return view('fleet.request.scheduled', compact('requests'));
            }

            
        } catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 
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
            $request = UserRequests::findOrFail($id);

            if(Auth::guard('admin')->user()){
                return view('admin.request.show', compact('request'));
            }elseif(Auth::guard('dispatcher')->user()){
                return view('dispatcher.request.show', compact('request'));
            }
            
        } catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    }

    public function Fleetshow($id)
    {
        try {
            $request = UserRequests::findOrFail($id);

            if(Auth::guard('admin')->user()){
                return view('fleet.request.show', compact('request'));
            }elseif(Auth::guard('dispatcher')->user()){
                return view('fleet.request.show', compact('request'));
            }
            
        } catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }
    }

    public function Accountshow($id)
    {
        try {
            $request = UserRequests::findOrFail($id);

            if(Auth::guard('admin')->user()){
                return view('account.request.show', compact('request'));
            }elseif(Auth::guard('dispatcher')->user()){
                return view('account.request.show', compact('request'));
            }
            
        } catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
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
        //
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
        //
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
            $Request = UserRequests::findOrFail($id);
            $Request->delete();
            return back()->with('flash_success','Request Deleted!');
        } catch (Exception $e) {
            return back()->with('flash_error','Something Went Wrong!');
        }
    }

    public function Fleetdestroy($id)
    {
        try {
            $Request = UserRequests::findOrFail($id);
            $Request->delete();
            return back()->with('flash_success','Request Deleted!');
        } catch (Exception $e) {
            return back()->with('flash_error','Something Went Wrong!');
        }
    }
     /**
     * Seleted provider delete
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function request_seleted_cancelled_delete(Request $request) 
    {
            // print_r($request->deleted_id);

            foreach ($request->deleted_id as $key => $value) {
               if($value)
               {
                   $delete = UserRequests::findOrFail($value);
                   if($delete->status == 'CANCELLED'){
                        $delete->delete();
                        $rating = UserRequestRating::whererequest_id($value)->first();  
                        if($rating)
                            $delete->delete();
                   }
                }
            }

           return response()->json(['success' => 'success']);

    }
}

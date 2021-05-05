<?php

namespace App\Http\Controllers\Resource;

use App\User;
use App\UserRequests;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Exception;
use Storage;
use Setting;
use Auth;
use Maatwebsite\Excel\Facades\Excel;

class UserResource extends Controller
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
        if($request->all()) 
        {
            $users = User::orderBy('created_at' , 'desc')->orWhere('first_name', 'like', '%' . $request->search . '%')->orWhere('last_name', 'like', '%' . $request->search . '%')->orWhere('email', 'like', '%' . $request->search . '%')->orWhere('mobile', 'like', '%' . $request->search . '%')->paginate(10); 
            $search = $request->search;
        } 
        else
        {
            $users = User::orderBy('created_at' , 'desc')->paginate(10);
            $search = ''; 
        } 
        $page=10;
        if($request->has('page'))
        {
            $page =$request->page.'0';
        }

        if(Auth::guard('admin')->user()){
            $search = $request->search;
           return view('admin.users.index', compact('users','search','page'));
        }elseif(Auth::guard('dispatcher')->user()){
            return view('dispatcher.users.index', compact('users','search','page'));
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
            return view('admin.users.create');
        }elseif(Auth::guard('dispatcher')->user()){
            return view('dispatcher.users.create');
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
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            // 'email' => 'required|unique:users,email|email|max:255',
            'mobile' => 'required|unique:users,mobile',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            // 'password' => 'required|min:6|confirmed',
        ]);

        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

        try{

            $user = $request->all();

            $user['payment_mode'] = 'CASH';
            $user['password'] = bcrypt('123456');
            if($request->hasFile('picture')) {
                $user['picture'] = $request->picture->store('user/profile');
            }

            $user = User::create($user);

            return back()->with('flash_success','User Details Saved Successfully');

        } 

        catch (Exception $e) {
            return back()->with('flash_error', 'User Not Found');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);

            if(Auth::guard('admin')->user()){
                return view('admin.users.user-details', compact('user'));
            }elseif(Auth::guard('dispatcher')->user()){
                return view('dispatcher.users.user-details', compact('user'));
            }
            
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);

            if(Auth::guard('admin')->user()){
                return view('admin.users.edit',compact('user'));
            }elseif(Auth::guard('dispatcher')->user()){
                return view('dispatcher.users.edit',compact('user'));
            }
            
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'mobile' => 'digits_between:6,13',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

        try {

            $user = User::findOrFail($id);

            if($request->hasFile('picture')) {
                Storage::delete($user->picture);
                $user->picture = $request->picture->store('user/profile');
            }

            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->mobile = $request->mobile;
            $user->save();
            
            if(Auth::guard('admin')->user()){
                return redirect()->route('admin.user.index')->with('flash_success', 'User Updated Successfully');
            }elseif(Auth::guard('dispatcher')->user()){
                return redirect()->route('dispatcher.user.index')->with('flash_success', 'User Updated Successfully');
            }
                
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'User Not Found');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
        try {

            if(Setting::get('demo_mode', 0) == 1) {
                return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
            }

            User::find($id)->delete();
            return back()->with('message', 'User deleted successfully');
        } 
        catch (Exception $e) {
            return back()->with('flash_error', 'User Not Found');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function request($id){

        try{

            $requests = UserRequests::where('user_requests.user_id',$id)
                    ->RequestHistory()
                    ->get();

            if(Auth::guard('admin')->user()){
                return view('admin.request.index', compact('requests'));
            }elseif(Auth::guard('dispatcher')->user()){
                return view('dispatcher.request.index', compact('requests'));
            }
            
        }

        catch (Exception $e) {
             return back()->with('flash_error','Something Went Wrong!');
        }

    }
    /**
     * Seleted user delete
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function user_seleted_delete(Request $request)
    {
            // print_r($request->deleted_id);

        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

            foreach ($request->deleted_id as $key => $value) {
               if($value)
               {
                   $delete = User::findOrFail($value);
                   $delete->delete();
                }
            }

           return response()->json(['success' => 'success']);

    }
    /**
     * All user export excel
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function user_export_excel()
    { 
        $data = User::get()->makehidden(['id','created_at','updated_at'])->toArray();
        
        return Excel::create('users', function($excel) use ($data) {

            $excel->sheet('mySheet', function($sheet) use ($data)
            {
                $sheet->fromArray($data);
            });

        })->download('csv');

    }

}

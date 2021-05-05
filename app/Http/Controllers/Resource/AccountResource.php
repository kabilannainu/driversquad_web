<?php

namespace App\Http\Controllers\Resource;

use App\Account;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use Exception;
use Setting;
use Auth;

class AccountResource extends Controller
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
        $accounts = Account::orderBy('created_at' , 'desc')->get();

            if(Auth::guard('admin')->user()){
                return view('admin.account-manager.index', compact('accounts'));
            }elseif(Auth::guard('dispatcher')->user()){
                return view('dispatcher.account-manager.index', compact('accounts'));
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
            return view('admin.account-manager.create');
        }elseif(Auth::guard('dispatcher')->user()){
            return view('dispatcher.account-manager.create');
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
            'email' => 'required|unique:accounts,email|email|max:255',
            'password' => 'required|min:6|confirmed',
        ]);

        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

        try{

            $Account = $request->all();
            $Account['password'] = bcrypt($request->password);

            $Account = Account::create($Account);

            return back()->with('flash_success','Account Manager Details Saved Successfully');

        } 

        catch (Exception $e) {
            return back()->with('flash_error', 'Account Manager Not Found');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Dispatcher  $account
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\account  $account
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $account = Account::findOrFail($id);

            if(Auth::guard('admin')->user()){
                return view('admin.account-manager.edit',compact('account'));
            }elseif(Auth::guard('dispatcher')->user()){
                return view('dispatcher.account-manager.edit',compact('account'));
            } 
            
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\account  $account
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

            $Account = Account::findOrFail($id);
            $Account->name = $request->name;
            $Account->mobile = $request->mobile;
            $Account->save();

            if(Auth::guard('admin')->user()){
                return redirect()->route('admin.account-manager.index')->with('flash_success', 'Account Manager Updated Successfully');
            }elseif(Auth::guard('dispatcher')->user()){
                return redirect()->route('dispatcher.account-manager.index')->with('flash_success', 'Account Manager Updated Successfully');
            } 
                
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Account Manager Not Found');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Account  $dispatcher
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        try {
            if(Setting::get('demo_mode', 0) == 1) {
                return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
            }
            
            Account::find($id)->delete();
            return back()->with('message', 'Account Manager deleted successfully');
        } 
        catch (Exception $e) {
            return back()->with('flash_error', 'Account Not Found');
        }
    }

}

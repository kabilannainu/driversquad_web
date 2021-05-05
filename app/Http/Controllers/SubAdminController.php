<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Admin;
use App\Role;
use Storage;
use Setting;

class SubAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $sub_admin = Admin::whereNotIn('role_id',[0])->with('roles')->orderBy('created_at' , 'desc')->get();
        return view('admin.sub_admin.index', compact('sub_admin'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         $role = Role::get();
         return view('admin.sub_admin.create',compact('role'));
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
            'email' => 'required|unique:admins,email|email|max:255',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'password' => 'required|min:6|confirmed',
            'role_id' => 'required'
        ]);

        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

        try{

            $admin = $request->all();

            $admin['password'] = bcrypt($request->password);
            if($request->hasFile('picture')) {
                $admin['picture'] = $request->picture->store('admin/profile');
            }

            $admin = Admin::create($admin);

            return back()->with('flash_success','Admin Saved Successfully');

        } 

        catch (Exception $e) {
            return back()->with('flash_error', 'Admin Not Found');
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
            $user = Admin::findOrFail($id);
            $role = Role::get();
            return view('admin.sub_admin.edit',compact('user','role'));
        } catch (ModelNotFoundException $e) {
            return $e;
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
            'name' => 'required|max:255',
            'picture' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            'role_id' => 'required'
    
        ]);

         if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }
        try {

            $user = Admin::findOrFail($id);

            if($request->hasFile('picture')) {
                Storage::delete($user->picture);
                $user->picture = $request->picture->store('admin/profile');
            }

            $user->name = $request->name;
            $user->role_id = $request->role_id;
            $user->save();

            return redirect()->route('admin.subadmin.index')->with('flash_success', 'Admin Updated Successfully');    
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Admin Not Found');
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

            if(Setting::get('demo_mode', 0) == 1) {
                return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
            }

            Admin::find($id)->delete();
            return back()->with('message', 'Admin deleted successfully');
        } 
        catch (Exception $e) {
            return back()->with('flash_error', 'Admin Not Found');
        }
    }
}

<?php

namespace App\Http\Controllers\Resource;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Role;
use Exception;
use Auth;
use Route;
use Setting;
use App\RolePermission;

class RolesResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try{

            $roles = Role::orderBy('created_at','desc')->get();
            return view('admin.roles.index', compact('roles'));

        } catch(Exception $e){
             return back()->with('flash_error',  $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.roles.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request,[
                'role_name' => 'required|unique:roles,role_name',
                'description' => 'required',

            ]);

        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

        try{
                    $data =$request->all();
                    Role::create($data);
                    return redirect('admin/roles')->with('flash_message',"Role Created Successfully");

        } catch(Exception $e){

                return back()->with('flash_error',$e->getMessage());
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
            $role = Role::findOrFail($id);
            return view('admin.roles.edit',compact('role'));
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
            'description' => 'required',
         ]);

        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

        try {
            $role = Role::findOrFail($id);
            $role->description = $request->description;
            $role->save();

            return redirect()->route('admin.roles.index')->with('flash_success', 'Role Updated Successfully');    
        } 
        catch (Exception $e) {
            return back()->with('flash_error', 'Role Not Found');
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
            if(Setting::get('demo_mode', 0) == 1) {
                return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
            }

            Role::find($id)->delete();
            return back()->with('flash_success','Role Deleted Successfully');

        } catch(Exception $e){
                return back()->with('flash_error',$e->getMessage());
        }
    }

    public function permission($id)
    {
        $name = Route::getCurrentRoute()->uri();

        $data = Route::getRoutes();
      
        $role = Role::find($id);


        return view('admin.roles.permission', compact('roles','id' ));

   }

   public function store_permission(Request $request)
   {
        try{

            if(Setting::get('demo_mode', 0) == 1) {
                return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
            }

            RolePermission::where('role_id',$request->role_id)->delete();

            if($request->roles){

                foreach($request->roles as $key => $data)
                {
                        $create['route'] = $data;
                        $create['name'] = $key;
                        $create['role_id'] =$request->role_id;
                        RolePermission::create($create);
                }

            }

            return redirect('admin/roles')->with('flash_message',"Permissison Updated Successfully");

        }
        catch(Exception $e)
        {
             return back()->with('flash_error',$e->getMessage());
        }
   }

  

}

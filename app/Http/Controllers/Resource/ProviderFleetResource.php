<?php

namespace App\Http\Controllers\Resource;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use DB;
use Exception;
use Auth;

use App\Provider;
use App\UserRequests;
use App\Helpers\Helper;

use App\Document;
use App\ProviderService;
use App\ProviderDocument; 
use Storage;

class ProviderFleetResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $providers = Provider::with('service','accepted','cancelled')
                    ->where('fleet', Auth::user()->id )
                    ->orderBy('id', 'DESC')
                    ->get();

        if($request->ajax()){
            return $providers;

        }else{
            return view('fleet.providers.index', compact('providers'));
        }

        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $DriverDocuments = Document::get();
        return view('fleet.providers.create',compact('DriverDocuments'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    { //dd($request->all());
        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            // 'email' => 'required|unique:providers,email|email|max:255',
            'mobile' => 'digits_between:6,13',
            'avatar' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            // 'password' => 'required|min:6|confirmed',
        ]);

        try{

            $providerdata = $request->all(); 

            $providerdata['password'] = bcrypt('123456'); 
            $providerdata['fleet'] = Auth::user()->id;
            if($request->hasFile('avatar')) {
                $providerdata['avatar'] = $request->avatar->store('provider/profile');
            }

            $provider = Provider::create($providerdata);

            if((array_key_exists('document',$providerdata))){
                for($i=0; $i<sizeof($providerdata['document']);$i++)
                {   
               

                    ProviderDocument::create([
                            'url' => Storage::putFile('user/profile', $providerdata['document'][$i], 'public'),
                            'provider_id' => $provider->id,
                            'document_id'=>$providerdata['id'][$i],
                            'status' => 'ASSESSING',
                            //'expires_at'=> Carbon::parse($data['expires_at'][$i])->format('Y/m/d'),
                        ]);
                    
                }
            }



             if($request->ajax()){
                return response()->json(['message'=>'Provider Details Saved Successfully']);

            }else{
                return back()->with('flash_success','Provider Details Saved Successfully');
            }
            

        } 

        catch (Exception $e) {

          //  dd($e->getMessage());
            if($request->ajax()){
                return response()->json(['error'=>'Something Went Wrong'],500);


            }else{
                return back()->with('flash_error', 'Provider Not Found');
            }
            
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $provider = Provider::findOrFail($id);
            return view('fleet.providers.provider-details', compact('provider'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $provider = Provider::findOrFail($id);
            return view('fleet.providers.edit',compact('provider'));
        } catch (ModelNotFoundException $e) {
            return $e;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    { 
        $this->validate($request, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'mobile' => 'digits_between:6,13',
            'avatar' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        try {

            $provider = Provider::findOrFail($id);

            if($request->hasFile('avatar')) {
                if($provider->avatar) {
                    Storage::delete($provider->avatar);
                }
                $provider->avatar = $request->avatar->store('provider/profile');                    
            }

            $provider->first_name = $request->first_name;
            $provider->last_name = $request->last_name;
            $provider->mobile = $request->mobile;
            $provider->save();

              if($request->ajax()){
                return response()->json(['message'=>'Provider Updated Successfully']);

            }else{
                 return redirect()->route('fleet.provider.index')->with('flash_success', 'Provider Updated Successfully');
            }

               
        } 

        catch (ModelNotFoundException $e) {
             if($request->ajax()){
                return response()->json(['error'=>'Something Went Wrong'],500);
              }else{
                return back()->with('flash_error', 'Provider Not Found');
             }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function destroy($id,Request $request)
    {  
        try {
            Provider::find($id)->delete();
            
            if($request->ajax()){
                return response()->json(['message'=>'Provider deleted successfully']);
              }else{
                return back()->with('message', 'Provider deleted successfully');
             }
        } 
        catch (Exception $e) {
            if($request->ajax()){
                return response()->json(['error'=>'Something Went Wrong'],500);
              }else{
                return back()->with('flash_error', 'Provider Not Found');
             }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function approve($id)
    { 
        try {
            $Provider = Provider::findOrFail($id);
            if($Provider->service) {
                $Provider->update(['status' => 'approved']);
                return back()->with('flash_success', "Provider Approved");
            } else {
                return redirect()->route('fleet.provider.document.index', $id)->with('flash_error', "Provider has not been assigned a service type!");
            }
        } catch (ModelNotFoundException $e) {
            return back()->with('flash_error', "Something went wrong! Please try again later.");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function disapprove($id)
    {
        Provider::where('id',$id)->update(['status' => 'banned']);
        return back()->with('flash_success', "Provider Disapproved");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function request($id,Request $request){

        try{

            $requests = UserRequests::where('user_requests.provider_id',$id)
                    ->RequestHistory()
                    ->get();

            if($request->ajax()){
                return $requests;

            }else{
                return view('fleet.request.index', compact('requests'));
            }

            return view('fleet.request.index', compact('requests'));
        } catch (Exception $e) {
            return back()->with('flash_error','Something Went Wrong!');

            if($request->ajax()){
                return response()->json(['error' => $e->getMessage()], 500);
            }else{
                return back()->with('flash_error','Something Went Wrong!');
            }
        }
    }
}

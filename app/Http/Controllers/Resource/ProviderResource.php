<?php

namespace App\Http\Controllers\Resource;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Auth;
use DB;
use Exception;
use Setting;
use Storage;
use App\Http\Controllers\SendPushNotification;

use App\Provider;
use App\UserRequestPayment;
use App\UserRequests;
use App\Helpers\Helper;


use App\Document;
use App\ProviderService;
use App\ProviderDocument; 

class ProviderResource extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('demo', ['only' => [ 'store', 'update', 'destroy', 'disapprove']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        
        $AllProviders = Provider::with('service.service_type','accepted','cancelled','fleets')
                    ->orderBy('id', 'DESC');

        if(request()->has('fleet')){
            $providers = $AllProviders->where('fleet',$request->fleet);
            $fleet = 'fleet';
        }
else{ $fleet = ''; }
            $search = '';
            if($request->all()) 
            {
                $search = $request->search;
                $AllProviders->orWhere('first_name', 'like', '%' . $request->search . '%')->orWhere('last_name', 'like', '%' . $request->search . '%')->orWhere('email', 'like', '%' . $request->search . '%')->orWhere('mobile', 'like', '%' . $request->search . '%')->orwhereHas('service.service_type', function($q) use($search)
                        {
                            $q->where('name', 'like', '%' . $search . '%');

                        })->orwhereHas('service', function($q) use($search)
                        {
                            $q->where('service_number', 'like', '%' . $search . '%');

                        })->orwhereHas('service', function($q) use($search)
                        {
                            $q->where('status', 'like', '%' . $search . '%');

                        });  
            }    

            $providers = $AllProviders->paginate(10);

    //        $fleet = '';
  //      } 
        $page=10;
        if($request->has('page'))
        {
            $page =$request->page.'0';
        }
        
        if(Auth::guard('admin')->user()){
           return view('admin.providers.index', compact('providers','fleet','search','page'));
        }elseif(Auth::guard('dispatcher')->user()){
            return view('dispatcher.providers.index', compact('providers'));
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
        if(Auth::guard('admin')->user()){
            return view('admin.providers.create',compact('DriverDocuments')); 
        }elseif(Auth::guard('dispatcher')->user()){
            return view('dispatcher.providers.create',compact('DriverDocuments')); 
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
            // 'email' => 'required|unique:providers,email|email|max:255',
            'mobile' => 'required|unique:providers,mobile',
            'avatar' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
            // 'password' => 'required|min:6|confirmed',
        ]);

        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

        try{


            $providerdata = $request->all(); 

            $providerdata['password'] = bcrypt('123456');
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

            return back()->with('flash_success','Provider Details Saved Successfully');

        } 

        catch (Exception $e) {
            return back()->with('flash_error', 'Provider Not Found');
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

            if(Auth::guard('admin')->user()){
               return view('admin.providers.provider-details', compact('provider'));
            }elseif(Auth::guard('dispatcher')->user()){
                return view('dispatcher.providers.provider-details', compact('provider'));
            }

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

             if(Auth::guard('admin')->user()){
               return view('admin.providers.edit',compact('provider'));
            }elseif(Auth::guard('dispatcher')->user()){
                return view('dispatcher.providers.edit',compact('provider'));
            }
            
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

        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

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

            if(Auth::guard('admin')->user()){
               return redirect()->route('admin.provider.index')->with('flash_success', 'Provider Updated Successfully');
            }elseif(Auth::guard('dispatcher')->user()){
                return redirect()->route('dispatcher.provider.index')->with('flash_success', 'Provider Updated Successfully');
            }
                
        } 

        catch (ModelNotFoundException $e) {
            return back()->with('flash_error', 'Provider Not Found');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        try {
            if(Setting::get('demo_mode', 0) == 1) {
                return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
            }

            Provider::find($id)->delete();
            return back()->with('message', 'Provider deleted successfully');
        } 
        catch (Exception $e) {
            return back()->with('flash_error', 'Provider Not Found');
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
            if(Setting::get('demo_mode', 0) == 1) {
                return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
            }

            $Provider = Provider::with('service.service_type')->findOrFail($id);
            if($Provider->service) {
                $Provider->update(['status' => 'approved']);

                ProviderService::whereIn('provider_id',[$id])->update(['status' => 'active']);
                /// Admin approved provider
                (new SendPushNotification)->AdminApproveProvider($Provider); 

                return back()->with('flash_success', "Provider Approved");
            } else {

                if(Auth::guard('admin')->user()){
                  return redirect()->route('admin.provider.document.index', $id)->with('flash_error', "Provider has not been assigned a service type!");
                }elseif(Auth::guard('dispatcher')->user()){
                   return redirect()->route('dispatcher.provider.document.index', $id)->with('flash_error', "Provider has not been assigned a service type!");
                }
                
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
        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

        $Provider = Provider::with('service.service_type')->findOrFail($id);
        (new SendPushNotification)->AdminDisapproveProvider($Provider);
        Provider::where('id',$id)->update(['status' => 'banned']);
        ProviderService::whereIn('provider_id',[$id])->update(['status' => 'offline']);
        return back()->with('flash_success', "Provider Disapproved");
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function request($id){

        try{

            $requests = UserRequests::where('user_requests.provider_id',$id)
                    ->RequestHistory()
                    ->get();
                if(Auth::guard('admin')->user()){
                   return view('admin.request.index', compact('requests'));
                }elseif(Auth::guard('dispatcher')->user()){
                    return view('dispatcher.request.index', compact('requests'));
                }
           
        } catch (Exception $e) {
            return back()->with('flash_error','Something Went Wrong!');
        }
    }

    /**
     * account statements.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function statement($id){

        try{

            $requests = UserRequests::where('provider_id',$id)
                        ->where('status','COMPLETED')
                        ->with('payment')
                        ->get();

            $rides = UserRequests::where('provider_id',$id)->with('payment')->orderBy('id','desc')->paginate(10);
            $cancel_rides = UserRequests::where('status','CANCELLED')->where('provider_id',$id)->count();
            $Provider = Provider::find($id);
            $revenue = UserRequestPayment::whereHas('request', function($query) use($id) {
                                    $query->where('provider_id', $id );
                                })->select(\DB::raw(
                                   'SUM(ROUND(provider_pay)) as overall, SUM(ROUND(provider_commission)) as commission' 
                               ))->get();


            $Joined = $Provider->created_at ? '- Joined '.$Provider->created_at->diffForHumans() : '';

            if(Auth::guard('admin')->user()){
               return view('admin.providers.statement', compact('rides','cancel_rides','revenue'))
                        ->with('page',$Provider->first_name."'s Overall Statement ". $Joined);
            }elseif(Auth::guard('dispatcher')->user()){
                return view('dispatcher.providers.statement', compact('rides','cancel_rides','revenue'))
                        ->with('page',$Provider->first_name."'s Overall Statement ". $Joined);
            } 

        } catch (Exception $e) {
            return back()->with('flash_error','Something Went Wrong!');
        }
    }

    public function Accountstatement($id){

        try{

            $requests = UserRequests::where('provider_id',$id)
                        ->where('status','COMPLETED')
                        ->with('payment')
                        ->get();

            $rides = UserRequests::where('provider_id',$id)->with('payment')->orderBy('id','desc')->paginate(10);
            $cancel_rides = UserRequests::where('status','CANCELLED')->where('provider_id',$id)->count();
            $Provider = Provider::find($id);
            $revenue = UserRequestPayment::whereHas('request', function($query) use($id) {
                                    $query->where('provider_id', $id );
                                })->select(\DB::raw(
                                   'SUM(ROUND(fixed) + ROUND(distance)) as overall, SUM(ROUND(commision)) as commission' 
                               ))->get();


            $Joined = $Provider->created_at ? '- Joined '.$Provider->created_at->diffForHumans() : '';

            return view('account.providers.statement', compact('rides','cancel_rides','revenue'))
                        ->with('page',$Provider->first_name."'s Overall Statement ". $Joined);

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
    public function provider_seleted_delete(Request $request)
    {
            // print_r($request->deleted_id);

        if(Setting::get('demo_mode', 0) == 1) {
            return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
        }

            foreach ($request->deleted_id as $key => $value) {
               if($value)
               {
                   $delete = Provider::findOrFail($value);
                   $delete->delete();
                }
            }

           return response()->json(['success' => 'success']);

    }
    /**
     * Online Providers
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function make_online_provider(Request $request)
    { 
        try { 
            if(Setting::get('demo_mode', 0) == 1) {
                return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appoets.com');
            }
            
            $provider_services = ProviderService::whereIn('status',['offline','hold'])->get(); 
            foreach ($provider_services as $key => $value) {
               if($value)
               {
                   $save = ProviderService::findOrFail($value->id);
                   $save->status = 'active';
                   $save->save();

                   $update = Provider::findOrFail($value->provider_id);
                   $update->updated_at=\Carbon\Carbon::now();
                   $update->save();
                }
            }

           return back()->with('flash_success','Successfully onlined for all offline providers'); 
        } catch(Exception $e) { 
            return redirect()->route('admin.setting')->with('flash_error','Something Went Wrong!');
        }

    }
}

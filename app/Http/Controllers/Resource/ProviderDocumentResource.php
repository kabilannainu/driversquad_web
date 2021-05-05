<?php

namespace App\Http\Controllers\Resource;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\SendPushNotification;

use DB;
use Exception;
use Setting;
use Auth;

use App\Provider;
use App\ServiceType;
use App\ProviderService;
use App\ProviderDocument;
use App\Helpers\Helper;

use App\Document;
use Storage;

class ProviderDocumentResource extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $provider)
    {
        try {
            $Provider = Provider::findOrFail($provider);
            $ProviderService = ProviderService::where('provider_id',$provider)->with('service_type')->get();
            
            $ProviderServiceCheck = ProviderService::where('provider_id',$provider)->get()->pluck('service_type_id');
            $ServiceTypes = ServiceType::whereNotIn('id',$ProviderServiceCheck)->get();
            
            // $ServiceTypes = ServiceType::all();

            $DriverDocuments = Document::all();

            if(Auth::guard('admin')->user()){
               return view('admin.providers.document.index', compact('Provider', 'ServiceTypes','ProviderService','DriverDocuments'));
            }elseif(Auth::guard('dispatcher')->user()){
                return view('dispatcher.providers.document.index', compact('Provider', 'ServiceTypes','ProviderService','DriverDocuments'));
            }
            
        } catch (ModelNotFoundException $e) {
            return redirect()->route('admin.index');
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
    public function store(Request $request, $provider)
    {
        $this->validate($request, [
                'service_type' => 'required|exists:service_types,id',
                'service_number' => 'required',
                'service_model' => 'required',
            ]);
        

        try {
            
            $ProviderService = ProviderService::where('provider_id', $provider)->firstOrFail();
            
            ProviderService::create([
                    'provider_id' => $provider,
                    'service_type_id' => $request->service_type,
                    'status' => 'active',
                    'service_number' => $request->service_number,
                    'service_model' => $request->service_model,
                ]);

            // $ProviderService->update([
            //         'service_type_id' => $request->service_type,
            //         'status' => 'offline',
            //         'service_number' => $request->service_number,
            //         'service_model' => $request->service_model,
            //     ]);

            // Sending push to the provider
            (new SendPushNotification)->DocumentsVerfied($provider);

        } catch (ModelNotFoundException $e) {
            ProviderService::create([
                    'provider_id' => $provider,
                    'service_type_id' => $request->service_type,
                    'status' => 'active',
                    'service_number' => $request->service_number,
                    'service_model' => $request->service_model,
                ]);
        }

        if(Auth::guard('admin')->user()){
           return redirect()->route('admin.provider.document.index', $provider)->with('flash_success', 'Provider service type updated successfully!');
        }elseif(Auth::guard('dispatcher')->user()){
            return redirect()->route('dispatcher.provider.document.index', $provider)->with('flash_success', 'Provider service type updated successfully!');
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
    public function edit($provider, $id)
    {
        try {
            $Document = ProviderDocument::where('provider_id', $provider)
                ->findOrFail($id);


            if(Auth::guard('admin')->user()){
               return view('admin.providers.document.edit', compact('Document'));
            }elseif(Auth::guard('dispatcher')->user()){
               return view('dispatcher.providers.document.edit', compact('Document'));
            }
            
        } catch (ModelNotFoundException $e) {
            if(Auth::guard('admin')->user()){
               return redirect()->route('admin.index');
            }elseif(Auth::guard('dispatcher')->user()){
               return redirect()->route('dispatcher.index');
            }

        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $provider, $id)
    {
        try {
            $Document = ProviderDocument::where('provider_id', $provider)
                ->findOrFail($id);
            $Document->update(['status' => 'ACTIVE']);

            if(Auth::guard('admin')->user()){
               return redirect()
                ->route('admin.provider.document.index', $provider)
                ->with('flash_success', 'Provider document has been approved.');
            }elseif(Auth::guard('dispatcher')->user()){
               return redirect()
                ->route('dispatcher.provider.document.index', $provider)
                ->with('flash_success', 'Provider document has been approved.');
            } 
        } catch (ModelNotFoundException $e) {

            if(Auth::guard('admin')->user()){
               return redirect()
                ->route('admin.provider.document.index', $provider)
                ->with('flash_error', 'Provider not found!');
            }elseif(Auth::guard('dispatcher')->user()){
                return redirect()
                ->route('dispatcher.provider.document.index', $provider)
                ->with('flash_error', 'Provider not found!');
            } 
           
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($provider, $id)
    { 

        try {

            $Document = ProviderDocument::findOrfail($id); 
            $Document->delete();

            if(Auth::guard('admin')->user()){
               return redirect()
                ->route('admin.provider.document.index', $provider)
                ->with('flash_success', 'Provider document has been deleted');
            }elseif(Auth::guard('dispatcher')->user()){
                return redirect()
                ->route('dispatcher.provider.document.index', $provider)
                ->with('flash_success', 'Provider document has been deleted');
            } 
            
        } catch (ModelNotFoundException $e) { 
            if(Auth::guard('admin')->user()){
               return redirect()
                ->route('admin.provider.document.index', $provider)
                ->with('flash_error', 'Provider not found!');
            }elseif(Auth::guard('dispatcher')->user()){
                return redirect()
                ->route('dispatcher.provider.document.index', $provider)
                ->with('flash_error', 'Provider not found!');
            } 
            
        }
    }

    /**
     * Delete the service type of the provider.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function service_destroy(Request $request, $provider, $id)
    {
        try {

            $ProviderService = ProviderService::where('id', $id)->firstOrFail();
            $ProviderService->delete();

            if(Auth::guard('admin')->user()){
               return redirect()
                ->route('admin.provider.document.index', $provider)
                ->with('flash_success', 'Provider service has been deleted.');
            }elseif(Auth::guard('dispatcher')->user()){
                return redirect()
                ->route('dispatcher.provider.document.index', $provider)
                ->with('flash_success', 'Provider service has been deleted.');
            } 
            
        } catch (ModelNotFoundException $e) {
            if(Auth::guard('admin')->user()){
               return redirect()
                ->route('admin.provider.document.index', $provider)
                ->with('flash_error', 'Provider service not found!');
            }elseif(Auth::guard('dispatcher')->user()){
                return redirect()
                ->route('dispatcher.provider.document.index', $provider)
                ->with('flash_error', 'Provider service not found!');
            } 
            
        }
    }
    /**
     * Upload provider documents
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function provider_document_upload(Request $request, $provider)
    { 
        try {

            $providerdata = $request->all(); 

           if((array_key_exists('document',$providerdata))){ 

                //for($i=0; $i<sizeof($providerdata['document']);$i++)
                foreach($providerdata['document'] as $key => $val)
                {   
                
                    ProviderDocument::create([
                            //'url' => Storage::putFile('provider/document', $providerdata['document'][$i], 'public'),
                            'url' => Storage::putFile('provider/document', $val, 'public'),
                            'provider_id' => $provider,
                            //'document_id'=>$providerdata['id'][$i],
                            'document_id'=>$key,
                            'status' => 'ASSESSING',
                            //'expires_at'=> Carbon::parse($data['expires_at'][$i])->format('Y/m/d'),
                        ]);
                    
                }
            }
            return back()->with('flash_success','Provider Details Saved Successfully');
            
      } catch (Exception $e) {
        dd($e);
            return back()->with('flash_error', 'Provider Not Found');
        }
    }
}

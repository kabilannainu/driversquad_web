<?php

namespace App\Http\Controllers\ProviderAuth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;

use Setting;
use Validator;

use App\Provider;
use App\Document;
use App\ProviderService;
use App\ProviderDocument;
use Storage;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after login / registration.
     *
     * @var string
     */
    protected $redirectTo = '/provider/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('provider.guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'phone_number' => 'required',
            'country_code' => 'required',
            // 'email' => 'required|email|max:255|unique:providers',
            // 'password' => 'required|min:6|confirmed',
            // 'service_type' => 'required',
            // 'service_number' => 'required',
            // 'service_model' => 'required',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return Provider
     */
    protected function create(array $data)
    {
        $Provider = Provider::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            //'gender' => $data['gender']?:"MALE",
            'mobile' => $data['phone_number'],
            'password' => bcrypt('123456'),
            'country_code' => $data['country_code'],
            'child_seat' => $data['child_seat'],
            'pet_allowed' => $data['pet_allowed'],
        ]);

        // $provider_service = ProviderService::create([
        //     'provider_id' => $Provider->id,
        //     'service_type_id' => $data['service_type'],
        //     'service_number' => $data['service_number'],
        //     'service_model' => $data['service_model'],
        // ]);

        if(Setting::get('demo_mode', 0) == 1) {
            $Provider->update(['status' => 'approved']);
            $provider_service->update([
                'status' => 'active',
            ]);
        }

        // if((array_key_exists('document',$data))){
        //     for($i=0; $i<sizeof($data['document']);$i++)
        //     {   
           

        //         ProviderDocument::create([
        //                 'url' => Storage::putFile('user/profile', $data['document'][$i], 'public'),
        //                 'provider_id' => $Provider->id,
        //                 'document_id'=>$data['id'][$i],
        //                 'status' => 'ASSESSING',
        //                 //'expires_at'=> Carbon::parse($data['expires_at'][$i])->format('Y/m/d'),
        //             ]);
                
        //     }
        // }
        
        return $Provider;
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        $DriverDocuments = Document::get();
        return view('provider.auth.register',compact('DriverDocuments'));
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard('provider');
    }
}

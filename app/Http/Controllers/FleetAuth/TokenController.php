<?php

namespace App\Http\Controllers\FleetAuth;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Http\Controllers\Controller;

use Tymon\JWTAuth\Exceptions\JWTException;
use App\Notifications\ResetPasswordOTP;

use Auth;
use Config;
use JWTAuth;
use App\Helpers\Helper;
use Setting;
use Notification;

use App\Fleet;
use App\FleetDevice;

class TokenController extends Controller
{

   public function authenticate(Request $request)
   {
    $this->validate($request, [
                'device_id' => 'required',
                'device_type' => 'required|in:android,ios',
                'device_token' => 'required',
                'email' => 'required|email',
                'password' => 'required|min:6',
            ]);

        Config::set('auth.providers.users.model', 'App\Fleet');

        $credentials = $request->only('email', 'password');

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'The email address or password you entered is incorrect.'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Something went wrong, Please try again later!'], 500);
        }



        $User = Fleet::with('device')->find(Auth::user()->id);
//dd(Auth::user()->id);
        $User->access_token = $token;
        $User->currency = Setting::get('currency', '$');
        $User->sos = Setting::get('sos_number', '911');

        if($User->device) {
            FleetDevice::where('id',$User->device->id)->update([
        
                'udid' => $request->device_id,
                'token' => $request->device_token,
                'type' => $request->device_type,
            ]);
            
        } else {
            FleetDevice::create([
                    'fleet_id' => $User->id,
                    'udid' => $request->device_id,
                    'token' => $request->device_token,
                    'type' => $request->device_type,
                ]);
        }

        return response()->json($User);

    }

    public function logout(Request $request)
    {
        try {
            FleetDevice::where('fleet_id', $request->id)->update(['udid'=> '', 'token' => '']);
            
            
            return response()->json(['message' => trans('api.logout_success')]);
        } catch (Exception $e) {
            return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }

    /**
     * Forgot Password.
     *
     * @return \Illuminate\Http\Response
     */
    public function forgot_password(Request $request){

        $this->validate($request, [
                'email' => 'required|email|exists:fleets,email',
            ]);

        try{  
            
            $fleet = Fleet::where('email' , $request->email)->first();

            $otp = mt_rand(100000, 999999);

            $fleet->otp = $otp;
            $fleet->save();

            Notification::send($fleet, new ResetPasswordOTP($otp));

            return response()->json([
                'message' => 'OTP sent to your email!',
                'fleet' => $fleet
            ]);

        }catch(Exception $e){
                return response()->json(['error' => trans('api.something_went_wrong')], 500);
        }
    }


    /**
     * Reset Password.
     *
     * @return \Illuminate\Http\Response
     */

    public function reset_password(Request $request){

        $this->validate($request, [
                'password' => 'required|confirmed|min:6',
                'id' => 'required|numeric|exists:providers,id'
            ]);

        try{

            $Fleet = Fleet::findOrFail($request->id);

            $Fleet->password = bcrypt($request->password);
            $Fleet->save();
            if($request->ajax()) {
                return response()->json(['message' => 'Password Updated']);
            }

        }catch (Exception $e) {
            if($request->ajax()) {
                return response()->json(['error' => trans('api.something_went_wrong')]);
            }
        }
    }

    public function profile_detail(Request $request)
    {

       // dd(\Auth::user()->id);
        $fleet = Fleet::where('id',\Auth::user()->id)->first();

        return $fleet;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Provider  $provider
     * @return \Illuminate\Http\Response
     */
    public function profile_update(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|max:255',
            'company' => 'required|max:255',
            'mobile' => 'required|digits_between:6,13',
            'logo' => 'mimes:jpeg,jpg,bmp,png|max:5242880',
        ]);

        try{
            $fleet = Auth::user();
          //  dd($fleet);
            $fleet->name = $request->name;
            $fleet->mobile = $request->mobile;
            $fleet->company = $request->company;
            if($request->hasFile('logo')){
                $fleet->logo = $request->logo->store('fleet/profile');  
            }
            $fleet->save();

            if($request->ajax()){

                return response()->json(['message' => 'Profile Updated']);

            }else{
                return redirect()->back()->with('flash_success','Profile Updated');
            }

            
        }

        catch (Exception $e) {
            if($request->ajax()){

                return response()->json(['message' => 'Something Went Wrong!'],500);

            }else{

             return back()->with('flash_error','Something Went Wrong!');
            }
        }
        
    }

     public function password_update(Request $request)
    {

        $this->validate($request,[
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        try {

           $Fleet = Fleet::find(Auth::user()->id);

            if(password_verify($request->old_password, $Fleet->password))
            {
                $Fleet->password = bcrypt($request->password);
                $Fleet->save();

                if($request->ajax()){

                    return response()->json(['message' => 'Password Updated']);

                }else{

                 return redirect()->back()->with('flash_success','Password Updated');
                }

                
            } else {

                if($request->ajax()){

                    return response()->json(['message' => 'Password entered doesn\'t match'],500);

                }else{

                 return back()->with('flash_error','Password entered doesn\'t match');
                }

                
            }
        } catch (Exception $e) {

             if($request->ajax()){

                return response()->json(['message' => 'Something Went Wrong!'],500);

            }else{

             return back()->with('flash_error','Something Went Wrong!');
            }
        }
    }
}

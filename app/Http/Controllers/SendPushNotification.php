<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\ProviderDevice;
use App\ProviderService;
use Exception;
use Log;
use Setting;
use Edujugon\PushNotification\PushNotification;

class SendPushNotification extends Controller
{
	/**
     * New Ride Accepted by a Driver.
     *
     * @return void
     */
    public function RideAccepted($request){

    	return $this->sendPushToUser($request->user_id, trans('api.push.request_accepted'), 'accepted');
    }

    /**
     * New Ride Accepted Remain Driver .
     *
     * @return void
     */
    public function RideAcceptedRemainProviders($request,$provider){

        return $this->sendPushToProvider($request->provider_id, 'Current ride request has been Accepted by '.$provider->first_name.'-'.$provider->service->service_number);
    }
    /**
     * New Ride Cancelled All Driver .
     *
     * @return void
     */
    public function RideCancelledAllProviders($request,$user_name){

        return $this->sendPushToProvider($request->provider_id, 'User cancelled the request');
    }

    /**
     * Admin Approved Driver .
     *
     * @return void
     */
    public function AdminApproveProvider($provider){

        return $this->sendPushToProvider($provider->id, 'Admin approved your account to ride SERVICE NAME :'.$provider->service->service_type->name);
    }

    /**
     * Admin Disapproved Driver .
     *
     * @return void
     */
    public function AdminDisapproveProvider($provider){

        return $this->sendPushToProvider($provider->id, 'Admin disapproved your account to ride SERVICE NAME :'.$provider->service->service_type->name);
    }


    /**
     * Driver Arrived at your location.
     *
     * @return void
     */
    public function user_schedule($user){

        return $this->sendPushToUser($user, trans('api.push.schedule_start'));
    }

    /**
     * New Incoming request
     *
     * @return void
     */
    public function provider_schedule($provider){

        return $this->sendPushToProvider($provider, trans('api.push.schedule_start'));

    }

    /**
     * New Ride Accepted by a Driver.
     *
     * @return void
     */
    public function UserCancellRide($request){

        return $this->sendPushToProvider($request->provider_id, trans('api.push.user_cancelled'));
    }


    /**
     * New Ride Accepted by a Driver.
     *
     * @return void
     */
    public function ProviderCancellRide($request){

        return $this->sendPushToUser($request->user_id, trans('api.push.provider_cancelled'));
    }

    /**
     * Driver Arrived at your location.
     *
     * @return void
     */
    public function Arrived($request){

        return $this->sendPushToUser($request->user_id, trans('api.push.arrived'), 'arrived');
    }

    /**
     * Driver Picked You  in your location.
     *
     * @return void
     */
    public function Pickedup($request){

        return $this->sendPushToUser($request->user_id, trans('api.push.pickedup'), 'pickedup');
    }

    /**
     * Driver Reached  destination
     *
     * @return void
     */
    public function Dropped($request){

        Log::info( trans('api.push.dropped').Setting::get('currency').$request->payment->total.' by '.$request->payment_mode);

        return $this->sendPushToUser($request->user_id, trans('api.push.dropped').Setting::get('currency').$request->payment->total.' by '.$request->payment_mode, 'dropped');
    }

    /**
     * Your Ride Completed
     *
     * @return void
     */
    public function Complete($request){

        return $this->sendPushToUser($request->user_id, trans('api.push.complete'), 'completed');
    }

    
     
    /**
     * Rating After Successful Ride
     *
     * @return void
     */
    public function Rate($request){

        return $this->sendPushToUser($request->user_id, trans('api.push.rate'));
    }


    /**
     * Money added to user wallet.
     *
     * @return void
     */
    public function ProviderNotAvailable($user_id){

        return $this->sendPushToUser($user_id,trans('api.push.provider_not_available'));
    }

    /**
     * New Incoming request
     *
     * @return void
     */
    public function IncomingRequest($provider, $voip=false){

        $this->sendPushToProvider($provider, trans('api.push.incoming_request'), 'searching');

    } 
    

    /**
     * Driver Documents verfied.
     *
     * @return void
     */
    public function DocumentsVerfied($provider_id){

        return $this->sendPushToProvider($provider_id, trans('api.push.document_verfied'));
    }


    /**
     * Money added to user wallet.
     *
     * @return void
     */
    public function WalletMoney($user_id, $money){

        return $this->sendPushToUser($user_id, $money.' '.trans('api.push.added_money_to_wallet'));
    }

    /**
     * Money charged from user wallet.
     *
     * @return void
     */
    public function ChargedWalletMoney($user_id, $money){

        return $this->sendPushToUser($user_id, $money.' '.trans('api.push.charged_from_wallet'));
    }

    /**
     * Sending Push to a user Device.
     *
     * @return void
     */
    public function sendPushToUser($user_id, $push_message,$sound="default"){

    	try{

	    	// $user = User::findOrFail($user_id);


      //       if($user->device_token != ""){

      //           \Log::info('sending push for user : '. $user->first_name);

    	 //    	if($user->device_type == 'ios'){

    	 //    		return \PushNotification::app('IOSUser')
    		//             ->to($user->device_token)
    		//             ->send($push_message, array('sound' => $sound.'.caf'));

    	 //    	}elseif($user->device_type == 'android'){
    	    		
    	 //    		return \PushNotification::app('AndroidUser') 
      //                   ->to($user->device_token)
      //                   ->send($push_message, array('sound' => $sound));
                       

    	 //    	}
      //       }



            $user = User::findOrFail($user_id);


            if($user->device_token != ""){

               \Log::info('sending push for user : '. $user->first_name);
                \Log::info($push_message);

                if($user->device_type == 'ios'){
                     if(env('IOS_USER_ENV')=='development'){
                        $crt_user_path=app_path().'/apns/user/Tranxit_enterprise_user.pem';
                        $crt_provider_path=app_path().'/apns/user/Tranxit_enterprise_user.pem';
                        $dry_run = true;
                    }
                   else{
                         $crt_user_path=app_path().'/apns/user/Tranxit_enterprise_user.pem';
                        $crt_provider_path=app_path().'/apns/user/Tranxit_enterprise_user.pem';
                        $dry_run = false;
                    }
                    
                   $push = new PushNotification('apn');

                    $push->setConfig([
                            'certificate' => $crt_user_path,
                            'passPhrase' => env('IOS_USER_PUSH_PASS', 'apple'),
                            'dry_run' => $dry_run
                        ]);

                   $send=  $push->setMessage([
                            'aps' => [
                                'alert' => [
                                    'body' => $push_message
                                ],
                                'sound' => 'default',
                                'badge' => 1

                            ],
                            'extraPayLoad' => [
                                'custom' => $push_message
                            ]
                        ])
                        ->setDevicesToken($user->device_token)->send();
                        \Log::info('sent');
                    
                    return $send;

                }elseif($user->device_type == 'android'){

                   $push = new PushNotification('fcm');
                   $send=  $push->setMessage(['message'=>$push_message])
                        ->setDevicesToken($user->device_token)->send();
                    
                    return $send;
                       

                }
            }

    	} catch(Exception $e){
    		return $e;
    	}

    }

    /**
     * Sending Push to a user Device.
     *
     * @return void
     */
    public function sendPushToProvider($provider_id, $push_message){

    	try{

                    // if($voip == true)
                    // {
                    //     \Log::info('voip token');
                        
                    //     \PushNotification::app('IOSProviderVoip')
                    //         ->to($provider->voip_token)
                    //         ->send($push_message);
                    // }

	    	// $provider = ProviderDevice::where('provider_id',$provider_id)->with('provider')->first();

            // $provider = ProviderDevice::where('provider_id',$provider_id)->where('current','1')->with('provider')->orderBy('id','DESC')->first();

            // // dd($provider->token);

            // if($provider->token != ""){
                
            //     \Log::info('sendnig push for provider : '. $provider->provider->first_name);

            // 	if($provider->type == 'ios'){

            // 		\PushNotification::app('IOSProvider')
        	   //          ->to($provider->token)
        	   //          ->send($push_message); 

            // 	}elseif($provider->type == 'android'){
            		
            // 		\PushNotification::app('AndroidProvider')
        	   //          ->to($provider->token)
            //             ->send($push_message);
        	            

            // 	}
            // }


            $provider = ProviderDevice::where('provider_id',$provider_id)->first();           

            if($provider->token != ""){

                if($provider->type == 'ios'){

                    if(env('IOS_USER_ENV')=='development'){
                        $crt_user_path=app_path().'/apns/user/Tranxit_enterprise_user.pem';
                        $crt_provider_path=app_path().'/apns/user/Tranxit_enterprise_user.pem';
                        $dry_run = true;
                    }
                      else{
                         $crt_user_path=app_path().'/apns/user/Tranxit_enterprise_user.pem';
                        $crt_provider_path=app_path().'/apns/user/Tranxit_enterprise_user.pem';
                        $dry_run = false;
                    }

                   $push = new PushNotification('apn');
                   $push->setConfig([
                            'certificate' => $crt_provider_path,
                            'passPhrase' => env('IOS_PROVIDER_PUSH_PASS', 'apple'),
                            'dry_run' => $dry_run
                        ]);
                   $send=  $push->setMessage([
                            'aps' => [
                                'alert' => [
                                    'body' => $push_message
                                ],
                                'sound' => 'default',
                                'badge' => 1

                            ],
                            'extraPayLoad' => [
                                'custom' => $push_message
                            ]
                        ])
                        ->setDevicesToken($provider->token)->send();
                
                    
                    return $send;

                }elseif($provider->type == 'android'){
                    
                   $push = new PushNotification('fcm');
                   $send=  $push->setMessage(['message'=>$push_message])
                        ->setDevicesToken($provider->token)->send();
                    
                    return $send;
                }
            }

    	} catch(Exception $e){
    		return $e;
    	}

    }

}

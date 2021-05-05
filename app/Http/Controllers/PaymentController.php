<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\SendPushNotification;

use Razorpay\Api\Api;

use Stripe\Charge;
use Stripe\Stripe;
use Stripe\StripeInvalidRequestError;

use Auth;
use Setting;
use Exception;

use App\Card;
use App\User;
use App\WalletPassbook;
use App\UserRequests;
use App\UserRequestPayment;
 
use Softon\Indipay\Facades\Indipay;
 

class PaymentController extends Controller
{
       /**
     * payment for user.
     *
     * @return \Illuminate\Http\Response
     */
    public function payment(Request $request)
    {

        $this->validate($request, [
                'request_id' => 'required|exists:user_request_payments,request_id|exists:user_requests,id,paid,0,user_id,'.Auth::user()->id
            ]);


        $UserRequest = UserRequests::find($request->request_id);
         
        if($UserRequest->payment_mode == 'CARD') {

            $RequestPayment = UserRequestPayment::where('request_id',$request->request_id)->first(); 
            
            $StripeCharge = $RequestPayment->total * 100;
           
            try {

                $Card = Card::where('user_id',Auth::user()->id)->where('is_default',1)->first();
                $stripe_secret = Setting::get('stripe_secret_key');

                Stripe::setApiKey(Setting::get('stripe_secret_key'));
                
                if($StripeCharge  == 0){

                $RequestPayment->payment_mode = 'CARD';
                $RequestPayment->save();

                $UserRequest->paid = 1;
                $UserRequest->status = 'COMPLETED';
                $UserRequest->save();


                   if($request->ajax()) {
                   return response()->json(['message' => trans('api.paid')]); 
                } else {
                    return redirect('dashboard')->with('flash_success','Paid');
                }
               }else{
                
               $Charge = Charge::create(array(
                      "amount" => $StripeCharge,
                      "currency" => "usd",
                      "customer" => Auth::user()->stripe_cust_id,
                      "card" => $Card->card_id,
                      "description" => "Payment Charge for ".Auth::user()->email,
                      "receipt_email" => Auth::user()->email
                    ));
                 
                $RequestPayment->payment_id = $Charge["id"];
                $RequestPayment->payment_mode = 'CARD';
                $RequestPayment->save();

                $UserRequest->paid = 1;
                $UserRequest->status = 'COMPLETED';
                $UserRequest->save();

                if($request->ajax()) {
                   return response()->json(['message' => trans('api.paid')]); 
                } else {
                    return redirect('dashboard')->with('flash_success','Paid');
                }
              }

            } catch(StripeInvalidRequestError $e){
              
                if($request->ajax()){
                    return response()->json(['error' => $e->getMessage()], 500);
                } else {
                    return back()->with('flash_error', $e->getMessage());
                }
            } catch(Exception $e) {
                if($request->ajax()){
                    return response()->json(['error' => $e->getMessage()], 500);
                } else {
                    return back()->with('flash_error', $e->getMessage());
                }
            }
        }
        else
        {
           $RequestPayment = UserRequestPayment::where('request_id',$request->request_id)->first();  

           $tid=rand(10,10000000);
           /* All Required Parameters by your Gateway */
        
            $parameters = [
            
              'tid' => $tid,
              
              'order_id' => 'trip-'.$request->request_id,
              
              'amount' => $RequestPayment->total,
              
            ];
            
            // gateway = CCAvenue / PayUMoney / EBS / Citrus / InstaMojo / ZapakPay / Mocker
            
            $order = Indipay::gateway('CCAvenue')->prepare($parameters);
            return Indipay::process($order);
        }
    }


    /**
     * add wallet money for user.
     *
     * @return \Illuminate\Http\Response
     */
    public function add_money(Request $request){

      if($request->card_id != 'CC_AVENUE')
      {
          $this->validate($request, [
                  'amount' => 'required|integer',
                  'card_id' => 'required|exists:cards,card_id,user_id,'.Auth::user()->id
              ]);

          try{
              
              $StripeWalletCharge = $request->amount * 100;

              Stripe::setApiKey(Setting::get('stripe_secret_key'));

              $Charge = Charge::create(array(
                    "amount" => $StripeWalletCharge,
                    "currency" => "usd",
                    "customer" => Auth::user()->stripe_cust_id,
                    "card" => $request->card_id,
                    "description" => "Adding Money for ".Auth::user()->email,
                    "receipt_email" => Auth::user()->email
                  ));

              $update_user = User::find(Auth::user()->id);
              $update_user->wallet_balance += $request->amount;
              $update_user->save();

              WalletPassbook::create([
                'user_id' => Auth::user()->id,
                'amount' => $request->amount,
                'status' => 'CREDITED',
                'via' => 'CARD',
              ]);

              Card::where('user_id',Auth::user()->id)->update(['is_default' => 0]);
              Card::where('card_id',$request->card_id)->update(['is_default' => 1]);

              //sending push on adding wallet money
              (new SendPushNotification)->WalletMoney(Auth::user()->id,currency($request->amount));

              if($request->ajax()){
                  return response()->json(['message' => currency($request->amount).trans('api.added_to_your_wallet'), 'user' => $update_user]); 
              } else {
                  return redirect('wallet')->with('flash_success',currency($request->amount).' added to your wallet');
              }

          } catch(StripeInvalidRequestError $e) {
              if($request->ajax()){
                   return response()->json(['error' => $e->getMessage()], 500);
              }else{
                  return back()->with('flash_error',$e->getMessage());
              }
          } catch(Exception $e) {
              if($request->ajax()) {
                  return response()->json(['error' => $e->getMessage()], 500);
              } else {
                  return back()->with('flash_error', $e->getMessage());
              }
          }
      }
      else
      {
          $tid=rand(10,10000000);

         /* All Required Parameters by your Gateway */
      
          $parameters = [
          
            'tid' => $tid,
            
            'order_id' => 'wallet-'.Auth::user()->id,
            
            'amount' => $request->amount,
            
          ];
          
          // gateway = CCAvenue / PayUMoney / EBS / Citrus / InstaMojo / ZapakPay / Mocker
          
          $order = Indipay::gateway('CCAvenue')->prepare($parameters);
          return Indipay::process($order);
      }
    }


    /**
     * Show the CC avanue payment for wallet response web
     *
     * @return \Illuminate\Http\Response
     */
    public function ccavanue_web_response(Request $request)
    { 
         // For default Gateway
        $response = Indipay::response($request);
        
        // For Otherthan Default Gateway
        $response = Indipay::gateway('CCAvenue')->response($request);

        $Check=explode("-",$response['order_id']);
        
        if($response['order_status'] == 'Success')
        {
          
          if($Check[0] == 'wallet')
          {     
                $user_update=User::findOrFail($Check[1]);
                $user_update->wallet_balance=$response['amount'];
                $user_update->save();
            
               $update=new WalletPassbook();
               $update->user_id=$Check[1];
               $update->amount=$response['amount'];
               $update->status='CREDITED';
               $update->via='CC_AVENUE';
               $update->save();
          }
          else
          { 

              $update=UserRequests::findOrFail($Check[1]);
              $update->paid=1;
              $update->save();

              $updatepayment=UserRequestPayment::whererequest_id($Check[1])->first();
              $updatepayment->payment_id=$response['tracking_id'];
              $updatepayment->save();

          }

            return redirect('dashboard')->with('flash_success', $response['order_status']);

        } 
        else 
        {
            return redirect('dashboard')->with('flash_error', $response['order_status']);
        }

        

    }
    /**
     * Show the CC avanue payment for wallet cancel response web
     *
     * @return \Illuminate\Http\Response
     */
    public function ccavanue_web_cancel_response(Request $request)
    {

          // For default Gateway
        $response = Indipay::response($request);
        
        // For Otherthan Default Gateway
        $response = Indipay::gateway('CCAvenue')->response($request);

         
        return redirect('dashboard')->with('flash_error', $response['order_status']);
       
    }

    /**
     * Show the CC avanue payment for wallet response web
     *
     * @return \Illuminate\Http\Response
     */
    public function ccavanue_response(Request $request)
    { 
         // For default Gateway
        $response = Indipay::response($request);
        
        // For Otherthan Default Gateway
        $response = Indipay::gateway('CCAvenue')->response($request);

         $Check=explode("-",$response['order_id']);
        
        if($response['order_status'] == 'Success')
        {
          
          if($Check[0] == 'wallet')
          { 

              $user_update=User::findOrFail($Check[1]);
              $user_update->wallet_balance=$response['amount'];
              $user_update->save();


              $update=new WalletPassbook();
              $update->user_id=$Check[1];
              $update->amount=$response['amount'];
              $update->status='CREDITED';
              $update->via='CC_AVENUE';
              $update->save();  
          }
          else
          { 
              $update=UserRequests::findOrFail($Check[1]);
              $update->paid=1;
              $update->save();

              $updatepayment=UserRequestPayment::whererequest_id($Check[1])->first();
              $updatepayment->payment_id=$response['tracking_id'];
              $updatepayment->save(); 
          }
            return $response['order_status'];
             
        } 
        else 
        { 
            return $response['order_status'];
        }

        

    }
    /**
     * Show the CC avanue payment for wallet cancel response web
     *
     * @return \Illuminate\Http\Response
     */
    public function ccavanue_cancel_response(Request $request)
    {

          // For default Gateway
        $response = Indipay::response($request);
        
        // For Otherthan Default Gateway
        $response = Indipay::gateway('CCAvenue')->response($request);
 
        return $response['order_status'];
         
    }

    /**
     * RSA KEY Generate
     *
     * @return \Illuminate\Http\Response
     */
    public function rsa_key_generate(Request $request)
    {

        $url = "https://secure.ccavenue.com/transaction/getRSAKey";
        $fields = array(
                'access_code'=>$request->access_code,
                'order_id'=>$request->order_id
        );

        $postvars='';
        $sep='';
        foreach($fields as $key=>$value)
        {
                $postvars.= $sep.urlencode($key).'='.urlencode($value);
                $sep='&';
        }
        $pem= url("storage/ccavanue/cacert.pem"); 

        $ch = curl_init();

        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_POST,count($fields));
        curl_setopt($ch,CURLOPT_CAINFO,$pem);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$postvars);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);

        $result = curl_exec($ch);

        
        return $result;

    }


    public function rzp_success(Request $request){
      
\Log::info($request->all());     
       if(isset($request->payment_id)){

   

           $api = new Api(Setting::get('rzp_key'),Setting::get('rzp_secret'));

           $payment = $api->payment->fetch($request->payment_id);

           if($payment->status=="authorized"){

                $update_user = User::find(Auth::user()->id);
                $update_user->wallet_balance += $payment->amount/100;
                $update_user->save();
                
               (new SendPushNotification)->WalletMoney(Auth::user()->id,currency($payment->amount/100));

               $amount = $payment->amount/100;

            //for create the user wallet transaction

               WalletPassbook::create([
                'user_id' => Auth::user()->id,
                'amount' => $amount,
                'status' => 'CREDITED',
                'via' => 'razorpay',
              ]);
         

             if($request->ajax()){
               return response()->json(['message' => currency($amount).trans('api.added_to_your_wallet'), 'user' => $update_user]); 
            }else{
                return redirect('wallet')->with('flash_success',currency($amount).' added to your wallet');
            }

           }else{

              if($request->ajax()) {
                 return response()->json(['message' => 'Payment Failed']); 
              } else {
                  return redirect('wallet')->with('flash_error','Not Paid');
              }

           }

            

       }else{

           if($request->ajax()) {
               return response()->json(['message' => 'Payment Failed']); 
            } else {
                return redirect('wallet')->with('flash_error','Not Paid');
            }

       }
    }


    public function rzp_flow(Request $request){
      
       if(isset($request->payment_id)){

            

           $api = new Api(Setting::get('rzp_key'),Setting::get('rzp_secret'));

           $payment = $api->payment->fetch($request->payment_id);

           if($payment->status=="authorized"){

                 $UserRequest = UserRequests::find($request->request_id);
                 $RequestPayment = UserRequestPayment::where('request_id',$request->request_id)->first(); 

                  $RequestPayment->payment_id =$request->payment_id;
                  $RequestPayment->payment_mode = 'razorpay';
                  $RequestPayment->save();

                  $UserRequest->paid = 1;
                  $UserRequest->status = 'COMPLETED';
                  $UserRequest->save();

                

                  if($request->ajax()) {
                     return response()->json(['message' => trans('api.paid')]); 
                  } else {
                      return redirect('dashboard')->with('flash_success','Paid');
                  }



           }else{

              if($request->ajax()) {
                 return response()->json(['message' => 'Payment Failed']); 
              } else {
                  return redirect('dashboard')->with('flash_error','Not Paid');
              }

           }

            

       }else{

           if($request->ajax()) {
               return response()->json(['message' => 'Payment Failed']); 
            } else {
                return redirect('dashboard')->with('flash_error','Not Paid');
            }

       }
    }


}

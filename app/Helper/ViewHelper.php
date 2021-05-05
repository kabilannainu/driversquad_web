<?php

use App\PromocodeUsage;
use App\ServiceType;
use App\RolePermission;
use App\AppsCountries;
use App\User;
use App\UserRequests;


function is_in_polygon($points_polygon, $vertices_x, $vertices_y, $longitude_x, $latitude_y)
{
  $i = $j = $c = 0;
  for ($i = 0, $j = $points_polygon-1 ; $i < $points_polygon; $j = $i++) {
    if ( (($vertices_y[$i] > $latitude_y != ($vertices_y[$j] > $latitude_y)) &&
    ($longitude_x < ($vertices_x[$j] - $vertices_x[$i]) * ($latitude_y - $vertices_y[$i]) / ($vertices_y[$j] - $vertices_y[$i]) + $vertices_x[$i]) ) ) 
        $c = !$c;
  }
  return $c;
}


function currency($value = '')
{
	if($value == ""){
		return Setting::get('currency')."0.00";
	} else {
		return Setting::get('currency').$value;
	}
}

function distance($value = '')
{
    if($value == ""){
        return "0".Setting::get('distance', 'Km');
    }else{
        return $value.Setting::get('distance', 'Km');
    }
}

function img($img){
	if($img == ""){
		return asset('main/avatar.jpg');
	}else if (strpos($img, 'http') !== false) {
        return $img;
    }else{
		return asset('storage/'.$img);
	}
}

function image($img){
	if($img == ""){
		return asset('main/avatar.jpg');
	}else{
		return asset($img);
	}
}

function promo_used_count($promo_id)
{
	return PromocodeUsage::where('status','ADDED')->where('promocode_id',$promo_id)->count();
}

function curl($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $return = curl_exec($ch);
    curl_close ($ch);
    return $return;
}

function get_all_service_types()
{
	return ServiceType::all();
}

function country_list()
{
  return AppsCountries::all();
}

function demo_mode(){
	if(\Setting::get('demo_mode', 0) == 1) {
        return back()->with('flash_error', 'Disabled for demo purposes! Please contact us at info@appdupe.com');
    }
}

/// Send Message to User for Instant ride details

    function sendmessage($request_id){
 

        $request= UserRequests::with('user','provider.service.service_type')->findOrFail($request_id);

        $mobileno = trim($request->user->mobile,'+');
        $otp = $request->user->otp;
        $service_name=$request->provider->service->service_type->name; 
        $service=strtolower($service_name);
       $message = "Your trip details : Pickup : ".$request->s_address.", Drop : ".$request->d_address.", Driver Details : Name : ".$request->provider->first_name.", Number Plate : ".$request->provider->service->service_number.", ServiceType : ".$service." OTP: ".$otp."";
       $authkey = Setting::get('msg91_authkey');
       $sender = 'Tranxit Enterprise';
       
       $curl = curl_init();

          curl_setopt_array($curl, array(
          CURLOPT_URL => "http://control.msg91.com/api/sendotp.php?template=&otp_length=&authkey=".$authkey."&message=".$message."&sender=".$sender."&mobile=".$mobileno."&otp=".$otp."&otp_expiry=&email=",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_SSL_VERIFYHOST => 0,
          CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        //return $response;
        //dd($response);
        // Use file get contents when CURL is not installed on server.
        if(!$response){
           //$response = file_get_contents($url);
          // dd(json_decode($response, true));
            return  $json = json_decode($response, true);
        }else{
          // dd(json_decode($response, true));
            return  $json = json_decode($response, true);
        }
        
    }
    function sendsms($mobileno, $otp){
        $mobileno = trim($mobileno,'+');
        
       $message = "Your ".Setting::get('site_title')." Mobile User Verification Code is ".$otp."";
       $authkey = Setting::get('msg91_authkey');
       $sender = 'Tranxit Enterprise';
       
       $curl = curl_init();

          curl_setopt_array($curl, array(
          CURLOPT_URL => "http://control.msg91.com/api/sendotp.php?template=&otp_length=&authkey=".$authkey."&message=".$message."&sender=".$sender."&mobile=".$mobileno."&otp=".$otp."&otp_expiry=&email=",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_SSL_VERIFYHOST => 0,
          CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        //return $response;
        //dd($response);
        // Use file get contents when CURL is not installed on server.
        if(!$response){
           //$response = file_get_contents($url);
            return  $json = json_decode($response, true);
        }else{
            return  $json = json_decode($response, true);
        }
        
    }

    function voicesms($mobileno ,$otp){
        
          //$mobile = $mobileno;
          $mobileno = trim($mobileno,'+');
          $message = "your otp is ".$otp."";
          $curl = curl_init();
          $authkey = Setting::get('msg91_authkey');

          curl_setopt_array($curl, array(
          CURLOPT_URL => "http://control.msg91.com/api/sendVoiceCall.php?message=".$message."&to=".$mobileno."&from=8807216605&authkey=".$authkey."",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "",
          CURLOPT_SSL_VERIFYHOST => 0,
          CURLOPT_SSL_VERIFYPEER => 0,
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if(!$response){
           //$response = file_get_contents($url);
            return  $json = json_decode($response, true);
        }else{
            return  $json = json_decode($response, true);
        }
    }


    class pointLocation {
    var $pointOnVertex = true; // Check if the point sits exactly on one of the vertices?
 
    function pointLocation() {
    }
 
    function pointInPolygon($point, $polygon, $pointOnVertex = true) {
        $this->pointOnVertex = $pointOnVertex;
 
        // Transform string coordinates into arrays with x and y values
        $point = $this->pointStringToCoordinates($point);
        $vertices = array(); 
        foreach ($polygon as $vertex) {
            $vertices[] = $this->pointStringToCoordinates($vertex); 
        }
 
        // Check if the point sits exactly on a vertex
        if ($this->pointOnVertex == true and $this->pointOnVertex($point, $vertices) == true) {
            return "vertex";
        }
 
        // Check if the point is inside the polygon or on the boundary
        $intersections = 0; 
        $vertices_count = count($vertices);
 
        for ($i=1; $i < $vertices_count; $i++) {
            $vertex1 = $vertices[$i-1]; 
            $vertex2 = $vertices[$i];
            if ($vertex1['y'] == $vertex2['y'] and $vertex1['y'] == $point['y'] and $point['x'] > min($vertex1['x'], $vertex2['x']) and $point['x'] < max($vertex1['x'], $vertex2['x'])) { // Check if point is on an horizontal polygon boundary
                return "boundary";
            }
            if ($point['y'] > min($vertex1['y'], $vertex2['y']) and $point['y'] <= max($vertex1['y'], $vertex2['y']) and $point['x'] <= max($vertex1['x'], $vertex2['x']) and $vertex1['y'] != $vertex2['y']) { 
                $xinters = ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x']; 
                if ($xinters == $point['x']) { // Check if point is on the polygon boundary (other than horizontal)
                    return "boundary";
                }
                if ($vertex1['x'] == $vertex2['x'] || $point['x'] <= $xinters) {
                    $intersections++; 
                }
            } 
        } 
        // If the number of edges we passed through is odd, then it's in the polygon. 
        if ($intersections % 2 != 0) {
            return "inside";
        } else {
            return "outside";
        }
    }
 
    function pointOnVertex($point, $vertices) {
        foreach($vertices as $vertex) {
            if ($point == $vertex) {
                return true;
            }
        }
 
    }
 
    function pointStringToCoordinates($pointString) {
        $coordinates = explode(" ", $pointString);
        return array("x" => $coordinates[0], "y" => $coordinates[1]);
    }
 
}


function check_permission($route,$role_id)
{
    if($role_id!=0)
    {
        $result = array(
            'role_id' => $role_id,
            'name' => $route
        );
       return RolePermission::where($result)->count();
    }
    else
    {
        return 1;
    }
}


function check_route($route,$role_id)
{
   
        $result = array(
            'role_id' => $role_id,
            'route' => $route
        );
       return RolePermission::where($result)->count();
   
  
}


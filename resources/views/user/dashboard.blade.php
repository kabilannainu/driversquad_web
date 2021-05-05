@extends('user.layout.base')

@section('title', 'Dashboard ')

@section('content')

<div class="col-md-9">
    <div class="dash-content">
        <div class="row no-margin">
            <div class="col-md-12">
                <h4 class="page-title">@lang('user.ride.ride_now')</h4>
            </div>
        </div>
        @include('common.notify') 
        <div class="row no-margin">
            <div class="col-md-6"> 
                <form id="ride_form" method="GET" action="{{url('confirm/ride')}}"  onkeypress="return disableEnterKey(event);">
                   <div class="input-group dash-form"> 
                    <ul class="nav nav-tabs" style="border-bottom: 1px solid #fff !important;padding-left: 35px !important; ">
                      <li class="nav-itema active normal_inactive">
                        <a class="$('.nav-link')" onclick="select_service_required('normal');">Daily Rides</a>
                      </li>
                      <li class="nav-item rental_inactive">
                        <a class="nav-link " onclick="select_service_required('rental');">Rental</a>
                      </li>
                      <li class="nav-item outstation_inactive">
                        <a class="nav-link" onclick="select_service_required('outstation');">Outstation</a>
                      </li> 
                    </ul> <br>
                      <input type="hidden" name="service_required" value="normal">
                    </div> 
                    <div class="input-group dash-form pickup_addr">
                        <input type="text" class="form-control" id="origin-input" name="s_address"  placeholder="Enter pickup location">
                    </div>

                    <div class="input-group dash-form">
                        <input type="text" class="form-control" id="destination-input" name="d_address"  placeholder="Enter drop location" >
                    </div>

                    <input type="hidden" name="s_latitude" id="origin_latitude">
                    <input type="hidden" name="s_longitude" id="origin_longitude">
                    <input type="hidden" name="d_latitude" id="destination_latitude">
                    <input type="hidden" name="d_longitude" id="destination_longitude">
                    <input type="hidden" name="current_longitude" id="long">
                    <input type="hidden" name="current_latitude" id="lat">
                    <input type="hidden" name="check_status" id="check_status">

                    <div class="car-detail">

                        @foreach($services as $service)
                        <div class="car-radio">
                            <input type="radio" 
                                name="service_type"
                                value="{{$service->id}}"
                                id="service_{{$service->id}}"
                                @if ($loop->first) @endif>
                                
                            <label for="service_{{$service->id}}">
                                <div class="car-radio-inner">
                                    <div class="img"><img src="{{image($service->image)}}"></div>
                                    <div class="name"><span>{{$service->name}}<p style="font-size: 10px;">(1-{{$service->capacity}})</p></span>
                                    </div>
                                </div>
                            </label>
                        </div>
                        @endforeach


                    </div>

                   <!-- 

                     <div class="input-group dash-form">
                        <select name="service_required" class="form-control" id="service_required">

                            <option value="none"> Select </option>

                            <option value="rental"> Rental </option>
                            <option value="outstation"> Outstation </option>

                        </select>
                    </div> -->

                   <!--  <div class="input-group dash-form" id="hours">
                        <input type="number" class="form-control" id="rental_hours" name="rental_hours"  placeholder="(Rental Hours)How many hours?" >
                    </div>

                    <div class="input-group dash-form" id="day">
                        <input type="number" class="form-control" name="day"  placeholder="How Many Days">
                    </div> -->

        <div class="rental_package" id="hours">
          <select class="form-control" name="rental_hours" id="rental_hours">
            <option value="">Choose Rental Package</option>  
          </select>
        
               </div>
              
               <div class="outstation" id="day">
                    <div class="row">
                       <div class="col-md-6">
                         <div class="col-md-3">
                             <input type="radio" style="width: 20px;" class="form-control" name="day" value="oneway"> 
                         </div>
                         <div class="col-md-3">
                             <label>Oneway Trip</label> 
                         </div>
                         </div>
                       <div class="col-md-6">
                           <div class="col-md-3">
                           <input type="radio" style="width: 20px;" class="form-control" name="day" value="round">
                           </div>
                           <div class="col-md-3">
                             <label>Round Trip</label> 
                            </div>
                       </div>
                   </div>
                   <div class="row">
                       <div class="col-md-6" id="leave">
                          <input type="text"  class="form-control time" name="leave" value=""> 
                          <label>Leave Date</label> 
                        </div>
                       <div class="col-md-6" id="return">
                          <input type="text"  class="form-control time" name="return" value="">
                          <label>Return Date</label> 
                       </div>
                   </div>
                </div>

                    <button type="button"  onclick="return check_fare()"  class="full-primary-btn fare-btn otherservice " data-value='{!!Setting::get('service_range')!!}'>@lang('user.ride.ride_now')</button>

                    <button type="submit" class="full-primary-btn fare-btn onlyrental" >@lang('user.ride.ride_now')</button>

                </form>
            </div>

            <div class="col-md-6">
                <div class="map-responsive">
                    <div id="map" style="width: 100%; height: 450px;"></div>
                </div> 
            </div>
        </div>
    </div>
</div> 
@endsection

@section('scripts')   
<script type="text/javascript">
    function select_service_required(service_required) {
      $('input[name=service_required]').val(service_required);
        if(service_required == 'rental')
        {
          $('.normal_inactive ').removeClass('active');
          $('.outstation_inactive ').removeClass('active');
          $('.rental_inactive ').addClass('active');

          $('.otherservice').hide(); 
          $('.onlyrental').show(); 
          $('#destination-input').hide(); 
          $('#hours').show();
          $('#day').hide();
          $('input[name=service_type]').change(function(){
            var check = $('input[name=service_required]').val(); 
            if(check == 'rental'){ 
              
              var service_type = $(this).val();
              $.ajax({
                url: "{{ url('/rental_package') }}",
                dataType: "json",
                method : "GET",
                data : {service_type:service_type},
                success: function(data){
                  if(data.message == 'success')
                  {
                    $('#rental_hours').html('<option value="">Choose Rental Package</option>'); 
                     $.each(data.packages, function(index, value) {  
                          $('#rental_hours').append('<option value="'+value.id+'">'+value.hour+'Hrs - '+value.km+'Kms - '+value.price+'Rs</option>'); 
                          });
                  }
                  else if(data.message == 'failed')
                  {  
                     $('#rental_hours').html('<option value="">Choose Rental Package</option>');
                  }
                }
              });
            }
          });
        }
        else if(service_required == 'outstation')
        {
          $('.normal_inactive ').removeClass('active');
          $('.outstation_inactive ').addClass('active');
          $('.rental_inactive ').removeClass('active');

          $('.otherservice').show(); 
          $('.onlyrental').hide();
          $('#destination-input').show();
          $('#hours').hide();
          $('#day').show();
        }
        else if(service_required == 'normal')
        { 
          $('.normal_inactive ').addClass('active');
          $('.outstation_inactive ').removeClass('active');
          $('.rental_inactive ').removeClass('active');

          $('.otherservice').show(); 
          $('.onlyrental').hide();
          $('#hours').hide();
          $('#destination-input').show();
          $('#day').hide(); 
        }
    }
    function check_fare()
    {

        var form = $('#ride_form').serialize();
          $.ajax({
            url: "{{ url('/fare') }}",
            dataType: "json",
            method : "POST",
            data : form,
            success: function(data){ 

                if(data.city_limits==1)
                {
                    if(data.required_service==0){
                        swal({
                            title: "Alert",
                            text: "Your destination exceeds our city limits, Please try our rental or outstation service for this trip two way charges will be applied",
                            type: "warning",
                            showCancelButton: true,
                            confirmButtonClass: "btn-danger",
                            confirmButtonText: "OK",
                            closeOnConfirm: false
                        }),

                        $("#service_required").show();

                        $.each(data.rental_hour_package, function(index, value) {  
                          $('#rental_hours').append('<option value="'+value.id+'">'+value.hour+'Hrs - '+value.km+'Kms - '+value.price+'Rs</option>'); 
                        });

                            
                    }else{
                        $( "#ride_form" ).submit();
                      }
                }
                else
                {
                    $( "#ride_form" ).submit();
                }

            }
         });


    }

    var current_latitude = 3.1390;
    var current_longitude = 101.6869;

    if( navigator.geolocation ) {
       navigator.geolocation.getCurrentPosition( success, fail );
    } else {
        console.log('Sorry, your browser does not support geolocation services');
        initMap();
    }

    function success(position)
    {
        document.getElementById('long').value = position.coords.longitude;
        document.getElementById('lat').value = position.coords.latitude

        if(position.coords.longitude != "" && position.coords.latitude != ""){
            current_longitude = position.coords.longitude;
            current_latitude = position.coords.latitude;
        }
        initMap();
    }

    function fail()
    {
        // Could not obtain location
        console.log('unable to get your location');
        initMap();
    }
</script> 

<script type="text/javascript" src="{{ asset('asset/js/map.js') }}"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ Setting::get('map_key') }}&libraries=places&callback=initMap" async defer></script>

<script type="text/javascript">
    function disableEnterKey(e)
    {
        var key;
        if(window.e)
            key = window.e.keyCode; // IE
        else
            key = e.which; // Firefox

        if(key == 13)
            return e.preventDefault();
    }
</script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#hours").hide();
        $("#day").hide();
         $('#leave').hide();
        $('#return').hide();
        $("#service_required").hide();
        $(".onlyrental").hide();
        

//         $('input[name=service_type]').change(function(){

//     var id =     $('input[name=service_type]:checked').val();
    
//      $.ajax({url: "{{ url('hour') }}/"+id,dataType: "json",
//                    success: function(data){
//                     //console.log(data['calculator']);

//                        if (data['calculator'] == 'DISTANCEHOUR')
//                        $("#hours").show();  
//                        else
//                        $("#hours").hide(); 
//                   }});
// });



  });  
</script>
<script type="text/javascript">
   //  $( "#service_required" ).change(function() 
   //  {
   //     var stateID = $(this).val();
   // //    alert(stateID);
   //     if (stateID == 'rental'){
   //         $("#hours").show();  
   //         $("#day").hide();
   //         $("#destination-input").hide();


   //         }else if(stateID == 'outstation'){
   //         $("#hours").hide(); 
   //         $("#day").show();
   //         $("#destination-input").show();
   //     }
   //  }); 

        $("input[name=day]:radio").click(function() {
            if ($('input[name=day]:checked').val() == "oneway") {
                $('#leave').show();
                $('#return').hide();

            } else if ($('input[name=day]:checked').val() == "round") {
                $('#leave').show();
                $('#return').show();

            }
        });
</script>

<script type="text/javascript" src="{{asset('main/vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js')}}"></script>
<script type="text/javascript" src="{{asset('main/vendor/moment/moment.js')}}"></script>
<script type="text/javascript" src="{{asset('main/vendor/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
<script type="text/javascript" src="{{asset('main/assets/dt/js/bootstrap-material-datetimepicker.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function() {

        $('.time').bootstrapMaterialDatePicker({
            format: 'YYYY-MM-DD HH:mm:ss'
        });


    });
</script>   

@endsection


 <!-- { (this.state.hide1==true) ? <a><span className="fa fa-info-circle" aria-hidden="true" onClick={this.onClickplus1}></span></a> : <a><span className="fa fa-info-circle" aria-hidden="true" onClick={this.onClick1}></span></a> } -->



  <!-- { (this.state.hide2==true) ? <a><span className="fa fa-info-circle" aria-hidden="true" onClick={this.onClickplus2}></span></a> : <a><span className="fa fa-info-circle" aria-hidden="true" onClick={this.onClick2}></span></a> } -->
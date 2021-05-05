@extends('user.layout.app')

@section('content')
    <div class="banner row no-margin" style="background-image: url('{{ asset('asset/img/banner-bg.jpg') }}');">
        <div class="banner-overlay"></div>
        <div class="container">
            <div class="col-md-8">
                <h2 class="banner-head"><span class="strong">Always the ride you want</span><br>The best way to get wherever you’re going</h2>
            </div>
            <div class="col-md-4">
                <div class="banner-form">
                    <div class="row no-margin fields">
                        <div class="left">
                            <img src="{{asset('asset/img/ride-form-icon.png')}}">
                        </div>
                        <div class="right">
                            <a href="{{url('login')}}">
                                <h3>Ride with {{Setting::get('site_title','Tranxit')}}</h3>
                                <h5>SIGN IN <i class="fa fa-chevron-right"></i></h5>
                            </a>
                        </div>
                    </div>
                    <div class="row no-margin fields">
                        <div class="left">
                            <img src="{{asset('asset/img/ride-form-icon.png')}}">
                        </div>
                        <div class="right">
                            <a href="{{url('register')}}">
                                <h3>Sign up to Ride</h3>
                                <h5>SIGN UP <i class="fa fa-chevron-right"></i></h5>
                            </a>
                        </div>
                    </div>

                    <p class="note-or">Or <a href="{{url('provider/login')}}">sign in</a> with your driver account.</p>
                    
                </div>
            </div>
        </div>
    </div>

    <div class="row white-section no-margin">
        <div class="container">
            
            <div class="col-md-4 content-block small">
                <h2>Tap the app, get a ride</h2>
                <div class="title-divider"></div>
                <p>{{ Setting::get('site_title', 'Tranxit')  }} is the smartest way to get around. One tap and a car comes directly to you. Your driver knows exactly where to go. And you can pay with either cash or card.</p>
            </div>

            <div class="col-md-4 content-block small">
                <h2>Choose how to pay</h2>
                <div class="title-divider"></div>
                <p>When you arrive at your destination, either pay with cash or have your card automatically charged. With {{ Setting::get('site_title', 'Tranxit') }}, the choice is yours.</p>
            </div>

            <div class="col-md-4 content-block small">
                <h2>You rate, we listen</h2>
                <div class="title-divider"></div>
                <p>Rate your driver and provide anonymous feedback about your trip. Your input helps us make every ride a 5-star experience.</p>
            </div>


        </div>
    </div>

    <div class="row gray-section no-margin">
        <div class="container">                
            <h2 class="sub-head"><span class="strong">There’s a ride for every price</span><br>And any occasion</h2>

            <div class="car-tab">
                <ul class="nav nav-tabs">
                  <li class="active"><a data-toggle="tab" href="#economy">ECONOMY</a></li>
                  <li><a data-toggle="tab" href="#premium">PREMIUM</a></li>
                  <li><a data-toggle="tab" href="#accessibility">ACCESSIBILITY</a></li>
                  <li><a data-toggle="tab" href="#carpool">CARPOOL</a></li>
                </ul>

                <div class="tab-content">
                  <div id="economy" class="tab-pane fade in active">
                    <div class="car-slide">
                        <img src="{{asset('/asset/img/car-slide1.png')}}">
                    </div>
                  </div>
                  <div id="premium" class="tab-pane fade">
                    <div class="car-slide">
                        <img src="{{asset('/asset/img/car-slide2.png')}}">
                    </div>
                  </div>
                  <div id="accessibility" class="tab-pane fade">
                    <div class="car-slide">
                        <img src="{{asset('/asset/img/car-slide3.png')}}">
                    </div>
                  </div>

                  <div id="carpool" class="tab-pane fade">
                    <div class="car-slide">
                        <img src="{{asset('/asset/img/car-slide4.png')}}">
                    </div>
                  </div>


                </div>
            </div>
        </div>
    </div>


    <div class="row white-section no-margin" id="estimate-fare">
        <div class="container">
            
            <div class="col-md-6 content-block">
                <h2 class="two-title"><span class="light">Pricing</span><br><span class="strong">Get a fare estimate</span></h2>
                <div class="title-divider"></div>
                <form method="post" id="idForm" onsubmit="return">

                    {{ csrf_field() }}
                <div class="input-group fare-form">
                    <input type="text" class="form-control" required  placeholder="Enter pickup location" id="origin-input" name="s_address">                               
                </div>

                <div class="input-group fare-form no-border-right">
                    <input type="text" class="form-control" required  placeholder="Enter drop location" id="destination-input" name="d_address">
                   
                </div>

                 

                 <div class="car-detail">

                    @foreach($services as $service)
                    <div class="car-radio">
                        <input type="radio" 
                            name="service_type"
                            value="{{$service->id}}"
                            id="service_{{$service->id}}"
                            @if ($loop->first) @endif>
                            
                        <label onclick="service_add('{{$service->id}}')" for="service_{{$service->id}}">
                            <div class="car-radio-inner">
                                <div class="img"><img src="{{image($service->image)}}"></div>
                                <div class="name"><span>{{$service->name}}<p style="font-size: 10px;">(1-{{$service->capacity}})</p></span>
                                </div>
                            </div>
                        </label>
                    </div>
                    @endforeach


                </div>
            

                 <input type="hidden" id="service_type" name="service_type">
                 <input type="hidden" name="s_latitude" id="origin_latitude">
                 <input type="hidden" name="s_longitude" id="origin_longitude">
                 <input type="hidden" name="d_latitude" id="destination_latitude">
                    <input type="hidden" name="d_longitude" id="destination_longitude">
                    <input type="hidden" name="current_longitude" id="long">
                    <input type="hidden" name="current_latitude" id="lat">

                 <span class="error_ride" id="error1"></span>   
                 <span class="error_ride" id="error2"></span> 
                 <span class="error_ride" id="error3"></span>   

              
                 <button  type="submit" id="btnSubmit" class="full-primary-btn fare-btn" style="text-align: center; margin-top: 10px;">Get Estimate Fare</button>

                <div id="div1" class="full-primary-btn fare-btn"  style="text-align: center; display: none; float: left;"></div>
                        
                <div id="div2" class="full-primary-btn fare-btn" style="text-align: center; display: none; float: left;"></div>

                </form>
            </div>

            <div class="col-md-6 map-right">
                <div class="map-responsive" style="padding-bottom: 73.25%;"">
                    <div id="map" style="width: 100%; height: 700px;"></div>
                </div>                                
            </div>
            
        </div>
    </div>          

    <div class="row gray-section no-margin">
        <div class="container">                
            <div class="col-md-6 content-block">
                <h2>Safety Putting people first</h2>
                <div class="title-divider"></div>
                <p>Whether riding in the backseat or driving up front, every part of the {{ Setting::get('site_title', 'Tranxit') }} experience has been designed around your safety and security.</p>
                <a class="content-more" href="#">HOW WE KEEP YOU SAFE <i class="fa fa-chevron-right"></i></a>
            </div>
            <div class="col-md-6 img-block text-center"> 
                <img src="{{asset('asset/img/seat-belt.jpg')}}">
            </div>
        </div>
    </div>


    <div class="row find-city no-margin" id="city-view">
        <div class="container">
            <h2>{{Setting::get('site_title','Tranxit')}} is in your city</h2>
           <!--  <form>
                <div class="input-group find-form">
                    <input type="text" class="form-control"  placeholder="Search" >
                    <span class="input-group-addon">
                        <button type="submit">
                            <i class="fa fa-arrow-right"></i>
                        </button>  
                    </span>
                </div>
            </form> -->
        </div>
    </div>
    <?php $footer = asset('asset/img/footer-city.png'); ?>
    <div class="footer-city row no-margin" style="background-image: url({{$footer}});"></div>
@endsection


@section('scripts')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript">

$(document).ready(function () {

    $("#btnSubmit").click(function (event) {

    $('.error_ride').html('');

    var error = 0;

    var origin_input = $('#origin-input').val();
    var destination_input = $('#destination-input').val();
    var service = $('#service_type').val();

    if(origin_input=='')
    {
        $('#error1').html('Enter Pickup Location');
        error = 1;
    }
    if(destination_input=='')
    {
        $('#error2').html('Enter Drop Location');
        error = 1;
    }
     if(service=='')
    {
        $('#error3').html('Select Service Type');
        error = 1;
    }

    event.preventDefault();

    if(error==0)
    {

            $.ajax({
               type: "POST",
               url: "{{url('/fare')}}",
               data: $("#idForm").serialize(),

               success: function(data)
               { 
                   $("#div1").show();
                   $("#div2").show();
                   $("#btnSubmit").hide();
                   var currency="{{Setting::get('currency')}}";
                   $("#div1").html("Estimated Fare - "+data.estimated_fare+currency+"");
                   $("#div2").html("Distance - "+data.distance+"KM");


               }
             });
    }


 

   });

});


function service_add(val)
{
    $('#service_type').val(val);
}

</script>


@endsection




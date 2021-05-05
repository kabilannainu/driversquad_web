@extends('provider.layout.auth')

@section('content')
<div class="col-md-12">
    <a class="log-blk-btn" href="{{ url('/provider/login') }}">@lang('provider.signup.already_register')</a>
    <h3>@lang('provider.signup.sign_up')</h3>
</div>

<div class="col-md-12">
    <form class="form-horizontal" role="form" enctype="multipart/form-data" method="POST" action="{{ url('/provider/register') }}">

        <div id="first_step">
            <div class="col-md-4">
                <input value="+60" type="text" placeholder="+60" id="country_code" name="country_code" />
            </div> 
            
            <div class="col-md-8">
                <input type="text" autofocus id="phone_number" class="form-control" placeholder="@lang('provider.signup.enter_phone')" name="phone_number" value="{{ old('phone_number') }}" />
            </div>
            <div class="col-md-12 exist-msg" style="display: none;">
                <span class="help-block">
                        <strong>Mobile number already exists!!</strong>
                </span>
            </div>

            <div class="col-md-8">
                @if ($errors->has('phone_number'))
                    <span class="help-block">
                        <strong>{{ $errors->first('phone_number') }}</strong>
                    </span>
                @endif
            </div>

            <!-- <div class="col-md-12" style="padding-bottom: 10px;" id="mobile_verfication">
                <input type="button" class="log-teal-btn small" onclick="smsLogin();" value="Verify Phone Number"/>
            </div> -->
        </div>

        {{ csrf_field() }}

        <!-- <div id="second_step" style="display: none;"> -->
        <div id="second_step">

            <input id="name" type="text" class="form-control" name="first_name" value="{{ old('first_name') }}" placeholder="@lang('provider.profile.first_name')" autofocus>

            @if ($errors->has('first_name'))
                <span class="help-block">
                    <strong>{{ $errors->first('first_name') }}</strong>
                </span>
            @endif

            <input id="name" type="text" class="form-control" name="last_name" value="{{ old('last_name') }}" placeholder="@lang('provider.profile.last_name')">

            @if ($errors->has('last_name'))
                <span class="help-block">
                    <strong>{{ $errors->first('last_name') }}</strong>
                </span>
            @endif

            <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="@lang('provider.signup.email_address')">

            @if ($errors->has('email'))
                <span class="help-block">
                    <strong>{{ $errors->first('email') }}</strong>
                </span>
            @endif

            <label class="checkbox-inline"><input type="radio" name="gender" value="MALE" required="">@lang('provider.signup.male')</label>
            <label class="checkbox-inline"><input type="radio" name="gender" value="FEMALE">@lang('provider.signup.female')</label>
            @if ($errors->has('gender'))
                <span class="help-block">
                    <strong>{{ $errors->first('gender') }}</strong>
                </span>
            @endif     

            <h5>Child seat available:</h5>
            <label class="checkbox-inline"><input type="radio" name="child_seat" value="1" required="">Yes</label>
            <label class="checkbox-inline"><input type="radio" name="child_seat" value="0">No</label>

            <h5>Pet Allowed:</h5>
            <label class="checkbox-inline"><input type="radio" name="pet_allowed" value="1" required="">Yes</label>
            <label class="checkbox-inline"><input type="radio" name="pet_allowed" value="0">No</label>            
            
            <button type="submit" class="log-teal-btn">
                @lang('provider.signup.register')
            </button>

        </div>
    </form>
</div>
@endsection


@section('scripts')
<script type="text/javascript">
    $('.checkbox-inline').on('change', function() {
        $('.checkbox-inline').not(this).prop('checked', false);  
    });
</script>
<script src="https://sdk.accountkit.com/en_US/sdk.js"></script>
<script>
  // initialize Account Kit with CSRF protection
  AccountKit_OnInteractive = function(){
    AccountKit.init(
      {
        appId: {{Setting::get('fb_app_id')}}, 
        state:"state", 
        version: "{{Setting::get('fb_app_version')}}",
        fbAppEventsEnabled:true
      }
    );
  };

  // login callback
  function loginCallback(response) {
    if (response.status === "PARTIALLY_AUTHENTICATED") {
      var code = response.code;
      var csrf = response.state;
      // Send code to server to exchange for access token
      $('#mobile_verfication').html("<p class='helper'> * Phone Number Verified </p>");
      $('#phone_number').attr('readonly',true);
      $('#country_code').attr('readonly',true);
      $('#second_step').fadeIn(400);

      $.post("{{route('account.kit')}}",{ code : code }, function(data){
        $('#phone_number').val(data.phone.national_number);
        $('#country_code').val('+'+data.phone.country_prefix);
      });

    }
    else if (response.status === "NOT_AUTHENTICATED") {
      // handle authentication failure
      $('#mobile_verfication').html("<p class='helper'> * Authentication Failed </p>");
    }
    else if (response.status === "BAD_PARAMS") {
      // handle bad parameters
    }
  }

  // phone form submission handler
  function smsLogin() {
    var countryCode = document.getElementById("country_code").value;
    var phoneNumber = document.getElementById("phone_number").value;

    $('#mobile_verfication').html("<p class='helper'> Please Wait... </p>");
    $('#phone_number').attr('readonly',true);
    $('#country_code').attr('readonly',true);

    AccountKit.login(
      'PHONE', 
      {countryCode: countryCode, phoneNumber: phoneNumber}, // will use default values if not specified
      loginCallback
    );
  }

  // phone form submission handler
  function smsLogin() {

        $('.exist-msg').hide();

        var countryCode = document.getElementById("country_code").value;
        var phoneNumber = document.getElementById("phone_number").value;

        $.post("{{route('provider.check.mobile')}}",{ username : phoneNumber }, function(data){
              
            if(data.status == false)
            {  
                $('#mobile_verfication').html("<p class='helper'> Please Wait... </p>");
                $('#phone_number').attr('readonly',true);
                $('#country_code').attr('readonly',true);

                AccountKit.login(
                  'PHONE', 
                  {countryCode: countryCode, phoneNumber: phoneNumber}, // will use default values if not specified
                  loginCallback
                );
            }
            else
            {
                $('.exist-msg').show();
            }

        });
  
  }

</script>

@endsection
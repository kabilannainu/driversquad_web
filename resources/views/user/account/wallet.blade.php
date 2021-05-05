@extends('user.layout.base')

@section('title', 'Wallet ')

@section('content')

<div class="col-md-9">
    <div class="dash-content">
        <div class="row no-margin">
            <div class="col-md-12">
                <h4 class="page-title">@lang('user.my_wallet')</h4>
            </div>
        </div>
        @include('common.notify')

        <div class="row no-margin">
            <form id="myCCForm" action="#" method="post" >
                 {{ csrf_field() }}
                  <div class="col-md-6">
                     
                    <div class="wallet">
                        <h4 class="amount">
                            <span class="price">{{currency(Auth::user()->wallet_balance)}}</span>
                            <span class="txt">@lang('user.in_your_wallet')</span>
                        </h4>
                    </div>                                                               

                </div>
                <div class="col-md-6">
                    
                    <h6><strong>@lang('user.add_money')</strong></h6>


                    <div class="input-group full-input">
                        <input type="number" class="form-control" id="amount" name="amount" placeholder="Enter Amount" >
                    </div>
                    <br>

                  <!--   @if($cards->count() > 0)
                        <select class="form-control" name="card_id">
                        {{--<option value='CC_AVENUE'>CC AVENUE</option>--}}
	                      @foreach($cards as $card)
	                        <option @if($card->is_default == 1) selected @endif value="{{$card->card_id}}">{{$card->brand}} **** **** **** {{$card->last_four}}</option>
	                      @endforeach
                        </select>
                    @else
                        <select class="form-control" name="card_id">
                        {{--<option value='CC_AVENUE'>CC AVENUE</option>--}}
                        </select>
                    	<p>Please <a href="{{url('payment')}}" class="add-card-btn">add card</a> to continue</p>
                    @endif -->
                    

                    <input type="hidden" class="form-control" id="email" name="email" value={{Auth::user()->email}}>
                    <input type="hidden" class="form-control" id="name" name="name" value={{Auth::user()->first_name}}>
                  
                    <button type="button" id="rzp-button1" onclick="call()" class="full-primary-btn fare-btn">@lang('user.add_money')</button> 


                </div>
                </form>
                <body onload="loadingAjax('myDiv');">
                    <div id="myDiv">
                        <img id="loading-image" src="{{asset('asset/img/ajax-loader.gif')}}" style="display:none;"/>
                    </div>
                </body>
        </div>

    </div>
</div>

@endsection

@section('scripts')

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>

<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>



function call() {

   var amount = $('#amount').val();
   var email = $('#email').val();
   var name = $('#name').val();

  
    

    var options = {
    "key": "<?php echo Setting::get('rzp_key'); ?>",
    "amount": amount*100,
    "name": "Merchant Name",
    "description": "Purchase Description",
    "image": "{{ asset('asset/img/site_logo.png') }}",
    "handler": function (response){

        $.ajax({
            type: "POST",
            url: "{{url('rzp/success')}}",
            data:{ payment_id: response.razorpay_payment_id },           
            dataType: "json",
            beforeSend: function() {
              $("#loading-image").show();
            },
            success: function(data) {
                $("#loading-image").hide();
                swal({
                        title: "Success",
                        text: data.message,
                        type: "success",
                        confirmButtonClass: "btn-success",
                    },
                    function(){
                         window.location.href="{{url('/wallet')}}";
                    });

            }
        });    

    },
    "prefill": {
        "name":name ,
        "email": email
    },
    "notes": {
        "address": ""
    },
    "theme": {
        "color": "#e86609"
    }
};
var rzp1 = new Razorpay(options);
rzp1.open();


   
}




</script>


@endscripts
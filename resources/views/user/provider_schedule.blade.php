@extends('user.layout.base')

@section('title', 'Dashboard ')

@section('content')
<style>
.rate_space{
  margin-top:50px;
  margin-left:30px;
}
.exp_space{
  margin-top:40px;
}
.des_space{
  margin:35px 0 0 18px;
}
.qualification_space
{
  margin-top:-40px; 
  margin-left:170px
}
</style>

<div class="col-md-9">
    <div class="dash-content">
        <div class="row no-margin">
            <div class="col-md-12">
                <h4 class="page-title">Detailed view</h4>
            </div>
        </div>
        <form action="{{url('create/ride')}}" method="POST" id="create_ride" onkeypress="return disableEnterKey(event);">
                 {{ csrf_field() }}
                  @foreach($providers as $provider)                  
                  <input type="hidden" name="service_type" value="{{isset($provider->service->service_type) ? $provider->service->service_type->id : '' }}">
                   <input type="hidden" name="broadcast" value="1">

                   <input type="hidden" name="payment_mode" id="payment_mode">

                <input type="hidden" name="provider_id" value="{{$provider->id}}">
                 <div class="col-md-12">
                  <div class="col-md-2">
                  <img src="{{img($provider->avatar)}}" height="70" width="70">
                  <div class="exp_space">
                   <b>Experience</b></br>
                    {{$provider->experience ? : 0}}years
                   </div> 
                </div>
                <div class="col-md-10">
                  <div><b>
                  {{$provider->first_name . $provider->last_name}}
                  </b>
                  </div>
                  <div>
                    {{isset($provider->service->service_type->name) ? $provider->service->service_type->name : '' }}
                  </div>
                  <div>
                  @for($i=0; $i<5; ++$i)
                  @if($i < $provider->rating)
                  <i class="fa fa-star" aria-hidden="true" style="color: #ffe000!important;"></i>
                  @else
                  <i class="fa fa-star-o" aria-hidden="true" style="color: #ffe000!important;"></i>
                  @endif
                  @endfor
                  </div>
                  <div class="rate_space">
                    <b>Rate</b><br>
                    {{ Setting::get('currency','$') .(isset($provider->service->provider_price) && $provider->service->provider_price != '' ? $provider->service->provider_price : (isset($provider->service->service_type->fixed) ? $provider->service->service_type->fixed : '0')).'/min'}}
                  </div>
                </div>
                <div class="clearfix"></div>
                <div class="des_space">
                  <b>Description</b><br>
                 {{$provider->description}}
               </div>
               <div class="qualification_space">
                  <b>Qualification</b><br>
                 {{$provider->qualification}}
               </div>
               <a href="{{img($provider->certificate)}}" target="_blank"><img src="{{img($provider->certificate)}}" height="70" width="70"></a>
                </div>
          
                 @endforeach
                <button type="button" class="half-secondary-btn fare-btn" data-toggle="modal" data-target="#schedule_modal">Schedule</button>

        </form>        
    </div>
    <!-- Schedule Modal -->
<div id="schedule_modal" class="modal fade schedule-modal" role="dialog">
  <div class="modal-dialog">

    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title">Schedule BroadCast</h4>
      </div>
      <form>
      <div class="modal-body">
        <label>Date</label>
        <input value="{{date('m/d/Y')}}" type="text" id="datepicker" placeholder="Date" name="schedule_date">
        <label>Time</label>
        <input value="{{date('H:i')}}" type="text" id="timepicker" placeholder="Time" name="schedule_time">
        <label>Payment Mode</label>
        <select class="form-control" onchange="card(this.value);">
          <option value="CASH">CASH</option>
           @if(Setting::get('razorpay') == 1)
           <option value="razorpay">Razorpay</option>
           @endif
         
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" id="schedule_button" class="btn btn-default" data-dismiss="modal">Schedule</button>
      </div>

      </form>
    </div>

  </div>
</div>

</div>
@endsection
@section('scripts')
    <script type="text/javascript">
        $(document).ready(function(){
            $('#schedule_button').click(function(){
                $("#datepicker").clone().attr('type','hidden').appendTo($('#create_ride'));
                $("#timepicker").clone().attr('type','hidden').appendTo($('#create_ride'));
                $("#payment_mode").clone().attr('type','hidden').appendTo($('#create_ride'));
                document.getElementById('create_ride').submit();
            });
        });
    </script>
    <script type="text/javascript">
        var date = new Date();
        date.setDate(date.getDate()-1);
        $('#datepicker').datepicker({  
            startDate: date
        });
        $('#timepicker').timepicker({showMeridian : false});
    </script>

    <script type="text/javascript">
        function card(value){
            if(value == 'CASH'){
                $('#payment_mode').val('CASH');
            }else{
                $('#payment_mode').val('razorpay');
            }
        }
    </script>
@endsection

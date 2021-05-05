@extends('admin.layout.base')

@section('title', 'Update Service Type ')

@section('content')


<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.service.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

            <h5 style="margin-bottom: 2em;">@lang('admin.service.Update_User')</h5>

            <form novalidate class="form-horizontal" action="{{route('admin.service.update', $service->id )}}" method="POST" enctype="multipart/form-data" role="form">
                {{csrf_field()}}


                <input type="hidden" name="_method" value="PATCH">
                <div class="form-group row">
                    <label for="name" class="col-xs-2 col-form-label">@lang('admin.service.Service_Name')</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $service->name }}" name="name" required id="name" placeholder="Service Name">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="provider_name" class="col-xs-2 col-form-label">@lang('admin.service.Provider_Name')</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $service->provider_name }}" name="provider_name" required id="provider_name" placeholder="Provider Name">
                    </div>
                </div>

                <div class="form-group row">
                    
                    <label for="image" class="col-xs-2 col-form-label">@lang('admin.picture')</label>
                    <div class="col-xs-10">
                        @if(isset($service->image))
                        <img style="height: 90px; margin-bottom: 15px; border-radius:2em;" src="{{ $service->image }}">
                        @endif
                        <input type="file" accept="image/*" name="image" class="dropify form-control-file" id="image" aria-describedby="fileHelp">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="calculator" class="col-xs-2 col-form-label">@lang('admin.service.Pricing_Logic')</label>
                    <div class="col-xs-10">
                        <select class="form-control" id="calculator" name="calculator">
                            <option value="MIN" @if($service->calculator =='MIN') selected @endif>@lang('servicetypes.MIN')</option>
                            <option value="HOUR" @if($service->calculator =='HOUR') selected @endif>@lang('servicetypes.HOUR')</option>
                            <option value="DISTANCE" @if($service->calculator =='DISTANCE') selected @endif>@lang('servicetypes.DISTANCE')</option>
                            <option value="DISTANCEMIN" @if($service->calculator =='DISTANCEMIN') selected @endif>@lang('servicetypes.DISTANCEMIN')</option>
                            <option value="DISTANCEHOUR" @if($service->calculator =='DISTANCEHOUR') selected @endif>@lang('servicetypes.DISTANCEHOUR')</option>
                        </select>
                    </div>
                </div>

 

                <div class="form-group row">
                    <label for="fixed" class="col-xs-2 col-form-label">@lang('admin.service.Base_Price') ({{ currency('') }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $service->fixed }}" name="fixed" required id="fixed" placeholder="Base Price">
                    </div>
                </div> 
                
                {{--<!-- Base distance -->
                <div class="form-group row">
                    <label for="distance" class="col-xs-2 col-form-label">@lang('admin.service.Base_Distance') ({{ distance() }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $service->distance }}" name="distance" required id="distance" placeholder="Base Distance">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="price" class="col-xs-2 col-form-label">@lang('admin.service.unit') ({{ distance() }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $service->price }}" name="price" required id="price" placeholder="Unit Distance Price">
                    </div>
                </div>
                <div class="form-group row">
                    <label for="minute" class="col-xs-2 col-form-label">@lang('admin.service.unit_time') ({{ currency() }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $service->minute }}" name="minute" required id="minute" placeholder="Unit Time Pricing">
                    </div>
                </div>
                 <!-- Set Hour Price -->
                 @if($service->calculator =='DISTANCEHOUR')
               
                  <div class="form-group row" >
                    <label for="fixed" class="col-xs-2 col-form-label">@lang('admin.service.hourly_Price') ({{ currency('') }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $service->hour }}" name="hour"  id="hourly_price" placeholder="Set Hour Price">
                    </div>
                </div>
                @else
                <div class="form-group row" >
                    <label for="fixed" class="col-xs-2 col-form-label">@lang('admin.service.hourly_Price') ({{ currency('') }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="" name="hour"  id="hourly_price" placeholder="Set Hour Price (Only for DISTANCEHOUR)">
                    </div>
                </div>
                @endif --}}

                 <div class="form-group row">
                    <label for="capacity" class="col-xs-2 col-form-label">@lang('admin.service.Seat_Capacity')</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" value="{{ $service->capacity }}" name="capacity" required id="capacity" placeholder="Seat Capacity">
                    </div>
                </div>



                 <div class="form-group row">
                     <label for="description" class="col-xs-12 col-form-label" style="color: black;font-size: 25px;">Outstation Fare</label>
                </div>

                 <div class="form-group row" id="outstation_price">
                    <label for="fixed" class="col-xs-2 col-form-label">@lang('admin.service.outstation_per_km') ({{ distance() }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text"  name="outstation_km"  value="{{$service->outstation_km}}" id="outstation_price" min="0" placeholder="Oneway Km Price">
                    </div>
                </div>
                <div class="form-group row" id="roundtrip_price">
                    <label for="fixed" class="col-xs-2 col-form-label">@lang('admin.service.roundtrip_per_km') ({{ distance() }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{$service->roundtrip_km}}" name="roundtrip_km"  id="roundtrip_price" min="0" placeholder="Roundtrip Km Price">
                    </div>
                </div>


                 <div class="form-group row" id="outstation_driver">
                    <label for="fixed" class="col-xs-2 col-form-label">@lang('admin.service.outstation_driverbata') ({{ currency() }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text"  name="outstation_driver"  value="{{$service->outstation_driver}}" id="outstation_driver" min="0" placeholder="Driver Bata per day">
                    </div>
                </div>

                <div class="form-group row">
                     <label for="description" class="col-xs-12 col-form-label" style="color: black;font-size: 25px;">Rental Fare</label>
                </div>


                 <div class="form-group row" id="rental_fare">
                    <label for="fixed" class="col-xs-2 col-form-label">@lang('admin.service.rental_fare') ({{ currency() }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text"  name="rental_fare"  id="rental_fare" value="{{$service->rental_fare}}" placeholder="Set Hour Price">
                    </div>
                </div>




                 <div class="form-group row">
                     <label for="description" class="col-xs-12 col-form-label" style="color: black;font-size: 25px;">Peak Time (Minute Fare)</label>
                </div>
                <!-- Set Peak Time -->
                                <div class="form-group row" >
                        <table class="table table-striped table-bordered dataTable" id="table-2">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Time</th>
                                    <th>Peak Price</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($wc as $index => $w)
                           <tr>
                                
                                <td>{{$index + 1}}</td>
                                <td>{{ date('h:i A', strtotime($w->time[0]->from_time)) }} - {{date('h:i A', strtotime($w->time[0]->to_time))}}</td>
                                <td> <input type="number" id="peak_price" name="peak_price[{{ $w->time_id}}]"  min="1" value="{{$w->peak_price}}" > </td>
                                <input type="hidden" name="peak_update" value="1">

                           </tr>

                           @endforeach
@if($wc_new != 0 )
                            @foreach($wc_new as $index => $w)
                                <tr>
                                    <td>{{$index + 1 }}</td>
                                    <td>{{ date('h:i A', strtotime($w->from_time)) }} - {{date('h:i A', strtotime($w->to_time))}}</td>
                                    <td> <input type="number" id="peak_price" name="peak_new_price[{{ $w->id}}]"  min="0"> </td>
                                    <input type="hidden" name="peak_new" value="0">
  
                                </tr>
                            @endforeach
@endif


                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>ID</th>
                                    <th>Time</th>
                                    <th>Peak Price</th>              
                                     
                                </tr>
                            </tfoot>
                        </table>
                <!-- unit time pricing -->
                </div>
                 <div class="form-group row">
                     <label for="description" class="col-xs-12 col-form-label" style="color: black;font-size: 25px;">Rental Hour Pacakge</label>
                </div>
                 <div class="form-group row" id="rental_fare">
                    <label for="rental_hour_price" class="col-xs-2 col-form-label">@lang('admin.service.hour_fare') ({{ currency() }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text"  name="rental_hour_price"  id="rental_hour_price" value="{{$service->rental_hour_price}}" placeholder="Set Hour Price">
                    </div>
                </div>
                 <div class="form-group row" id="rental_fare">
                    <label for="rental_km_price" class="col-xs-2 col-form-label">@lang('admin.service.km_fare') ({{ currency() }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text"  name="rental_km_price"  id="rental_km_price" value="{{$service->rental_km_price}}" placeholder="Set KM Price">
                    </div>
                </div>
                <div class="form-group row">
                     <label for="description" class="col-xs-12 col-form-label" style="color: black;font-size: 25px;">Night Fare</label>
                </div>

                <!-- Set Night Fare -->
                <div class="form-group row" id="hour_price">
                    <label for="night_fare" class="col-xs-2 col-form-label">@lang('admin.service.night_fare') (in %)</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ $service->night_fare ? $service->night_fare : 0.00  }}" name="night_fare"  id="night_fare" placeholder="Set percentage">
                    </div>
                </div>
 
                 
                <div class="form-group row">
                     <label for="description" class="col-xs-12 col-form-label" style="color: black;font-size: 25px;">Clustured Price</label>
                </div>


                <div class="form-group row" style="margin-right: 0px;margin-left: 0px;">
                                 <table class="table table-striped table-bordered dataTable" id="table-2">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>City Name</th>
                                        <th>Base Distance(0 KM)</th>
                                        <th>Distance Price (1 KM)</th>
                                        <th>City Limits(0KM)</th> 
                                        <th>Minute Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($geofencing as $index => $data)


                                <?php

                                    $service_fencing = \App\ServiceTypeGeoFencings::where('geo_fencing_id',$data->id)->where('service_type_id',$service->id)->first();
                                 ?>
                                    
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $data->city_name }}
                                         <input type="hidden" value="{{@$service_fencing->id ? @$service_fencing->id : 0 }}" name="geo_fencing[{{$data->id}}][id]"/> </td>
                                        

                                        <td><input class="form-control" type="number" value="{{@$service_fencing['distance']  ? @$service_fencing['distance'] : 0 }}" min="0" name="geo_fencing[{{$data->id}}][distance]" placeholder="Distance (0 KM)"/></td>
                                        

                                        <td><input class="form-control" type="number" value="{{@$service_fencing['price']  ? @$service_fencing['price'] : 0 }}" min="0" name="geo_fencing[{{$data->id}}][price]" placeholder="Distance Price"/></td>

                                        <td><input class="form-control" type="number" value="{{@$service_fencing['city_limits']  ? @$service_fencing['city_limits'] : 0 }}" min="0" name="geo_fencing[{{$data->id}}][city_limits]" placeholder="City Limits(0KM)"/></td> 

                                        <td><input class="form-control" type="number" value="{{@$service_fencing['minute']  ? @$service_fencing['minute'] : 0 }}" min="0" name="geo_fencing[{{$data->id}}][minute]" placeholder="Minutes Price"/></td> 
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                   <tr>
                                        <th>ID</th>
                                        <th>City Name</th>
                                        <th>Base Distance(0 KM)</th>
                                        <th>Distance Price (1 KM)</th>
                                        <th>City Limits(0KM)</th> 
                                        <th>Minute Price</th> 
                                    </tr>
                                </tfoot>
                            </table>
                </div>


                
                <div class="form-group row">
                    <div class="col-xs-12 col-sm-6 col-md-3">
                        <a href="{{route('admin.service.index')}}" class="btn btn-danger btn-block">@lang('admin.cancel')</a>
                    </div>
                    <div class="col-xs-12 col-sm-6 offset-md-6 col-md-3">
                        <button type="submit" class="btn btn-primary btn-block">@lang('admin.service.Update_Service_Type')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

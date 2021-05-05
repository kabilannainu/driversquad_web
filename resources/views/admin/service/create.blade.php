@extends('admin.layout.base')

@section('title', 'Add Service Type ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.service.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

            <h5 style="margin-bottom: 2em;">@lang('admin.service.Add_Service_Type')</h5>

            <form class="form-horizontal" action="{{route('admin.service.store')}}" method="POST" enctype="multipart/form-data" role="form">
                {{ csrf_field() }}

             
                <div class="form-group row">
                    <label for="name" class="col-xs-12 col-form-label">@lang('admin.service.Service_Name')</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('name') }}" name="name" required id="name" placeholder="Service Name">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="provider_name" class="col-xs-12 col-form-label">@lang('admin.service.Provider_Name')</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('provider_name') }}" name="provider_name" required id="provider_name" placeholder="Provider Name">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="picture" class="col-xs-12 col-form-label">
                    @lang('admin.service.Service_Image')</label>
                    <div class="col-xs-10">
                        <input type="file" accept="image/*" name="image" class="dropify form-control-file" id="picture" aria-describedby="fileHelp">
                    </div>
                </div>

                 <div class="form-group row">
                    <label for="calculator" class="col-xs-12 col-form-label">@lang('admin.service.Pricing_Logic')</label>
                    <div class="col-xs-10">
                        <select class="form-control" id="calculator" name="calculator">
                            <option value="MIN">@lang('servicetypes.MIN')</option>
                            <option value="HOUR">@lang('servicetypes.HOUR')</option>
                            <option value="DISTANCE">@lang('servicetypes.DISTANCE')</option>
                            <option value="DISTANCEMIN">@lang('servicetypes.DISTANCEMIN')</option>
                            <option value="DISTANCEHOUR">@lang('servicetypes.DISTANCEHOUR')</option>
                        </select>
                    </div>
                </div>
                <!-- Base fare -->
                <div class="form-group row">
                    <label for="fixed" class="col-xs-12 col-form-label">@lang('admin.service.Base_Price') ({{ currency() }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('fixed') }}" name="fixed" required id="fixed" placeholder="Base Price">
                    </div>
                </div> 
             

                <div class="form-group row">
                    <label for="capacity" class="col-xs-12 col-form-label">@lang('admin.service.Seat_Capacity')</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" value="{{ old('capacity') }}" name="capacity" required id="capacity" placeholder="Capacity">
                    </div>
                </div>

               

                <div class="form-group row">
                    <label for="description" class="col-xs-12 col-form-label">@lang('admin.service.Description')</label>
                    <div class="col-xs-10">
                        <textarea class="form-control" type="number" value="{{ old('description') }}" name="description" required id="description" placeholder="Description" rows="4"></textarea>
                    </div>
                </div>
 
               {{-- <!-- Base distance -->
                <div class="form-group row">
                    <label for="distance" class="col-xs-12 col-form-label">@lang('admin.service.Base_Distance') ({{ distance() }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('distance') }}" name="distance" required id="distance" placeholder="Base Distance">
                    </div>
                </div>
                <!-- unit distance price -->
                <div class="form-group row">
                    <label for="price" class="col-xs-12 col-form-label">@lang('admin.service.unit')({{ distance() }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('price') }}" name="price" required id="price" placeholder="Unit Distance Price">
                    </div>
                </div>

                <!-- unit time pricing -->
                <div class="form-group row">
                    <label for="minute" class="col-xs-12 col-form-label">@lang('admin.service.unit_time')</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('minute') }}" name="minute" required id="minute" placeholder="Unit Time Pricing">
                    </div>
                </div>
                 <!-- Set Hour Price -->
                <div class="form-group row" id="hour_price">
                    <label for="fixed" class="col-xs-12 col-form-label">@lang('admin.service.hourly_Price') ({{ currency() }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('fixed') }}" name="hour"  id="hourly_price" placeholder="Set Hour Price( Only For DISTANCEHOUR )">
                    </div>
                </div>--}}

                 <div class="form-group row">
                     <label for="description" class="col-xs-12 col-form-label" style="color: black;font-size: 25px;">Outstation Fare</label>
                </div>

                 <div class="form-group row" id="outstation_price">
                    <label for="fixed" class="col-xs-12 col-form-label">@lang('admin.service.outstation_per_km') ({{ distance() }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" value="{{ old('outstation_km',0) }}" name="outstation_km"  id="outstation_price" min="0" placeholder="Onwway Km Price">
                    </div>
                </div>

                <div class="form-group row" id="roundtrip_price">
                    <label for="fixed" class="col-xs-12 col-form-label">@lang('admin.service.roundtrip_per_km') ({{ distance() }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" value="{{ old('roundtrip_km',0) }}" name="roundtrip_km"  id="roundtrip_price" min="0" placeholder="Roundtrip Km Price">
                    </div>
                </div>


                 <div class="form-group row" id="outstation_driver">
                    <label for="fixed" class="col-xs-12 col-form-label">@lang('admin.service.outstation_driverbata') ({{ currency() }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" value="{{ old('outstation_driver',0) }}" name="outstation_driver"  id="outstation_driver" min="0" placeholder="Driver Bata per day">
                    </div>
                </div>

                <div class="form-group row">
                     <label for="description" class="col-xs-12 col-form-label" style="color: black;font-size: 25px;">Rental Fare</label>
                </div>


                 <div class="form-group row" id="rental_fare">
                    <label for="fixed" class="col-xs-12 col-form-label">@lang('admin.service.rental_fare') ({{ currency() }})</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('rental_fare') }}" name="rental_fare"  id="rental_fare" placeholder="Set Hour Price">
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
                                    <td>{{ date('h:i A', strtotime($w->from_time)) }} - {{date('h:i A', strtotime($w->to_time))}}</td>
                                    <td> <input type="number" id="peak_price" name="peak_price[{{ $w->id}}]"  min="1"> </td>
                                
                                   
                                </tr>
                            @endforeach

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
                     <label for="description" class="col-xs-12 col-form-label" style="color: black;font-size: 25px;">Night Fare</label>
                </div>

                <!-- Set Night Fare -->
                <div class="form-group row" id="hour_price">
                    <label for="night_fare" class="col-xs-12 col-form-label">@lang('admin.service.night_fare') (in %)</label>
                    <div class="col-xs-10">
                        <input value="0.00" class="form-control" type="text" value="{{ old('night_fare') }}" name="night_fare"  id="night_fare" placeholder="Set percentage">
                    </div>
                </div>
    
                <div class="form-group row">
                     <label for="description" class="col-xs-12 col-form-label" style="color: black;font-size: 25px;">Clustured Price</label>
                </div>


                <div class="row" style="margin-right: 0px;margin-left: 0px;">
                                 <table class="table table-striped table-bordered dataTable" id="table-2">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>City Name</th>
                                        <th>Distance(0KM)</th>
                                        <th>Distance Price</th>
                                        <th>City Limits(0KM)</th>
                                        <th>Minute Price</th> 
                                    </tr>
                                </thead>
                                <tbody>

                           
                                @foreach($geofencing as $index => $data)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $data->city_name }}
                                            <input type="hidden" value="{{$data->id}}" name="geo_fencing[{{$data->id}}][id]"/> </td>
                                        <td><input class="form-control" type="number" min="0" value="0" name="geo_fencing[{{$data->id}}][distance]" placeholder="Distance (0 KM)"/></td>
                                        <td><input class="form-control" type="number" min="0" value="0" name="geo_fencing[{{$data->id}}][price]" placeholder="Distance Price"/></td>
                                        <td><input class="form-control" type="number" min="0" value="0" name="geo_fencing[{{$data->id}}][city_limits]" placeholder="City Limits(0KM)"/></td> 
                                        <td><input class="form-control" type="number" min="0" value="0" name="geo_fencing[{{$data->id}}][minute]" placeholder="Minutes Price"/></td>  
                                    </tr>

                                @endforeach
                                </tbody>
                                <tfoot>
                                   <tr>
                                        <th>ID</th>
                                        <th>City Name</th>
                                        <th>Distance(0KM)</th>
                                        <th>Distance Price</th>
                                        <th>City Limits(0KM)</th> 
                                        <th>Minute Price</th>  
                                    </tr>
                                </tfoot>
                            </table>
                </div>
                <div class="form-group row">
                    <div class="col-xs-10">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6 col-md-3">
                                <a href="{{ route('admin.service.index') }}" class="btn btn-danger btn-block">@lang('admin.cancel')</a>
                            </div>
                            <div class="col-xs-12 col-sm-6 offset-md-6 col-md-3">
                                <button type="submit" class="btn btn-primary btn-block">@lang('admin.service.Add_Service_Type'
                                )</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


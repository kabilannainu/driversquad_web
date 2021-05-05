@extends('fleet.layout.base')

@section('title', 'Add VehicleType ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('fleet.vehicle.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i>back</a>

            <h5 style="margin-bottom: 2em;">Add VehicleType</h5>

            <form class="form-horizontal" action="{{route('fleet.vehicle.store')}}" method="POST" enctype="multipart/form-data" role="form">
                {{csrf_field()}}
                <div class="form-group row">
                    <label for="vehicle_model" class="col-xs-12 col-form-label">Model</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('vehicle_model') }}" name="vehicle_model" required id="vehicle_model" placeholder="Enter Vehicle Model">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="vehicle_number" class="col-xs-12 col-form-label">Vehicle Number</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('vehicle_number') }}" name="vehicle_number" required id="vehicle_number" placeholder="Enter Vehicle Number">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="vehicle_number" class="col-xs-12 col-form-label">Service Type</label>
                    <div class="col-xs-10">
                        <select class="form-control" name="service_type">
                            @foreach($service as $type)
                            <option value="{{$type->id}}">{{$type->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>                        
                
                <div class="form-group row">
                    <label for="zipcode" class="col-xs-12 col-form-label"></label>
                    <div class="col-xs-10">
                        <button type="submit" class="btn btn-primary">Add VehicleType</button>
                        <a href="{{route('fleet.vehicle.index')}}" class="btn btn-default">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

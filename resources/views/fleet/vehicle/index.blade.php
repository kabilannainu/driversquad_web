@extends('fleet.layout.base')

@section('title', 'Vehicle Type ')

@section('content')

    <div class="content-area py-1">
        <div class="container-fluid">
            
            <div class="box box-block bg-white">
                <h5 class="mb-1">Vehicle Type</h5>
                <a href="{{ route('fleet.vehicle.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i>Add Vehicletype</a>
                <table class="table table-striped table-bordered dataTable" id="table-2">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Model</th>
                            <th>Vehicle No.</th>
                            <th>Service Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($fleet_vehicle as $index => $vehicle)
                        <tr>
                            <td>{{$index + 1}}</td>
                            <td>{{$vehicle->vehicle_model}}</td>
                            <td>{{$vehicle->vehicle_number}}</td>
                            <td>{{@$vehicle->service->name}}</td>
                            
                            
                            <td>
                                <form action="{{ route('fleet.vehicle.destroy', $vehicle->id) }}" method="POST">
                                    {{ csrf_field() }}
                                    <input type="hidden" name="_method" value="DELETE">
                                    @if( Setting::get('demo_mode') == 0)
                                    <a href="{{ route('fleet.vehicle.edit', $vehicle->id) }}" class="btn btn-info"><i class="fa fa-pencil"></i>Edit</a>
                                    <button class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i>Delete</button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Id</th>
                            <th>Model</th>
                            <th>Vehicle No.</th>
                            <th>Service Type</th>
                            <th>Action</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
        </div>
    </div>
@endsection
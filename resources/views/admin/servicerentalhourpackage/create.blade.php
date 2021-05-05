@extends('admin.layout.base')

@section('title', 'Add Rental Hour Package ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.servicerentalhourpackage.index') }}?service_type_id={{Request::get('service_type_id')}}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

            <h5 style="margin-bottom: 2em;">Add Rental Hour Package</h5>

            <form class="form-horizontal" action="{{route('admin.servicerentalhourpackage.store')}}" method="POST" enctype="multipart/form-data" role="form">
                {{csrf_field()}}
                <div class="form-group row">
                    <label for="hour" class="col-xs-12 col-form-label">Hour</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('hour') }}" name="hour" required id="hour" placeholder="Hour">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="km" class="col-xs-12 col-form-label">KM</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ old('km') }}" name="km" required id="km" placeholder="KM">
                    </div>
                </div>  
                <div class="form-group row">
                    <label for="price" class="col-xs-12 col-form-label">Price</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="number" value="{{ old('price') }}" name="price" required id="price" placeholder="Price"> 
                        <input class="form-control" type="hidden" value="{{ Request::get('service_type_id') }}" name="service_type_id">
                    </div>
                </div>               
                
                <div class="form-group row">
                    <label for="zipcode" class="col-xs-12 col-form-label"></label>
                    <div class="col-xs-10">
                        <button type="submit" class="btn btn-primary">Add Rental Hour Package</button>
                        <a href="{{route('admin.servicerentalhourpackage.index')}}" class="btn btn-default">@lang('admin.cancel')</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

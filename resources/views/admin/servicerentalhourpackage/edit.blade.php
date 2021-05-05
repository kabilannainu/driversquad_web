@extends('admin.layout.base')

@section('title', 'Update Rental Hour Package ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
    	    <a href="{{ route('admin.servicerentalhourpackage.index') }}?service_type_id={{$package->service_type_id}}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

			<h5 style="margin-bottom: 2em;">Rental Hour Package</h5>

            <form class="form-horizontal" action="{{route('admin.servicerentalhourpackage.update', $package->id )}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
            	<input type="hidden" name="_method" value="PATCH">
				<div class="form-group row">
					<label for="hour" class="col-xs-2 col-form-label">Hour</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $package->hour }}" name="hour" required id="hour" placeholder="Enter Hour">
					</div>
				</div>
				<div class="form-group row">
					<label for="km" class="col-xs-2 col-form-label">KM</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $package->km }}" name="km" required id="km" placeholder="Enter KM">
					</div>
				</div> 
				<div class="form-group row">
					<label for="price" class="col-xs-2 col-form-label">Price</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ $package->price }}" name="price" required id="price" placeholder="Enter Price">
					</div>
				</div>
				

				<div class="form-group row">
					<label for="feedback" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">Update Rental Hour Package</button>
						<a href="{{route('admin.servicerentalhourpackage.index')}}" class="btn btn-default">@lang('admin.cancel')</a>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

@endsection

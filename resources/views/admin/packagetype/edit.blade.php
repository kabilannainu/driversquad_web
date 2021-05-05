@extends('admin.layout.base')

@section('title', 'Update Packagetype ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
    	    <a href="{{ route('admin.packagetype.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

			<h5 style="margin-bottom: 2em;">Package Type</h5>

            <form class="form-horizontal" action="{{route('admin.packagetype.update', $package->id )}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
            	<input type="hidden" name="_method" value="PATCH">
				<div class="form-group row">
					<label for="package_hour" class="col-xs-2 col-form-label">Package Hour</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $package->package_hour }}" name="package_hour" required id="package_hour" placeholder="Enter Package Hour">
					</div>
				</div>
				<div class="form-group row">
					<label for="package_km" class="col-xs-2 col-form-label">Package KM</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $package->package_km }}" name="package_km" required id="package_km" placeholder="Enter Package KM">
					</div>
				</div>
				

				<div class="form-group row">
					<label for="feedback" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">Update Package</button>
						<a href="{{route('admin.packagetype.index')}}" class="btn btn-default">@lang('admin.cancel')</a>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

@endsection

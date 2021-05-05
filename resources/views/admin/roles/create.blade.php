@extends('admin.layout.base')

@section('title', 'Add Role ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
    		@if(Setting::get('demo_mode') == 1)
            <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : No Permission to Edit and Delete.
            </div>
            @endif
            <a href="{{ route('admin.roles.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

			<h5 style="margin-bottom: 2em;">@lang('admin.roles.Add_Role')</h5>

            <form class="form-horizontal" action="{{route('admin.roles.store')}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
				<div class="form-group row">
					<label for="first_name" class="col-xs-12 col-form-label">@lang('admin.role_name')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ old('role_name') }}" name="role_name" required id="role_name" placeholder="Name">
					</div>
				</div>

	
			   <div class="form-group row">
					<label for="email" class="col-xs-12 col-form-label">@lang('admin.description')</label>
					<div class="col-xs-10">
						<textarea class="form-control" placeholder="Description" name="description">{{old('description')}}</textarea>
					</div>
				</div>

	

				<div class="form-group row">
					<label for="zipcode" class="col-xs-12 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">@lang('admin.roles.Add_Role')</button>
						<a href="{{route('admin.user.index')}}" class="btn btn-default">@lang('admin.cancel')</a>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

@endsection

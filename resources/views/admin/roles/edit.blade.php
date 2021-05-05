@extends('admin.layout.base')

@section('title', 'Update Role ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
    	    <a href="{{ route('admin.roles.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

			<h5 style="margin-bottom: 2em;">@lang('admin.roles.Update_Roles')</h5>

            <form class="form-horizontal" action="{{route('admin.roles.update', $role->id )}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
            	<input type="hidden" name="_method" value="PATCH">
				<div class="form-group row">
					<label for="first_name" class="col-xs-2 col-form-label">@lang('admin.roles.role_name')</label>
					<div class="col-xs-10">
						<input disabled="" class="form-control" type="text" value="{{ $role->role_name }}" name="role_name" required id="role_name" placeholder="Role Name">
					</div>
				</div>

			
  				 <div class="form-group row">
					<label for="email" class="col-xs-2 col-form-label">@lang('admin.description')</label>
					<div class="col-xs-10">
						<textarea class="form-control" placeholder="Description" name="description">{{ $role->description }}</textarea>
					</div>
				</div>

				<div class="form-group row">
					<label for="zipcode" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">@lang('admin.roles.Update_Roles')</button>
						<a href="{{route('admin.user.index')}}" class="btn btn-default">@lang('admin.cancel')</a>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

@endsection

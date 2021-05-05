@extends('admin.layout.base')

@section('title', 'Update Sub Admin ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
    	    <a href="{{ route('admin.subadmin.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

			<h5 style="margin-bottom: 2em;">Update SubAdmin</h5>

            <form class="form-horizontal" action="{{route('admin.subadmin.update', $user->id )}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
            	<input type="hidden" name="_method" value="PATCH">
				<div class="form-group row">
					<label for="first_name" class="col-xs-2 col-form-label">@lang('admin.name')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ $user->name }}" name="name" required id="name" placeholder="Name">
					</div>
				</div>

	
				<div class="form-group row">
					
					<label for="picture" class="col-xs-2 col-form-label">@lang('admin.picture')</label>
					<div class="col-xs-10">
					@if(isset($user->picture))
                    	<img style="height: 90px; margin-bottom: 15px; border-radius:2em;" src="{{url('storage/'.$user->picture)}}">
                    @endif
						<input type="file" accept="image/*" name="picture" class="dropify form-control-file" id="picture" aria-describedby="fileHelp">
					</div>
				</div>

				<div class="form-group row">
					<label for="picture" class="col-xs-2 col-form-label">@lang('admin.role')</label>
					<div class="col-xs-10">
						<select name="role_id" class="col-xs-12 col-form-label">
								<option value="">Select Role</option>
								<?php
									foreach( $role as $list_role) {
								?>
								<option <?php if($list_role->id==$user->role_id) { echo 'selected=selected'; } ?> value="{{$list_role->id}}">{{$list_role->role_name}}</option>
								<?php } ?>

						</select>
					</div>
				</div>

	
				<div class="form-group row">
					<label for="zipcode" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">@lang('admin.users.Update_User')</button>
						<a href="{{route('admin.user.index')}}" class="btn btn-default">@lang('admin.cancel')</a>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

@endsection

@extends('admin.layout.base')

@section('title', 'Profile ')

@section('content')

<!-- edit page -->
<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
    	    <a href="{{ route('admin.user.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Back</a>

			<h5 style="margin-bottom: 2em;">User Details</h5>

  
            	<input type="hidden" name="_method" value="PATCH">
				<div class="form-group row">
					<label for="first_name" class="col-xs-2 col-form-label">First Name</label>
					<div class="col-xs-10">
						{{ $user->first_name }}
					</div>
				</div>

				<div class="form-group row">
					<label for="last_name" class="col-xs-2 col-form-label">Last Name</label>
					<div class="col-xs-10">
						{{ $user->last_name }}
					</div>
				</div>


				<div class="form-group row">
					<label for="last_name" class="col-xs-2 col-form-label">Email</label>
					<div class="col-xs-10">
						@if(Setting::get('demo_mode', 0) == 1)
                        {{ substr($user->email, 0, 3).'****'.substr($user->email, strpos($user->email, "@")) }}
                        @else
                        {{ $user->email }}
                        @endif
					</div>
				</div>


				<div class="form-group row">
					
					<label for="picture" class="col-xs-2 col-form-label">Picture</label>
					<div class="col-xs-10">
					@if(isset($user->picture))
                    	<img style="height: 90px; margin-bottom: 15px; border-radius:2em;" src="{{img($user->picture)}}">
        			@else
        				N/A
                    @endif
		
					</div>
				</div>

				<div class="form-group row">
					<label for="mobile" class="col-xs-2 col-form-label">Mobile</label>
					<div class="col-xs-10">
						@if(Setting::get('demo_mode', 0) == 1)
                        {{ substr($user->mobile, 0, 5).'****' }}
                        @else
                        {{ $user->mobile }}
                        @endif
					</div>
				</div>
			
		</div>
    </div>
</div>

@endsection

@extends('fleet.layout.base')

@section('title', 'Add Provider ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
            <a href="{{ route('fleet.provider.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> Back</a>

			<h5 style="margin-bottom: 2em;">Add Provider</h5>

            <form class="form-horizontal" action="{{route('fleet.provider.store')}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}
				<div class="form-group row">
					<label for="first_name" class="col-xs-12 col-form-label">First Name</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ old('first_name') }}" name="first_name" required id="first_name" placeholder="First Name">
					</div>
				</div>

				<div class="form-group row">
					<label for="last_name" class="col-xs-12 col-form-label">Last Name</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ old('last_name') }}" name="last_name" required id="last_name" placeholder="Last Name">
					</div>
				</div>

				<div class="form-group row">
					<label for="mobile" class="col-xs-12 col-form-label">Mobile</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ old('mobile') }}" name="mobile" required id="mobile" placeholder="Mobile">
					</div>
				</div>



				<div class="form-group row">
					<label for="email" class="col-xs-12 col-form-label">Email</label>
					<div class="col-xs-10">
						<input class="form-control" type="email" name="email" value="{{old('email')}}" id="email" placeholder="Email">
					</div>
				</div> 

				<div class="form-group row">
					<label for="picture" class="col-xs-12 col-form-label">Picture</label>
					<div class="col-xs-10">
						<input type="file" accept="image/*" name="avatar" class="dropify form-control-file" id="picture" aria-describedby="fileHelp">
					</div>
				</div>
				<div class="form-group row">
					<h2><label for="mobile" class="col-xs-12 col-form-label">Documents</label></h2>
				</div>
				@foreach($DriverDocuments as $Document)
        
            	<div class="form-group row"> 
            	<label for="mobile" class="col-xs-12 col-form-label"> {{$Document->name}}</label>
					<div class="col-xs-10">
			            <span class="input-group-addon btn btn-default btn-file" style="width:100%;display: block;margin-bottom: 25px"  >
			           
			            <input type="file" name="document[]" accept="application/pdf, image/*" value="" >
			            <input type="hidden" name="id[]" value="{{$Document->id}}">
			            </span>
	           		</div>
				</div>
	          
	            @endforeach

				<div class="form-group row">
					<label for="zipcode" class="col-xs-12 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">Add Provider</button>
						<a href="{{route('fleet.provider.index')}}" class="btn btn-default">Cancel</a>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

@endsection

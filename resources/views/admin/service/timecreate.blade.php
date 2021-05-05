@extends('admin.layout.base')

@section('title', 'Add Time')

@section('link')

 <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" />
<link rel="stylesheet" href="{{ asset('asset/bdt/css/bootstrap-material-datetimepicker.css') }}" />
<link href='https://fonts.googleapis.com/css?family=Roboto:400,500' rel='stylesheet' type='text/css'>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

@endsection


@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('admin.time.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

            <h5 style="margin-bottom: 2em;">Add Time</h5>

            <form class="form-horizontal" action="{{route('admin.time.store')}}" method="POST" enctype="multipart/form-data" role="form">
                {{ csrf_field() }}
                <div class="form-group row">
                    <label for="name" class="col-xs-12 col-form-label">From</label>
                    <div class="col-xs-10">
                        <input class="form-control from" type="text"  name="from_time" required id="from"    placeholder="From">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="provider_name" class="col-xs-12 col-form-label">To</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text"  name="to_time" required id="to" placeholder="to"> 
                    </div>
                </div>
                

                <div class="form-group row">
                    <div class="col-xs-10">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6 col-md-3">
                                <a href="{{ route('admin.time.index') }}" class="btn btn-danger btn-block">@lang('admin.cancel')</a>
                            </div>
                            <div class="col-xs-12 col-sm-6 offset-md-6 col-md-3">
                                <button type="submit" class="btn btn-primary btn-block">Add Time</button>
                            </div>
                        </div>
                    </div>
                </div>


            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript" src="{{asset('main/vendor/moment/moment.js')}}"></script>
   <script type="text/javascript" src="{{asset('main/vendor/bdt/js/bootstrap-material-datetimepicker.js')}}"></script>


   <script type="text/javascript">
   $(document).ready(function()
       {
        

        $('#from').bootstrapMaterialDatePicker({  
            format: 'hh:mm A' ,
            date: false,


         });
        $('#to').bootstrapMaterialDatePicker({  

            format: 'hh:mm A' ,
            date: false,


         });

        });  
</script>
@endsection


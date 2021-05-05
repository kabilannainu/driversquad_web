@extends('admin.layout.base')

@section('title', 'Request History ')

@section('content') 

<style type="text/css"> 
 .pagination {
    display: inline-flex;
    border-radius: .25rem;
}
    .pagination li {
    border: 1px solid #f0f1f2;
    width: 25px;
    height: 25px;
    text-align: center;
    list-style: none;
}
</style>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>

<script type="text/javascript">
    
    $(document).ready(function () {
        $('#seleted_delete').hide();
        $('input[type="checkbox"]').click(function () {
            var checked_count = $('input:checkbox:checked').length;
            if(checked_count >= 1) {
            $('#seleted_delete').show();
            }
            else
            {
            $('#seleted_delete').hide();
            }
        });
        $('body').on('click', '#selectAll', function () { 
            $('#seleted_delete').hide();
          if ($(this).hasClass('allChecked')) { 
             $('input[type="checkbox"]').prop('checked', false);
             $('#seleted_delete').hide();
          } else { 
           $('input[type="checkbox"]').prop('checked', true);
           if($('input[type="checkbox"]').prop('checked') ==true)
                $('#seleted_delete').show();
           }
           $(this).toggleClass('allChecked');
         })
           

        $('body').on('click', '#seleted_delete', function () {
             var deleted = [];
             var deleted_id = [];
             <?php 
                foreach($requests as $key => $data)
                { ?>
                    
                    if($('.delete{{$data->id}}').prop('checked'))
                    { 
                        deleted_id[{{$key}}]= '{{$data->id}}'; 
                    }
                    


                <?php }
                ?>
                
                $.post( "requests/seleted_cancelled_delete", { deleted_id: deleted_id }) 
                 .done(function( data ) {
                    //alert( "Data Loaded: " + data );
                    window.location.replace("{{url('admin/requests')}}");
                  });

        })
    });
</script>

<!-- Delete Model -->
    <div class="modal fade" id="myModal" role="dialog"> 
        <div class="modal-dialog modal-sm">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">Are you sure?</h4>
            </div> 
            <div class="modal-footer">
            <button type="button" id="seleted_delete" style="margin-left: 1em;" class="btn btn-info btn-md"> Delete</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
            </div>
          </div>
        </div>
      </div>
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            @if(Setting::get('demo_mode') == 1)
        <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : No Permission to Edit and Delete.
                </div>
                @endif
            <h5 class="mb-1">Request History</h5>
            <div class="col-md-12"> 
            <div class="col-md-3">
            <button type="button" class="btn btn-info btn-md pull-left" data-toggle="modal" data-target="#myModal" style="margin-left: -30PX;"><span class="glyphicon glyphicon-trash"></span> Selected Cancelled Delete</button>
            </div> 
            </div> <br> <br>
            @if(count($requests) != 0)
            <table class="table table-striped table-bordered dataTable" id="cus-request-table-2">
                <thead>
                    <tr>
                        <th><button type="button" id="selectAll" class="main">
                        <span class="sub"></span> All </button></th>
                        <th>ID</th>
                        <th>@lang('admin.request.Booking_ID')</th>
                        <th>@lang('admin.request.User_Name')</th>
                        <th>@lang('admin.request.Provider_Name')</th>
                        <th>@lang('admin.request.Date_Time')</th>
                        <th>@lang('admin.status')</th>
                        <th>@lang('admin.amount')</th>
                        <th>@lang('admin.request.Payment_Mode')</th>
                        <th>@lang('admin.request.Payment_Status')</th>
                        <th>@lang('admin.action')</th>
                    </tr>
                </thead>
                <tbody>@php($page_plus=$page-10)
                @foreach($requests as $index => $request)
                    <tr>
                        <td> @if($request->status == 'CANCELLED')<input type="checkbox" class="delete{{$request->id}}" data-id="{{$request->id}}">@endif</td>
                        <td>{{ $request->id + $page_plus}}</td>
                        <td>{{ $request->booking_id }}</td>
                        <td>
                            @if($request->user)
                                {{ $request->user?$request->user->first_name:'' }} {{ $request->user?$request->user->last_name:'' }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if($request->provider)
                                {{ $request->provider?$request->provider->first_name:'' }} {{ $request->provider?$request->provider->last_name:'' }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>
                            @if($request->created_at)
                                <span class="text-muted">{{$request->created_at->diffForHumans()}}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $request->status }}</td>
                        <td>
                            @if($request->payment != "")
                                {{ currency($request->payment->total) }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td>{{ $request->payment_mode }}</td>
                        <td>
                            @if($request->paid)
                                Paid
                            @else
                                Not Paid
                            @endif
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-primary waves-effect dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    Action
                                </button>
                                <div class="dropdown-menu">
                                    <a href="{{ route('admin.requests.show', $request->id) }}" class="dropdown-item">
                                        <i class="fa fa-search"></i> More Details
                                    </a>
                                    <form action="{{ route('admin.requests.destroy', $request->id) }}" method="POST">
                                        {{ csrf_field() }}
                                        @if( Setting::get('demo_mode') == 0)
                                        {{ method_field('DELETE') }}
                                        <button type="submit" class="dropdown-item">
                                            <i class="fa fa-trash"></i> Delete
                                        </button>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>  
                <tfoot>
                    <tr>
                        <th><button type="button" id="selectAll" class="main">
                        <span class="sub"></span> All </button></th>
                        <th>ID</th>
                        <th>@lang('admin.request.Booking_ID')</th>
                        <th>@lang('admin.request.User_Name')</th>
                        <th>@lang('admin.request.Provider_Name')</th>
                        <th>@lang('admin.request.Date_Time')</th>
                        <th>@lang('admin.status')</th>
                        <th>@lang('admin.amount')</th>
                        <th>@lang('admin.request.Payment_Mode')</th>
                        <th>@lang('admin.request.Payment_Status')</th>
                        <th>@lang('admin.action')</th>
                    </tr>
                </tfoot>
            </table> 
            {{$requests->links()}} 
            @else
            <h6 class="no-result">No results found</h6>
            @endif 
        </div>
    </div>
</div>
@endsection
@extends('admin.layout.base')

@section('title', 'Providers ')

@section('content')
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
          if ($(this).hasClass('allChecked')) { 
             $('input[type="checkbox"]').prop('checked', false); 
             $('#seleted_delete').hide(); 
          } else { 
           $('input[type="checkbox"]').prop('checked', true);
           $('#seleted_delete').show(); 
           }
           $(this).toggleClass('allChecked');
         })
           

        $('body').on('click', '#seleted_delete', function () {
             var deleted = [];
             var deleted_id = [];
             <?php 
                foreach($providers as $key => $data)
                { ?>
                    
                    if($('.delete{{$data->id}}').prop('checked'))
                    { 
                        deleted_id[{{$key}}]= '{{$data->id}}'; 
                    } 
                    


                <?php }
                ?>
                
                $.post( "provider/seleted_delete", { deleted_id: deleted_id }) 
                 .done(function( data ) {
                    //alert( "Data Loaded: " + data );
                    window.location.replace("{{url('admin/provider')}}");
                  });

        })
    });
</script>

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
      <!-- Make online provider Model -->
    <div class="modal fade" id="myModalMakeOnline" role="dialog">
        <div class="modal-dialog modal-sm">
          <div class="modal-content">
            <div class="modal-header">
              <button type="button" class="close" data-dismiss="modal">&times;</button>
              <h4 class="modal-title">Are you sure All provider ONLINE to click yes?</h4>
            </div> 
            <div class="modal-footer">
            <a href="{{ route('admin.provider.online') }}" class="btn btn-info btn-md">YES</a>
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
            <h5 class="mb-1">
                @lang('admin.provides.providers')
                @if(Setting::get('demo_mode', 0) == 1)
                <span class="pull-right">(*personal information hidden in demo)</span>
                @endif
            </h5> 
            <div class="col-md-12"> 
            <div class="col-md-3">
            <button type="button" class="btn btn-info btn-md pull-left" data-toggle="modal" data-target="#myModal" style="margin-left: -30PX;"><span class="glyphicon glyphicon-trash"></span> Seleted Delete</button>
            </div>
             <div class="col-md-4">
            <form action="{{route('admin.provider.index')}}" method="GET"> 
            <div class="col-md-6">
            <input type="text" class="form-control col-md-6" name="search" value="{{$search}}">
            </div>
            <div class="col-md-6"> 
            <button type="submit" class="btn btn-success btn-md col-md-6" ><span class="glyphicon glyphicon-search" style="font-size: 15px;"></span></button>
            </div> 
            </form>
            </div>
            <div class="col-md-2">
            <button type="button" class="btn btn-success btn-md" data-toggle="modal" data-target="#myModalMakeOnline" > Make Provider Online</button>
            </div>
            <div class="col-md-3" style="text-align: right;">
            @if($fleet != 'fleet')
            <a href="{{ route('admin.provider.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i>@lang('admin.provides.add_new_provider')</a> 
            @endif
            </div> 
            </div>  <br><br> 
            <table class="table table-striped table-bordered dataTable" id="cus-table-2" style="width: 100% !important">
                <thead>
                    <tr>
                        <th><button type="button" id="selectAll" class="main">
                        <span class="sub"></span> All </button></th>
                        <th>@lang('admin.id')</th>
                        <th>Fleet Name</th>
                        <th>Joined At</th>
                        <th>@lang('admin.provides.full_name')</th> 
                        <th>@lang('admin.mobile')</th> 
                        <th>Total / Accepted / Cancelled</th>  
                        <th>Vehicle Type</th>
                        <th>Vehicle Number</th>
                        <th>@lang('admin.provides.service_type')</th>
                        <th>@lang('admin.status')</th>
                        <th>@lang('admin.action')</th>
                    </tr>
                </thead>
                <tbody> @php($page_plus=$page-10)
                @foreach($providers as $index => $provider)
                    <tr>
                        <td><input type="checkbox" class="delete{{$provider->id}}" data-id="{{$provider->id}}"></td>
                        <td>{{ $index + 1 + $page_plus }}</td>
                        <td>{{ $provider->fleets ? $provider->fleets->name : '--' }}</td>
                        <td>{{ date('d M Y', strtotime($provider->created_at) )}}</td>
                        <td>{{ $provider->first_name }} {{ $provider->last_name }}</td> 
                        @if(Setting::get('demo_mode', 0) == 1)
                        <td>{{ substr($provider->email, 0, 3).'****'.substr($provider->email, strpos($provider->email, "@")) }}</td>
                        @else
                        <td>{{ $provider->email }}</td>
                        @endif
                        @if(Setting::get('demo_mode', 0) == 1)
                        <td>{{ substr($provider->mobile, 0, 5).'****' }}</td>
                        @else
                        <td>{{ $provider->mobile }}</td>
                        @endif
                        <td>{{ $provider->accepted->count() + $provider->cancelled->count() }} / {{ $provider->accepted->count() }} / {{ $provider->cancelled->count() }}</td> 
                        <td>{{ @$provider->service->service_type->name }}</td>
                        <td>{{ @$provider->service->service_number }}</td>
                        <td>
                            @if($provider->pending_documents() > 0 || $provider->service == null)
                                <a class="btn btn-danger btn-block label-right" href="{{route('admin.provider.document.index', $provider->id )}}">Attention! <span class="btn-label">{{ $provider->pending_documents() }}</span></a>
                            @else
                                <a class="btn btn-success btn-block" href="{{route('admin.provider.document.index', $provider->id )}}">All Set!</a>
                            @endif
                        </td>
                        <td>
                            @if($provider->service)
                                @if($provider->service->status == 'active')
                                    <label class="btn btn-block btn-primary">Yes</label>
                                @elseif($provider->service->status == 'hold')
                                    <label class="btn btn-block btn-danger">Hold</label>
                                @else
                                    <label class="btn btn-block btn-warning">No</label>
                                @endif
                            @else
                                <label class="btn btn-block btn-danger">N/A</label>
                            @endif
                        </td>
                        <td>
                            <div class="input-group-btn">
                                @if($provider->status == 'approved')
                                <a class="btn btn-danger btn-block" href="{{ route('admin.provider.disapprove', $provider->id ) }}">@lang('Disable')</a>
                                @else
                                <a class="btn btn-success btn-block" href="{{ route('admin.provider.approve', $provider->id ) }}">@lang('Enable')</a>
                                @endif
                                <button type="button" 
                                    class="btn btn-info btn-block dropdown-toggle"
                                    data-toggle="dropdown">@lang('admin.action')
                                    <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="{{ route('admin.provider.request', $provider->id) }}" class="btn btn-default"><i class="fa fa-search"></i> @lang('admin.History')</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.provider.statement', $provider->id) }}" class="btn btn-default"><i class="fa fa-account"></i> @lang('admin.Statements')</a>
                                    </li>
                                    @if( Setting::get('demo_mode') == 0)
                                    <li>
                                        <a href="{{ route('admin.provider.edit', $provider->id) }}" class="btn btn-default"><i class="fa fa-pencil"></i> @lang('admin.edit')</a>
                                    </li>
                                    @endif
                                    <li>
                                        <form action="{{ route('admin.provider.destroy', $provider->id) }}" method="POST">
                                            {{ csrf_field() }}
                                            <input type="hidden" name="_method" value="DELETE">
                                            @if( Setting::get('demo_mode') == 0)
                                            <button class="btn btn-default look-a-like" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i>@lang('admin.delete')</button>
                                            @endif
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody> 
                 <tfoot>
                    <tr>
                        <th><button type="button" id="selectAll" class="main">
                        <span class="sub"></span> All </button></th>
                        <th>@lang('admin.id')</th>
                        <th>Fleet Name</th>
                        <th>Joined At</th>
                        <th>@lang('admin.provides.full_name')</th> 
                        <th>@lang('admin.mobile')</th> 
                        <th>Total / Accepted / Cancelled</th>  
                        <th>Vehicle Type</th>
                        <th>Vehicle Number</th>
                        <th>@lang('admin.provides.service_type')</th>
                        <th>@lang('admin.status')</th>
                        <th>@lang('admin.action')</th>
                    </tr>
                </tfoot>
            </table> 
            {{$providers->appends(['search' => $search])->links()}} 
        </div>
    </div>
</div>
@endsection
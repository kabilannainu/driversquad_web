@extends('admin.layout.base')

@section('title', 'Service Types ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
           @if(Setting::get('demo_mode') == 1)
        <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : No Permission to Edit and Delete.
                </div>
                @endif 
            <h5 class="mb-1">Service Types</h5>
            <a href="{{ route('admin.service.create') }}" style="margin-left: 1em;" class="btn btn-primary pull-right"><i class="fa fa-plus"></i> Add New Service</a>
            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Service Name</th>
                        <th>Provider Name</th>
                        <th>Capacity</th>
                        <th>Base Price</th>
                        <!-- <th>Base Distance</th>
                        <th>Distance Price</th>
                        <th>Time Price</th>
                        <th>Hour Price</th> -->
                        <th>Price Calculation</th>
                        <th>Service Image</th>
                        <th>Time Based Price</th>
                        <th>Rental</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($services as $index => $service)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $service->name }}</td>
                        <td>{{ $service->provider_name }}</td>
                        <td>{{ $service->capacity }}</td>
                        <td>{{ currency($service->fixed) }}</td>
                       <!--  <td>{{ distance($service->distance) }}</td>
                        <td>{{ currency($service->price) }}</td>
                        <td>{{ currency($service->minute) }}</td>
                        @if($service->calculator == 'DISTANCEHOUR') 
                        <td>{{ currency($service->hour) }}</td>
                        @else
                        <td>No Hour Price</td>
                        @endif -->
                        <td>@lang('servicetypes.'.$service->calculator)</td>
                        <td>
                            @if($service->image) 
                                <img src="{{$service->image}}" style="height: 50px" >
                            @else
                                N/A
                            @endif
                        </td>
                        <td><button class="btn btn-warning open_modal" data-pid="{{ $service->id }}"> View </button></td>
                        <td><a href="{{route('admin.servicerentalhourpackage.index')}}?service_type_id={{$service->id}}" class="btn btn-primary" data-pid="{{ $service->id }}"> Rental </a></td>
                        <td>
                            <form action="{{ route('admin.service.destroy', $service->id) }}" method="POST">
                                {{ csrf_field() }}
                                {{ method_field('DELETE') }}
                                @if( Setting::get('demo_mode') == 0)
                                <a href="{{ route('admin.service.edit', $service->id) }}" class="btn btn-info btn-block">
                                    <i class="fa fa-pencil"></i> Edit
                                </a>
                                <!-- <button class="btn btn-danger btn-block" onclick="return confirm('Are you sure?')">
                                    <i class="fa fa-trash"></i> Delete
                                </button> -->
                                @endif
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Service Name</th>
                        <th>Provider Name</th>
                        <th>Capacity</th>
                        <th>Base Price</th>
                       <!--  <th>Base Distance</th>
                        <th>Distance Price</th>
                        <th>Time Price</th>
                        <th>Hour Price</th> -->
                        <th>Price Calculation</th>
                        <th>Service Image</th>
                         <th>Time Based Price</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
            </table>
        </div>
         <div class="container"> 

          <!-- Modal -->
          <div class="modal fade" id="myModal" role="dialog">
            <div class="modal-dialog">
            
              <!-- Modal content-->
              <div class="modal-content">
                <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  <h4 class="modal-title">Time Based Price</h4>
                </div>
                <div class="modal-body">

                  <div id="table_active"> </div>

                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
              </div>
              
            </div>
          </div>
          
        </div>
    </div>
</div>

<script type="text/javascript" src="{{asset('main/vendor/jquery/jquery-1.12.3.min.js')}}"></script>

<script type="text/javascript">
$(document).ready(function()
{
    
    $(document).on('click', '.open_modal', function(e){  


         //$('#myModal').modal('show');

                 e.preventDefault();
                
                
                var sid = $(this).attr("data-pid") ;
 
         

          
                 $.ajax({
                    url: "/admin/timelist",
                    type:'GET',
                    data: {_token:"{{ csrf_token() }}", service:sid},
                    success: function(data) {                         
                        // if(data.status == 'success'){
                        //      alert(data.message);
                        // }else{
                        //     alert('Try again later');
                        // }
                             // alert(data);
                             var myTable = '';
                                // myTable += '<h4> FFFFFFFFF </h4>';
                          
                                
                                myTable += '<table class="table table-striped table-bordered dataTable" id="table-2">';
                                
                                  myTable += '<thead><tr><th>ID</th><th>Time</th> <th>Peak Price</th></tr> </thead>';
                                
                                  var i = 1;

                                    $.each(data, function (index, value) {
                                      // console.log(value);
                                        myTable += '<tr>';
                                        myTable += '<td>'+i+'</td>';

                                      $.each(value['time'], function (index1, value1) {

                                        myTable += '<td>'+value1['from_time'] +' to '+ value1['to_time']+'</td>';

                                     });

                                        myTable += '<td>'+value['peak_price']+'</td>';
                                       
    
                                        myTable += '</tr>';
                                        
                                        i++;


                                    });

                                 myTable += '</table>';


                                 $('#table_active').html(myTable);
                                
                                 $('#myModal').modal('show');


                    }
                });


          });

    });


</script>
@endsection
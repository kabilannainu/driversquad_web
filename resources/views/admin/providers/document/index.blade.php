@extends('admin.layout.base')

@section('title', 'Provider Documents ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
          @if(Setting::get('demo_mode') == 1)
                <div class="col-md-12" style="height:50px;color:red;">
                    <h1>** Demo Mode : No Permission to Edit and Delete.</h1>
                </div>
             @endif
            <h5 class="mb-1">@lang('admin.provides.type_allocation')</h5>
            <div class="row">
                <div class="col-xs-12">
                    @if($ProviderService->count() > 0)
                    <hr><h6>Allocated Services :  </h6>
                    <table class="table table-striped table-bordered dataTable">
                        <thead>
                            <tr>
                                <th>@lang('admin.provides.service_name')</th>
                                <th>@lang('admin.provides.service_number')</th>
                                <th>@lang('admin.provides.service_model')</th>
                                <th>@lang('admin.action')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ProviderService as $service)
                            <tr>
                                <td>{{ $service->service_type->name }}</td>
                                <td>{{ $service->service_number }}</td>
                                <td>{{ $service->service_model }}</td>
                                <td>
                                @if( Setting::get('demo_mode') == 0)
                                    <form action="{{ route('admin.provider.document.service', [$Provider->id, $service->id]) }}" method="POST">
                                        {{ csrf_field() }}
                                        {{ method_field('DELETE') }}
                                        <button class="btn btn-danger btn-large btn-block">Delete</a>
                                    </form>
                                     @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th>@lang('admin.provides.service_name')</th>
                                <th>@lang('admin.provides.service_number')</th>
                                <th>@lang('admin.provides.service_model')</th>
                                <th>@lang('admin.action')</th>
                            </tr>
                        </tfoot>
                    </table>
                    @endif
                    <hr>
                </div>
                <form action="{{ route('admin.provider.document.store', $Provider->id) }}" method="POST">
                    {{ csrf_field() }}
                    <div class="col-xs-3">
                        <select class="form-control input" name="service_type" required>
                            @forelse($ServiceTypes as $Type)
                            <option value="{{ $Type->id }}">{{ $Type->name }}</option>
                            @empty
                            <option>- Please Create a Service Type -</option>
                            @endforelse
                        </select>
                    </div>
                    <div class="col-xs-3">
                        <input type="text" required name="service_number" class="form-control" placeholder="Number (CY 98769)">
                    </div>
                    <div class="col-xs-3">
                        <input type="text" required name="service_model" class="form-control" placeholder="Model (Audi R8 - Black)">
                    </div>
                    @if( Setting::get('demo_mode') == 0)
                    <div class="col-xs-3">
                        <button class="btn btn-primary btn-block" type="submit">ADD</button>
                    </div>
                    @endif
                </form>
            </div>
        </div> 
        @if($Provider->documents->count() != $DriverDocuments->count()) 
            <div class="box box-block bg-white">
            
            <div class="form-group row">
                    <h2><label for="mobile" class="col-xs-12 col-form-label">Documents</label></h2>
            </div>
            <form action="{{ route('admin.provider.document.upload', $Provider->id) }}" method="POST" enctype="multipart/form-data">
                        {{ csrf_field() }}
            @foreach($DriverDocuments as $Document)
             @php($provider_document = \App\ProviderDocument::wheredocument_id($Document->id)->whereprovider_id($Provider->id)->first())
                @if(empty($provider_document))
                <div class="form-group row"> 
                <label for="mobile" class="col-xs-12 col-form-label"> {{$Document->name}}</label>
                    <div class="col-xs-10">
                        <span class="input-group-addon btn btn-default btn-file" style="width:100%;display: block;margin-bottom: 25px"  >
                         
                        <input type="file" name="document[{{$Document->id}}]" accept="application/pdf, image/*" value="" >
                        <input type="hidden" name="id[]" value="{{$Document->id}}">
                        </span>
                    </div>
                </div>
                @endif
          
            @endforeach
             <div class="form-group row " style="text-align: center;">
                    <button type="submit" class="btn btn-info">Upload</button>
                </div>
                </form>
            </div>
        @endif

        <div class="box box-block bg-white">
            <h5 class="mb-1">@lang('admin.provides.provider_documents')</h5>
            <table class="table table-striped table-bordered dataTable" id="table-2">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>@lang('admin.provides.document_type')</th>
                        <th>@lang('admin.status')</th>
                        <th>@lang('admin.action')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($Provider->documents as $Index => $Document)
                    <tr>
                        <td>{{ $Index + 1 }}</td>
                        <td>{{ $Document->document->name }}</td>
                        <td>{{ $Document->status }}</td>
                        <td>
                            <div class="input-group-btn">
                            @if( Setting::get('demo_mode') == 0)
                                <a href="{{ route('admin.provider.document.edit', [$Provider->id, $Document->id]) }}"><span class="btn btn-success btn-large">View</span></a>
                                <button class="btn btn-danger btn-large" form="form-delete">Delete</button>
                                <form action="{{ route('admin.provider.document.destroy', [$Provider->id, $Document->id]) }}" method="POST" id="form-delete">
                                    {{ csrf_field() }}
                                    {{ method_field('DELETE') }}
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>@lang('admin.provides.document_type')</th>
                        <th>@lang('admin.status')</th>
                        <th>@lang('admin.action')</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
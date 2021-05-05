@extends('dispatcher.layout.base')

@section('title', 'Users ')

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
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
           @if(Setting::get('demo_mode') == 1)
        <div class="col-md-12" style="height:50px;color:red;">
                    ** Demo Mode : No Permission to Edit and Delete.
                </div>
                @endif
            <h5 class="mb-1">
                @lang('admin.users.Users')
                @if(Setting::get('demo_mode', 0) == 1)
                <span class="pull-right">(*personal information hidden in demo)</span>
                @endif
            </h5> 
            <div class="col-md-12"> 
            <div class="col-md-3"> 
            </div>
            <div class="col-md-6">
            <form action="{{route('admin.user.index')}}" method="GET"> 
            <div class="col-md-6">
            <input type="text" class="form-control col-md-6" name="search" value="{{$search}}">
            </div>
            <div class="col-md-6"> 
            <button type="submit" class="btn btn-info col-md-6" ><span class="glyphicon glyphicon-search" style="font-size: 15px;"> Search</span></button>
            </div> 
            </form>
            </div>
            <div class="col-md-3" style="text-align: right;">
            <a href="{{ route('dispatcher.user.create') }}" style="margin-left: 1em;" class="btn btn-primary"><i class="fa fa-plus"></i> Add New User</a> 
            </div> 
            </div>
            <table class="table table-striped table-bordered dataTable" id="cus-table-2">
                <thead>
                    <tr>
                        <th>@lang('admin.id')</th>
                        <th>@lang('admin.first_name')</th>
                        <th>@lang('admin.last_name')</th>
                        <th>@lang('admin.email')</th>
                        <th>@lang('admin.mobile')</th>
                        <th>@lang('admin.users.Rating')</th>
                        <th>@lang('admin.users.Wallet_Amount')</th>
                        <th>@lang('admin.action')</th>
                    </tr>
                </thead>
                <tbody>@php($page_plus=$page-10)
                    @foreach($users as $index => $user)
                    <tr>
                        <td>{{ $index + 1 + $page_plus }}</td>
                        <td>{{ $user->first_name }}</td>
                        <td>{{ $user->last_name }}</td>
                        @if(Setting::get('demo_mode', 0) == 1)
                        <td>{{ substr($user->email, 0, 3).'****'.substr($user->email, strpos($user->email, "@")) }}</td>
                        @else
                        <td>{{ $user->email }}</td>
                        @endif
                        @if(Setting::get('demo_mode', 0) == 1)
                        <td>{{ substr($user->mobile, 0, 5).'****' }}</td>
                        @else
                        <td>{{ $user->mobile }}</td>
                        @endif
                        <td>{{ $user->rating }}</td>
                        <td>{{currency($user->wallet_balance)}}</td>
                        <td>
                            <form action="{{ route('dispatcher.user.destroy', $user->id) }}" method="POST">
                                {{ csrf_field() }}
                                <input type="hidden" name="_method" value="DELETE">
                                <a href="{{ route('dispatcher.user.request', $user->id) }}" class="btn btn-info"><i class="fa fa-search"></i> @lang('admin.History')</a>
                                @if( Setting::get('demo_mode') == 0)
                                <a href="{{ route('dispatcher.user.edit', $user->id) }}" class="btn btn-info"><i class="fa fa-pencil"></i> @lang('admin.edit')</a>
                                <button class="btn btn-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i> @lang('admin.delete')</button>
                                @endif
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th>@lang('admin.id')</th>
                        <th>@lang('admin.first_name')</th>
                        <th>@lang('admin.last_name')</th>
                        <th>@lang('admin.email')</th>
                        <th>@lang('admin.mobile')</th>
                        <th>@lang('admin.users.Rating')</th>
                        <th>@lang('admin.users.Wallet_Amount')</th>
                        <th>@lang('admin.action')</th>
                    </tr>
                </tfoot>
            </table> 
            {{$users->appends(['search' => $search])->links()}}
        </div>
    </div>
</div>
@endsection
<div class="row footer no-margin">
    <div class="container">
       <div class="col-md-4 col-sm-4 col-xs-12">
            <h5>{{ Setting::get('site_copyright', '&copy; '.date('Y').' Appoets') }}<h5>
        </div>
         <div class="col-md-4 col-sm-4 col-xs-12">
                <h5>@lang('provider.profile.connect_us')</h5>
                <ul class="social">
                    <li><a href="{{Setting::get('facebook_link','#')}}"><i class="fa fa-facebook"></i></a></li>
                    <li><a href="{{Setting::get('twitter_link','#')}}"><i class="fa fa-twitter"></i></a></li> 
                </ul>
            </div>
        <div class="col-md-4 col-sm-4 col-xs-12">
            <a href="{{Setting::get('store_link_ios','#')}}" class="app">
                <img src="{{asset('asset/img/appstore.png')}}">
            </a>
            <a href="{{Setting::get('store_link_android','#')}}" class="app">
                <img src="{{asset('asset/img/playstore.png')}}">
            </a>
        </div>
        
    </div>
</div>
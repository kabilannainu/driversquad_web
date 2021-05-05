@extends('admin.layout.base')

@section('title', 'Site Settings ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
			<h5>@lang('admin.setting.Site_Settings')</h5>

            <form class="form-horizontal" action="{{ route('admin.settings.store') }}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}


				<div class="form-group row">
					<label for="site_title" class="col-xs-2 col-form-label">@lang('admin.setting.Site_Name')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ Setting::get('site_title', 'Tranxit')  }}" name="site_title" required id="site_title" placeholder="Site Name">
					</div>
				</div>

				<div class="form-group row">
					<label for="site_logo" class="col-xs-2 col-form-label">@lang('admin.setting.Site_Logo')</label>
					<div class="col-xs-10">
						@if(Setting::get('site_logo')!='')
	                    <img style="height: 90px; margin-bottom: 15px;" src="{{ Setting::get('site_logo', asset('logo-black.png')) }}">
	                    @endif
						<input type="file" accept="image/*" name="site_logo" class="dropify form-control-file" id="site_logo" aria-describedby="fileHelp">
					</div>
				</div>


				<div class="form-group row">
					<label for="site_icon" class="col-xs-2 col-form-label">@lang('admin.setting.Site_Icon')</label>
					<div class="col-xs-10">
						@if(Setting::get('site_icon')!='')
	                    <img style="height: 90px; margin-bottom: 15px;" src="{{ Setting::get('site_icon') }}">
	                    @endif
						<input type="file" accept="image/*" name="site_icon" class="dropify form-control-file" id="site_icon" aria-describedby="fileHelp">
					</div>
				</div>

                <div class="form-group row">
                    <label for="tax_percentage" class="col-xs-2 col-form-label">@lang('admin.setting.Copyright_Content')</label>
                    <div class="col-xs-10">
                        <input class="form-control" type="text" value="{{ Setting::get('site_copyright', '&copy; '.date('Y').' Appoets') }}" name="site_copyright" id="site_copyright" placeholder="Site Copyright">
                    </div>
                </div>

				<div class="form-group row">
					<label for="store_link_android" class="col-xs-2 col-form-label">@lang('admin.setting.Playstore_link')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ Setting::get('store_link_android', '')  }}" name="store_link_android"  id="store_link_android" placeholder="Playstore link">
					</div>
				</div>

				<div class="form-group row">
					<label for="store_link_ios" class="col-xs-2 col-form-label">@lang('admin.setting.Appstore_Link')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ Setting::get('store_link_ios', '')  }}" name="store_link_ios"  id="store_link_ios" placeholder="Appstore link">
					</div>
				</div>

				<div class="form-group row">
					<label for="facebook_link_ios" class="col-xs-2 col-form-label">Facebook Link</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ Setting::get('facebook_link', '')  }}" name="facebook_link"  id="facebook_link" placeholder="Facebook link">
					</div>
				</div>
				<div class="form-group row">
					<label for="twiiter_link" class="col-xs-2 col-form-label">@lang('admin.setting.Appstore_Link')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ Setting::get('twitter_link', '')  }}" name="twitter_link"  id="twitter_link" placeholder="Twitter link">
					</div>
				</div>

				<div class="form-group row">
					<label for="provider_select_timeout" class="col-xs-2 col-form-label">@lang('admin.setting.Provider_Accept_Timeout')</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ Setting::get('provider_select_timeout', '60')  }}" name="provider_select_timeout" required id="provider_select_timeout" placeholder="Provider Timout">
					</div>
				</div>

				<div class="form-group row">
					<label for="provider_search_radius" class="col-xs-2 col-form-label">@lang('admin.setting.Provider_Search_Radius')</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ Setting::get('provider_search_radius', '100')  }}" name="provider_search_radius" required id="provider_search_radius" placeholder="Provider Search Radius">
					</div>
				</div>

				<div class="form-group row">
					<label for="sos_number" class="col-xs-2 col-form-label">@lang('admin.setting.SOS_Number')</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ Setting::get('sos_number', '911')  }}" name="sos_number" required id="sos_number" placeholder="SOS Number">
					</div>
				</div>

				<div class="form-group row">
					<label for="stripe_secret_key" class="col-xs-2 col-form-label"> Manual Assigning </label>
					<div class="col-xs-10">
						<div class="float-xs-left mr-1"><input @if(Setting::get('manual_request') == 1) checked  @endif  name="manual_request" type="checkbox" class="js-switch" data-color="#43b968"></div>
					</div>
				</div>

				<div class="form-group row">
					<label for="broadcast_request" class="col-xs-2 col-form-label"> BroadCast Assigning </label>
					<div class="col-xs-10">
						<div class="float-xs-left mr-1"><input @if(Setting::get('broadcast_request') == 1) checked  @endif  name="broadcast_request" type="checkbox" class="js-switch" data-color="#43b968"></div>
					</div>
				</div>

				<div class="form-group row">
					<label for="track_distance" class="col-xs-2 col-form-label"> Track Live Travel Distance </label>
					<div class="col-xs-10">
						<div class="float-xs-left mr-1"><input @if(Setting::get('track_distance') == 1) checked  @endif  name="track_distance" type="checkbox" class="js-switch" data-color="#43b968"></div>
					</div>
				</div>

				<div class="form-group row">
					<label for="contact_number" class="col-xs-2 col-form-label">@lang('admin.setting.Contact_Number')</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ Setting::get('contact_number', '911')  }}" name="contact_number" required id="contact_number" placeholder="Contact Number">
					</div>
				</div>

				<div class="form-group row">
					<label for="contact_email" class="col-xs-2 col-form-label">@lang('admin.setting.Contact_Email')</label>
					<div class="col-xs-10">
						<input class="form-control" type="email" value="{{ Setting::get('contact_email', '')  }}" name="contact_email" required id="contact_email" placeholder="Contact Email">
					</div>
				</div>

				<div class="form-group row">
					<label for="social_login" class="col-xs-2 col-form-label">@lang('admin.setting.Social_Login')</label>
					<div class="col-xs-10">
						<select class="form-control" id="social_login" name="social_login">
							<option value="1" @if(Setting::get('social_login', 0) == 1) selected @endif>@lang('admin.Enable')</option>
							<option value="0" @if(Setting::get('social_login', 0) == 0) selected @endif>@lang('admin.Disable')</option>
						</select>
					</div>
				</div>

				<div class="form-group row">
					<label for="map_key" class="col-xs-2 col-form-label">@lang('admin.setting.map_key')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ Setting::get('map_key')  }}" name="map_key" required id="map_key" placeholder="@lang('admin.setting.map_key')">
					</div>
				</div>

				<div class="form-group row">
					<label for="android_map_key" class="col-xs-2 col-form-label">@lang('admin.setting.android_map_key')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ Setting::get('android_map_key')  }}" name="android_map_key" required id="android_map_key" placeholder="@lang('admin.setting.android_map_key')">
					</div>
				</div>

				<div class="form-group row">
					<label for="ios_map_key" class="col-xs-2 col-form-label">@lang('admin.setting.ios_map_key')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ Setting::get('ios_map_key')  }}" name="ios_map_key" required id="ios_map_key" placeholder="@lang('admin.setting.ios_map_key')">
					</div>
				</div>

				<div class="form-group row">
					<label for="fb_app_version" class="col-xs-2 col-form-label">@lang('admin.setting.fb_app_version')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ Setting::get('fb_app_version')  }}" name="fb_app_version" required id="fb_app_version" placeholder="@lang('admin.setting.fb_app_version')">
					</div>
				</div>

				<div class="form-group row">
					<label for="fb_app_id" class="col-xs-2 col-form-label">@lang('admin.setting.fb_app_id')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ Setting::get('fb_app_id')  }}" name="fb_app_id" required id="fb_app_id" placeholder="@lang('admin.setting.fb_app_id')">
					</div>
				</div>

				<div class="form-group row">
					<label for="fb_app_secret" class="col-xs-2 col-form-label">@lang('admin.setting.fb_app_secret')</label>
					<div class="col-xs-10">
						<input class="form-control" type="text" value="{{ Setting::get('fb_app_secret')  }}" name="fb_app_secret" required id="fb_app_secret" placeholder="@lang('admin.setting.fb_app_secret')">
					</div>
				</div>

				<div class="form-group row">
					<label for="rental_content" class="col-xs-2 col-form-label">@lang('admin.setting.rental_content')</label>
					<div class="col-xs-10">
						<textarea name="rental_content" id="rental_content" class="form-control">{{ Setting::get('rental_content')  }}</textarea>
						
					</div>
				</div>


				<div class="form-group row">
					<label for="outstation_content" class="col-xs-2 col-form-label">@lang('admin.setting.outstation_content')</label>
					<div class="col-xs-10">
						<textarea name="outstation_content" id="outstation_content" class="form-control">{{ Setting::get('outstation_content')  }}</textarea>
						
					</div>
				</div>

				<div class="form-group row">
					<label for="limit_message" class="col-xs-2 col-form-label">@lang('admin.setting.limit_message')</label>
					<div class="col-xs-10">
						<textarea name="limit_message" id="limit_message" class="form-control">{{ Setting::get('limit_message')  }}</textarea>
						
					</div>
				</div>

				<div class="form-group row">
					<label for="landing_content" class="col-xs-2 col-form-label">@lang('admin.setting.landing_content')</label>
					<div class="col-xs-10">
						<textarea name="landing_content" id="landing_content" class="form-control">{{ Setting::get('landing_content')  }}</textarea>
						
					</div>
				</div>

				<div class="form-group row">
					<label for="msg91_authkey" class="col-xs-2 col-form-label">@lang('admin.setting.msg91_authkey')</label>
					<div class="col-xs-10">
						<textarea name="msg91_authkey" id="msg91_authkey" class="form-control">{{ Setting::get('msg91_authkey')  }}</textarea>
						
					</div>
				</div>

				<div class="form-group row">
					<label for="fb_app_secret" class="col-xs-2 col-form-label">IOS Review</label>
					<div class="col-xs-10">
						<input class="form-control" type="number" value="{{ Setting::get('ios_review')  }}" name="ios_review" min="0" required id="ios_driver_version" placeholder="@lang('admin.setting.ios_review')">
					</div>
				</div>
                  

   <!--               <div class="form-group row">
					<label for="offers" class="col-xs-2 col-form-label">@lang('admin.setting.offers')</label>
					<div class="col-xs-10">
						<textarea name="offers" id="offers" class="form-control">{{ Setting::get('offers')  }}</textarea>
						
					</div>
				</div>
                   -->
                  
      
				<div class="form-group row">
					<label for="zipcode" class="col-xs-2 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">@lang('admin.setting.Update_Site_Settings')</button>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>
@endsection


@section('styles')
<style type="text/css">
    #map {
        height: 100%;
        min-height: 400px; 
    }
    
    .controls {
        border: 1px solid transparent;
        border-radius: 2px 0 0 2px;
        box-sizing: border-box;
        -moz-box-sizing: border-box;
        height: 32px;
        outline: none;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        margin-bottom: 10px;
    }

    #pac-input {
        background-color: #fff;
        font-family: Roboto;
        font-size: 15px;
        font-weight: 300;
        padding: 0 11px 0 13px;
        text-overflow: ellipsis;
        width: 100%;
    }

    #pac-input:focus {
        border-color: #4d90fe;
    }

    #bar {
        width: 240px;
        background-color: rgba(255, 255, 255, 0.75);
        margin: 8px;
        padding: 4px;
        border-radius: 4px;
  	}
</style>
@endsection

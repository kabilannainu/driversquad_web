<div class="site-sidebar">
	<div class="custom-scroll custom-scroll-light">
		<ul class="sidebar-menu">
			<li class="menu-title">Dispatcher Dashboard</li>
			
			{{--<li>
				<a href="{{ route('dispatcher.dashboard') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-anchor"></i></span>
					<span class="s-text">@lang('admin.include.dashboard')</span>
				</a>
			</li>--}}

			<li>
				<a href="{{ route('dispatcher.index') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-target"></i></span>
					<span class="s-text">Dispatcher Panel</span>
				</a>
			</li>


			<li>
				<a href="{{ route('dispatcher.heatmap') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-map"></i></span>
					<span class="s-text">@lang('admin.include.heat_map')</span>
				</a>
			</li>

			{{--<li class="menu-title">@lang('admin.include.members')</li>
			<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="ti-crown"></i></span>
					<span class="s-text">@lang('admin.include.users')</span>
				</a>
				<ul>
					<li><a href="{{ route('dispatcher.user.index') }}">@lang('admin.include.list_users')</a></li>
					<li><a href="{{ route('dispatcher.user.create') }}">@lang('admin.include.add_new_user')</a></li>
				</ul>
			</li>
			<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="ti-car"></i></span>
					<span class="s-text">@lang('admin.include.providers')</span>
				</a>
				<ul>
					<li><a href="{{ route('dispatcher.provider.index') }}">@lang('admin.include.list_providers')</a></li>
					<li><a href="{{ route('dispatcher.provider.create') }}">@lang('admin.include.add_new_provider')</a></li>
				</ul>
			</li>--}}
			<!-- <li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="ti-crown"></i></span>
					<span class="s-text">@lang('admin.include.dispatcher')</span>
				</a>
				<ul>
					<li><a href="{{ route('dispatcher.dispatch-manager.index') }}">@lang('admin.include.list_dispatcher')</a></li>
					<li><a href="{{ route('dispatcher.dispatch-manager.create') }}">@lang('admin.include.add_new_dispatcher')</a></li>
				</ul>
			</li> -->
			{{--<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="ti-crown"></i></span>
					<span class="s-text">@lang('admin.include.fleet_owner')</span>
				</a>
				<ul>
					<li><a href="{{ route('dispatcher.fleet.index') }}">@lang('admin.include.list_fleets')</a></li>
					<li><a href="{{ route('dispatcher.fleet.create') }}">@lang('admin.include.add_new_fleet_owner')</a></li>
				</ul>
			</li>
			<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="ti-crown"></i></span>
					<span class="s-text">@lang('admin.include.account_manager')</span>
				</a>
				<ul>
					<li><a href="{{ route('dispatcher.account-manager.index') }}">@lang('admin.include.list_account_managers')</a></li>
					<li><a href="{{ route('dispatcher.account-manager.create') }}">@lang('admin.include.add_new_account_manager')</a></li>
				</ul>
			</li>--}}
			{{--<li class="menu-title">@lang('admin.include.accounts')</li>
			<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="ti-crown"></i></span>
					<span class="s-text">@lang('admin.include.statements')</span>
				</a>
				<ul>
					<li><a href="{{ route('dispatcher.ride.statement') }}">@lang('admin.include.overall_ride_statments')</a></li>
					<li><a href="{{ route('dispatcher.ride.statement.provider') }}">@lang('admin.include.provider_statement')</a></li>
					<li><a href="{{ route('dispatcher.ride.statement.today') }}">@lang('admin.include.daily_statement')</a></li>
					<li><a href="{{ route('dispatcher.ride.statement.monthly') }}">@lang('admin.include.monthly_statement')</a></li>
					<li><a href="{{ route('dispatcher.ride.statement.yearly') }}">@lang('admin.include.yearly_statement')</a></li>
				</ul>
			</li>--}}
			<li class="menu-title">@lang('admin.include.details')</li>
			<li>
				<a href="{{ route('dispatcher.map.index') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-map-alt"></i></span>
					<span class="s-text">@lang('admin.include.map')</span>
				</a>
			</li> 
			<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="ti-view-grid"></i></span>
					<span class="s-text">@lang('admin.include.ratings') &amp; @lang('admin.include.reviews')</span>
				</a>
				<ul>
					<li><a href="{{ route('dispatcher.user.review') }}">@lang('admin.include.user_ratings')</a></li>
					<li><a href="{{ route('dispatcher.provider.review') }}">@lang('admin.include.provider_ratings')</a></li>
				</ul>
			</li>
			<li class="menu-title">@lang('admin.include.requests')</li>
			<li>
				<a href="{{ route('dispatcher.requests.index') }}" class="waves-effect  waves-light">
					<span class="s-icon"><i class="ti-infinite"></i></span>
					<span class="s-text">@lang('admin.include.request_history')</span>
				</a>
			</li>
			<li>
				<a href="{{ route('dispatcher.requests.scheduled') }}" class="waves-effect  waves-light">
					<span class="s-icon"><i class="ti-palette"></i></span>
					<span class="s-text">@lang('admin.include.scheduled_rides')</span>
				</a>
			</li> 
			{{--<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="ti-layout-tab"></i></span>
					<span class="s-text">@lang('admin.include.documents')</span>
				</a>
				<ul>
					<li><a href="{{ route('dispatcher.document.index') }}">@lang('admin.include.list_documents')</a></li>
					<li><a href="{{ route('dispatcher.document.create') }}">@lang('admin.include.add_new_document')</a></li>
				</ul>
			</li> --}}
			
			<li class="menu-title">@lang('admin.include.payment_details')</li>
			<li>
				<a href="{{ route('dispatcher.payment') }}" class="waves-effect  waves-light">
					<span class="s-icon"><i class="ti-infinite"></i></span>
					<span class="s-text">@lang('admin.include.payment_history')</span>
				</a>
			</li> 
			{{--<li class="menu-title">@lang('admin.include.others')</li>
			<li>
				<a href="{{ route('dispatcher.privacy') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-help"></i></span>
					<span class="s-text">@lang('admin.include.privacy_policy')</span>
				</a>
			</li>

				<li>
				<a href="{{ route('dispatcher.terms') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-help"></i></span>
					<span class="s-text">@lang('admin.include.terms')</span>
				</a>
			</li>

							<li>
				<a href="{{ route('dispatcher.about_us') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-help"></i></span>
					<span class="s-text">@lang('admin.include.about_us')</span>
				</a>
			</li>
			<li>
				<a href="{{ route('dispatcher.help') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-help"></i></span>
					<span class="s-text">@lang('admin.include.help')</span>
				</a>
			</li>

			<li>
				<a href="{{ route('dispatcher.offers') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-help"></i></span>
					<span class="s-text">@lang('admin.setting.offers')</span>
				</a>
			</li>
			<li>
				<a href="{{ route('dispatcher.push') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-smallcap"></i></span>
					<span class="s-text">@lang('admin.include.custom_push')</span>
				</a>
			</li>
			<li>
				<a href="{{route('dispatcher.translation') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-smallcap"></i></span>
					<span class="s-text">@lang('admin.include.translations')</span>
				</a>
			</li>--}}
			
			<li class="menu-title">Account</li>
			<li>
				<a href="{{ route('dispatcher.profile') }}" class="waves-effect  waves-light">
					<span class="s-icon"><i class="ti-user"></i></span>
					<span class="s-text">Account Settings</span>
				</a>
			</li>
			<li>
				<a href="{{ route('dispatcher.password') }}" class="waves-effect  waves-light">
					<span class="s-icon"><i class="ti-exchange-vertical"></i></span>
					<span class="s-text">Change Password</span>
				</a>
			</li>
			<li class="compact-hide">
				<a href="{{ url('/dispatcher/logout') }}"
                            onclick="event.preventDefault();
                                     document.getElementById('logout-form').submit();">
					<span class="s-icon"><i class="ti-power-off"></i></span>
					<span class="s-text">Logout</span>
                </a>

                <form id="logout-form" action="{{ url('/dispatcher/logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
			</li>
			
		</ul>
	</div>
</div>
<div class="site-sidebar">
	<div class="custom-scroll custom-scroll-light">
		<ul class="sidebar-menu">
			<?php
					$dashboard_view = check_permission('dashboard_view',Auth::user()->role_id);
							if($dashboard_view==1)  {
				?>
			<li class="menu-title">@lang('admin.include.admin_dashboard')</li>
			<li>
				<a href="{{ route('admin.dashboard') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-anchor"></i></span>
					<span class="s-text">@lang('admin.include.dashboard')</span>
				</a>
			</li>
			<?php
				 }
				$dispatcher_view = check_permission('dispatcher_view',Auth::user()->role_id);
				if($dispatcher_view==1)  {
			 ?>
			<li>
				<a href="{{ route('admin.dispatcher.index') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-target"></i></span>
					<span class="s-text">@lang('admin.include.dispatcher_panel')</span>
				</a>
			</li>
			<?php
			 } 
			 $heatmap_view = check_permission('heatmap_view',Auth::user()->role_id);
			 if($heatmap_view==1)  {
			 ?>
			<li>
				<a href="{{ route('admin.heatmap') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-map"></i></span>
					<span class="s-text">@lang('admin.include.heat_map')</span>
				</a>
			</li>
			<?php
			 }
			 ?>
			<li class="menu-title">@lang('admin.include.members')</li>
			<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="ti-crown"></i></span>
					<span class="s-text">@lang('admin.include.users')</span>
				</a>
				<ul>
				<?php
					 $user_view = check_permission('user_view',Auth::user()->role_id);
					 if($user_view==1)  {
					 ?>
					<li><a href="{{ route('admin.user.index') }}">@lang('admin.include.list_users')</a></li>
					<?php } 
					$user_create = check_permission('user_create',Auth::user()->role_id);
					 if($user_create==1)  {
					 ?>
					<li><a href="{{ route('admin.user.create') }}">@lang('admin.include.add_new_user')</a></li>
					<?php } ?>
				</ul>
			</li>
			<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="ti-car"></i></span>
					<span class="s-text">@lang('admin.include.providers')</span>
				</a>
				<ul>
					<?php
					 $provider_view = check_permission('provider_view',Auth::user()->role_id);
					 if($provider_view==1)  {
					 ?>
					<li><a href="{{ route('admin.provider.index') }}">@lang('admin.include.list_providers')</a></li>
					<?php }
					$provider_create = check_permission('provider_create',Auth::user()->role_id);
					if($provider_create==1)  {
					 ?>
					<li><a href="{{ route('admin.provider.create') }}">@lang('admin.include.add_new_provider')</a></li>
					<?php } ?>
				</ul>
			</li>
			@if(Auth::user()->role_id==0)
			<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">

					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="ti-crown"></i></span>
					
					<span class="s-text">@lang('admin.include.sub_admin')</span>
					
				</a>
				<ul>
				<?php
					$subadmin_view = Auth::user()->role_id;
					if($subadmin_view ==0)
					{
				?>
					<li><a href="{{ route('admin.subadmin.index') }}">@lang('admin.include.list_sub_admin')</a></li>
					<?php }
					$subadmin_create = check_permission('subadmin_create',Auth::user()->role_id);
					if($subadmin_create ==1)
					{
					 ?>
					<li><a href="{{ route('admin.subadmin.create') }}">@lang('admin.include.add_sub_admin')</a></li>
					<?php } ?>
				</ul>
			</li>
			@endif
			<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="ti-crown"></i></span>
					<span class="s-text">@lang('admin.include.dispatcher')</span>
				</a>
				<ul>
					<?php
					 $dispatcher_view = check_permission('dispatcher_view',Auth::user()->role_id);
					 if($dispatcher_view==1)  {
					 ?>
					<li><a href="{{ route('admin.dispatch-manager.index') }}">@lang('admin.include.list_dispatcher')</a></li>
					<?php 
					 } 
					 $dispatcher_create = check_permission('dispatcher_create',Auth::user()->role_id);
					 if($dispatcher_create==1)  {
					 ?>
					<li><a href="{{ route('admin.dispatch-manager.create') }}">@lang('admin.include.add_new_dispatcher')</a></li>

					<?php } ?>
				</ul>
			</li>
			<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="ti-crown"></i></span>
					<span class="s-text">@lang('admin.include.fleet_owner')</span>
				</a>
				<ul>
					<?php
						$fleet_view = check_permission('fleet_view',Auth::user()->role_id);
						if($fleet_view ==1)
						{
					?>
					<li><a href="{{ route('admin.fleet.index') }}">@lang('admin.include.list_fleets')</a></li>
					<?php }
					$fleet_create = check_permission('fleet_create',Auth::user()->role_id);
					if($fleet_create ==1)
					{

					  ?>
					<li><a href="{{ route('admin.fleet.create') }}">@lang('admin.include.add_new_fleet_owner')</a></li>
					<?php } ?>
				</ul>
			</li>
			<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="ti-crown"></i></span>
					<span class="s-text">@lang('admin.include.account_manager')</span>
				</a>
				<ul>
					<?php
						$account_view = check_permission('account_view',Auth::user()->role_id);
						if($account_view ==1)
						{
					?>
					<li><a href="{{ route('admin.account-manager.index') }}">@lang('admin.include.list_account_managers')</a></li>
					<?php } 
						$account_create = check_permission('account_create',Auth::user()->role_id);
						if($account_create ==1)
						{
					 ?>
					<li><a href="{{ route('admin.account-manager.create') }}">@lang('admin.include.add_new_account_manager')</a></li>
					<?php } ?>
				</ul>
			</li>
			<li class="menu-title">@lang('admin.include.accounts')</li>
			<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="ti-crown"></i></span>
					<span class="s-text">@lang('admin.include.statements')</span>
				</a>
				<ul>
					
					<?php
						 $overall_statement = check_permission('overall_statement',Auth::user()->role_id);
						 if($overall_statement ==1)
						{
					?>
					<li><a href="{{ route('admin.ride.statement') }}">@lang('admin.include.overall_ride_statments')</a></li>

					<?php
				}
						 $statement_provider = check_permission('statement_provider',Auth::user()->role_id);
						  if($statement_provider ==1)
						{

					?>

					<li><a href="{{ route('admin.ride.statement.provider') }}">@lang('admin.include.provider_statement')</a></li>

					<?php
					}
					$statement_today = check_permission('statement_today',Auth::user()->role_id);
						  if($statement_today ==1)
					{
					?>

					<li><a href="{{ route('admin.ride.statement.today') }}">@lang('admin.include.daily_statement')</a></li>
						
					<?php
					}
					$statement_monthly = check_permission('statement_monthly',Auth::user()->role_id);
					if($statement_monthly ==1)
					{
					?>		

					<li><a href="{{ route('admin.ride.statement.monthly') }}">@lang('admin.include.monthly_statement')</a></li>

					<?php
					}
						 $statement_yearly = check_permission('statement_yearly',Auth::user()->role_id);
					if($overall_statement ==1)
					{
					?>
					<li><a href="{{ route('admin.ride.statement.yearly') }}">@lang('admin.include.yearly_statement')</a></li>
					<?php } ?>
				</ul>
			</li>
			<?php
				 $roles = Auth::user()->role_id;
			     if($roles == 0)
				 {
			?>
			<li>
				<a href="{{ route('admin.roles.index') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-view-grid"></i></span>
					<span class="s-text">@lang('admin.include.permissions')</span>
				</a>
			</li>
			<?php } ?>

			<li class="menu-title">@lang('admin.include.details')</li>
			<?php
				 $map_view = check_permission('map_view',Auth::user()->role_id);
				 if($map_view ==1)
				 {
			?>
			<li>
				<a href="{{ route('admin.map.index') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-map-alt"></i></span>
					<span class="s-text">@lang('admin.include.map')</span>
				</a>
			</li>
			<?php } $geo_fencing_view = check_permission('geo_fencing_view',Auth::user()->role_id);
				 if($geo_fencing_view ==1)
				 { ?>

			<li>
				<a href="{{ route('admin.geo-fencing.index') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-map-alt"></i></span>
					<span class="s-text">@lang('admin.include.goe_fencing')</span>
				</a>
			</li>
	       <?php	}	?>

			<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="ti-view-grid"></i></span>
					<span class="s-text">@lang('admin.include.ratings') &amp; @lang('admin.include.reviews')</span>
				</a>
				<ul>
					<?php
						 $user_review = check_permission('user_review',Auth::user()->role_id);
						 if($user_review ==1)
						 {
					?>
					<li><a href="{{ route('admin.user.review') }}">@lang('admin.include.user_ratings')</a></li>
					<?php } 
						$provider_review = check_permission('provider_review',Auth::user()->role_id);
						if($provider_review==1) {
					 ?>
					<li><a href="{{ route('admin.provider.review') }}">@lang('admin.include.provider_ratings')</a></li>
					<?php } ?>
				</ul>
			</li>
			<li class="menu-title">@lang('admin.include.requests')</li>
			<?php 
				$requests_index = check_permission('requests_index',Auth::user()->role_id);
				if($requests_index==1) {
			?>
			<li>
				<a href="{{ route('admin.requests.index') }}" class="waves-effect  waves-light">
					<span class="s-icon"><i class="ti-infinite"></i></span>
					<span class="s-text">@lang('admin.include.request_history')</span>
				</a>
			</li>
			<?php }
				$requests_scheduled = check_permission('requests_scheduled',Auth::user()->role_id);
				if($requests_scheduled==1) {	
			 ?>
			<li>
				<a href="{{ route('admin.requests.scheduled') }}" class="waves-effect  waves-light">
					<span class="s-icon"><i class="ti-palette"></i></span>
					<span class="s-text">@lang('admin.include.scheduled_rides')</span>
				</a>
			</li>
			<?php } ?>
			<li class="menu-title">@lang('admin.include.general')</li>
			<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="ti-view-grid"></i></span>
					<span class="s-text">@lang('admin.include.service_types')</span>
				</a>
				<ul>
					<?php 
						 $service = check_permission('service',Auth::user()->role_id);
						 if($service ==1) {
					?>
					<li><a href="{{ route('admin.service.index') }}">@lang('admin.include.list_service_types')</a></li>

					<?php }
					 $service_create = check_permission('service_create',Auth::user()->role_id);
					 if($service_create ==1) {
					 ?>
					<li><a href="{{ route('admin.service.create') }}">@lang('admin.include.add_new_service_type')</a></li>

					<?php } ?>

					<li><a href="{{ route('admin.time.index') }}">List Time</a></li>
					<li><a href="{{ route('admin.time.create') }}">Add Time</a></li>

				</ul>
			</li>

			{{--<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="fa fa-flag"></i></span>
					<span class="s-text">Rental Package Type</span>
				</a>
				<ul>
					<li><a href="{{ route('admin.packagetype.index') }}">List Package Type</a></li>
					<li><a href="{{ route('admin.packagetype.create') }}">Add Package Type</a></li>
				</ul>
			</li>--}}
			
			<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>

					<span class="s-icon"><i class="ti-layout-tab"></i></span>
					<span class="s-text">@lang('admin.include.documents')</span>
				</a>
				<ul>
					<?php 
						 $document = check_permission('document',Auth::user()->role_id);
						 if($document ==1) {
					?>
					<li><a href="{{ route('admin.document.index') }}">@lang('admin.include.list_documents')</a></li>
					<?php }
					 $document_create = check_permission('document_create',Auth::user()->role_id);
					 if($document_create ==1) {
					 ?>
					<li><a href="{{ route('admin.document.create') }}">@lang('admin.include.add_new_document')</a></li>

					<?php } ?>
				</ul>
			</li>

			<li class="with-sub">
				<a href="#" class="waves-effect  waves-light">
					<span class="s-caret"><i class="fa fa-angle-down"></i></span>
					<span class="s-icon"><i class="ti-layout-tab"></i></span>
					<span class="s-text">@lang('admin.include.promocodes')</span>
				</a>
				<ul>
					<?php 
						 $promocode = check_permission('promocode',Auth::user()->role_id);
						 if($promocode ==1) {
					?>
					<li><a href="{{ route('admin.promocode.index') }}">@lang('admin.include.list_promocodes')</a></li>
					<?php }
					 $promocode_create = check_permission('promocode_create',Auth::user()->role_id);
					 if($promocode_create ==1) {
					 ?>
					<li><a href="{{ route('admin.promocode.create') }}">
					@lang('admin.include.add_new_promocode')</a></li>
					<?php } ?>
				</ul>
			</li>
			
			<li class="menu-title">@lang('admin.include.payment_details')</li>
			<?php 
				 $payment = check_permission('payment',Auth::user()->role_id);
				 if($payment ==1) {
			?>
			<li>
				<a href="{{ route('admin.payment') }}" class="waves-effect  waves-light">
					<span class="s-icon"><i class="ti-infinite"></i></span>
					<span class="s-text">@lang('admin.include.payment_history')</span>
				</a>
			</li>
			<?php }
					 $payment_settings = check_permission('payment_settings',Auth::user()->role_id);
					 if($payment_settings ==1) {
					 ?>
			<li>
				<a href="{{ route('admin.settings.payment') }}" class="waves-effect  waves-light">
					<span class="s-icon"><i class="ti-money"></i></span>
					<span class="s-text">@lang('admin.include.payment_settings')</span>
				</a>
			</li>
			<?php } ?>
			<li class="menu-title">@lang('admin.include.settings')</li>
			<?php 
				 $settings = check_permission('settings',Auth::user()->role_id);
				 if($settings ==1) {
			?>
			<li>
				<a href="{{ route('admin.settings') }}" class="waves-effect  waves-light">
					<span class="s-icon"><i class="ti-settings"></i></span>
					<span class="s-text">@lang('admin.include.site_settings')</span>
				</a>
			</li>
			<?php } ?>
			
			<li class="menu-title">@lang('admin.include.others')</li>
			<?php
			  $privacy = check_permission('privacy',Auth::user()->role_id);
			  if($privacy ==1) {
			?>
			<li>
				<a href="{{ route('admin.privacy') }}" class="waves-effect waves-light">

					<span class="s-icon"><i class="ti-help"></i></span>
					<span class="s-text">@lang('admin.include.privacy_policy')</span>
				</a>
			</li>
			<?php } ?>
            <?php
			  $terms = check_permission('terms',Auth::user()->role_id);
			  if($terms ==1) {
			?>
				<li>
				<a href="{{ route('admin.terms') }}" class="waves-effect waves-light">

					<span class="s-icon"><i class="ti-help"></i></span>
					<span class="s-text">@lang('admin.include.terms')</span>
				</a>
			</li>
			<?php }  $about_us = check_permission('about_us',Auth::user()->role_id);
			  if($about_us ==1) { ?>

			<li>
				<a href="{{ route('admin.about_us') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-help"></i></span>
					<span class="s-text">@lang('admin.include.about_us')</span>
				</a>
			</li>
				<?php }
					 $help = check_permission('help',Auth::user()->role_id);
				 if($help ==1) {

				?>
			<li>
				<a href="{{ route('admin.help') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-help"></i></span>
					<span class="s-text">@lang('admin.include.help')</span>
				</a>
			</li>
			<?php }  $faq = check_permission('faq',Auth::user()->role_id);
				 if($faq ==1) {
             ?>

			<li>
				<a href="{{ route('admin.faq') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-help"></i></span>
					<span class="s-text">@lang('admin.include.faq')</span>
				</a>
			</li>
			<?php } $offers = check_permission('offers',Auth::user()->role_id);
				 if($offers ==1) {
             ?>

			<li>
				<a href="{{ route('admin.offers') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-help"></i></span>
					<span class="s-text">@lang('admin.setting.offers')</span>
				</a>
			</li>

			<?php 
		    }
			$send_push = check_permission('send_push',Auth::user()->role_id);
			 if($send_push ==1) {
			?>

			<li>
				<a href="{{ route('admin.push') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-smallcap"></i></span>
					<span class="s-text">@lang('admin.include.custom_push')</span>
				</a>
			</li>
			<?php }
			$translation = Auth::user()->role_id;
			 if($translation == 0) {
			 ?>
			<li>
				<a href="{{route('admin.translation') }}" class="waves-effect waves-light">
					<span class="s-icon"><i class="ti-smallcap"></i></span>
					<span class="s-text">@lang('admin.include.translations')</span>
				</a>
			</li>
			<?php }
			
			 ?>
			<li class="menu-title">@lang('admin.include.account')</li>
			<?php 
			$profile = check_permission('profile',Auth::user()->role_id);
			 if($profile ==1) {
			?>

			<li>
				<a href="{{ route('admin.profile') }}" class="waves-effect  waves-light">
					<span class="s-icon"><i class="ti-user"></i></span>
					<span class="s-text">@lang('admin.include.account_settings')</span>
				</a>
			</li>
			<?php }
			     $password = check_permission('password',Auth::user()->role_id);
			      if($password ==1) {
			 ?>
			<li>
				<a href="{{ route('admin.password') }}" class="waves-effect  waves-light">
					<span class="s-icon"><i class="ti-exchange-vertical"></i></span>
					<span class="s-text">@lang('admin.include.change_password')</span>
				</a>
			</li>
			<?php } ?>
			<li class="compact-hide">
				<a href="{{ url('/admin/logout') }}"
                            onclick="event.preventDefault();
                                     document.getElementById('logout-form').submit();">
					<span class="s-icon"><i class="ti-power-off"></i></span>
					<span class="s-text">@lang('admin.include.logout')</span>
                </a>

                <form id="logout-form" action="{{ url('/admin/logout') }}" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
			</li>
			
		</ul>
	</div>
</div>
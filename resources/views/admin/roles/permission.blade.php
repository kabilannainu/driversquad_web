@extends('admin.layout.base')

@section('title', 'Permission ')

@section('content')

<div class="content-area py-1">
    <div class="container-fluid">
    	<div class="box box-block bg-white">
            <a href="{{ route('admin.roles.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

			<h5 style="margin-bottom: 2em;">@lang('admin.roles.set_permission')</h5>

            <form class="form-horizontal" action="{{url('admin/roles/permission/store',$id)}}" method="POST" enctype="multipart/form-data" role="form">
            	{{csrf_field()}}

            	<input type="hidden" name="role_id" value="{{$id}}">
				<div class="form-group row">
					<div class="col-xs-10">
						<h5 style="margin-bottom: 12px;">@lang('admin.roles.dashboard')</h5>
					</div>
					<div class="col-xs-12">
						<div class="col-xs-4">
								<?php
								 $dashboard_view = check_permission('dashboard_view',$id);
								 ?>
								<input <?php if($dashboard_view==1) { echo 'checked=checked'; } ?> style="float: left;" type="checkbox" value="admin.dashboard" name="roles[dashboard_view]" id="dashboard" >
					
								<label style="float: left; margin-left: 5px;">View</label>
						</div>
					</div>
				</div>

				<div class="form-group row">
					<div class="col-xs-10">
						<h5 style="margin-bottom: 12px;">@lang('admin.roles.dispatcher')</h5>
					</div>
					<div class="col-xs-12">
						<div class="col-xs-4">
								<?php
								 $dispatcher_view = check_permission('dispatcher_view',$id);
								 ?>
								<input <?php if($dispatcher_view==1) { echo 'checked=checked'; } ?> style="float: left;" type="checkbox" value="admin.dispatcher.index" name="roles[dispatcher_view]" id="dispatcher" >
								<label style="float: left; margin-left: 5px;">View</label>
						</div>
					</div>
				</div>


				<div class="form-group row">
					<div class="col-xs-10">
						<h5 style="margin-bottom: 12px;">@lang('admin.roles.heat_map')</h5>
					</div>
					<div class="col-xs-12">
						<div class="col-xs-4">
								<?php
								 $heatmap_view = check_permission('heatmap_view',$id);
								 ?>
								<input <?php if($heatmap_view==1) { echo 'checked=checked'; } ?> style="float: left;" type="checkbox" value="admin.heatmap" name="roles[heatmap_view]" id="heatmap" >
								<label style="float: left; margin-left: 5px;">View</label>
						</div>
					</div>
				</div>


				<div class="form-group row">
					<div class="col-xs-10">
						<h5 style="margin-bottom: 12px;">@lang('admin.roles.users')</h5>
					</div>
					<div class="col-xs-12">
						<div class="col-xs-2">
								<?php
								 $user_view = check_permission('user_view',$id);
								 ?>
								<input <?php if($user_view==1) { echo 'checked=checked'; } ?>  style="float: left;" type="checkbox" value="admin.user.index" name="roles[user_view]" id="user_view" >
								<label style="float: left; margin-left: 5px;">View</label>
						</div>

						<div class="col-xs-2">
								<?php
								 $user_create = check_permission('user_create',$id);
								 ?>
								<input <?php if($user_create==1) { echo 'checked=checked'; } ?>  style="float: left;" type="checkbox" value="admin.user.create" name="roles[user_create]" id="user_create" >
								<label style="float: left; margin-left: 5px;">Create</label>
						</div>

						<div class="col-xs-2">
								<?php
								 $user_edit = check_permission('user_edit',$id);
								 ?>
								<input <?php if($user_edit==1) { echo 'checked=checked'; } ?>  style="float: left;" type="checkbox" value="admin.user.edit" name="roles[user_edit]" id="user_edit" >
								<label style="float: left; margin-left: 5px;">Edit</label>
						</div>

						<div class="col-xs-2">
								<?php
								 $user_delete = check_permission('user_delete',$id);
								 ?>
								<input <?php if($user_delete==1) { echo 'checked=checked'; } ?>  style="float: left;" type="checkbox" value="admin.user.destroy" name="roles[user_delete]" id="user_delete" >
								<label style="float: left; margin-left: 5px;">Delete</label>
						</div>
					</div>
				</div>

				<div class="form-group row">
					<div class="col-xs-10">
						<h5 style="margin-bottom: 12px;">@lang('admin.roles.providers')</h5>
					</div>
					<div class="col-xs-12">
						<div class="col-xs-2">
							<?php
								 $provider_view = check_permission('provider_view',$id);
								 ?>
								<input <?php if($provider_view==1) { echo 'checked=checked'; } ?> style="float: left;" type="checkbox" value="admin.provider.index" name="roles[provider_view]" id="provider_view" >
								<label style="float: left; margin-left: 5px;">View</label>
						</div>

						<div class="col-xs-2">
							<?php
								 $provider_create = check_permission('provider_create',$id);
								 ?>
								<input <?php if($provider_create==1) { echo 'checked=checked'; } ?> style="float: left;" type="checkbox" value="admin.provider.create" name="roles[provider_create]" id="provider_create" >
								<label style="float: left; margin-left: 5px;">Create</label>
						</div>

						<div class="col-xs-2">
								<?php
								 $provider_edit = check_permission('provider_edit',$id);
								 ?>
								<input  <?php if($provider_edit==1) { echo 'checked=checked'; } ?> style="float: left;" type="checkbox" value="admin.provider.edit" name="roles[provider_edit]" id="provider_edit" >
								<label style="float: left; margin-left: 5px;">Edit</label>
						</div>

						<div class="col-xs-2">
								<?php
								 $provider_delete = check_permission('provider_delete',$id);
								 ?>
								<input <?php if($provider_delete==1) { echo 'checked=checked'; } ?> style="float: left;" type="checkbox" value="admin.provider.destroy" name="roles[provider_delete]" id="provider_delete" >
								<label style="float: left; margin-left: 5px;">Delete</label>
						</div>
					</div>
				</div>


				<div class="form-group row">
					<div class="col-xs-10">
						<h5 style="margin-bottom: 12px;">@lang('admin.roles.dispatcher')</h5>
					</div>
					<div class="col-xs-12">
						<div class="col-xs-2">
								<?php
								 $dispatcher_view = check_permission('dispatcher_view',$id);
								 ?>
								<input <?php if($dispatcher_view==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.dispatch-manager.index" name="roles[dispatcher_view]" id="dispatcher_view" >
								<label style="float: left; margin-left: 5px;">View</label>
						</div>

						<div class="col-xs-2">
								<?php
								 $dispatcher_create = check_permission('dispatcher_create',$id);
								 ?>
								<input <?php if($dispatcher_create==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.dispatch-manager.create" name="roles[dispatcher_create]" id="dispatcher_create" >
								<label style="float: left; margin-left: 5px;">Create</label>
						</div>

						<div class="col-xs-2">
								<?php
								 $dispatcher_edit = check_permission('dispatcher_edit',$id);
								 ?>
								<input <?php if($dispatcher_edit==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.dispatch-manager.edit" name="roles[dispatcher_edit]" id="dispatcher_edit" >
								<label style="float: left; margin-left: 5px;">Edit</label>
						</div>

						<div class="col-xs-2">
								<?php
								 $dispatcher_delete = check_permission('dispatcher_delete',$id);
								 ?>
								<input <?php if($dispatcher_delete==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.dispatch-manager.destroy" name="roles[dispatcher_delete]" id="dispatcher_delete" >
								<label style="float: left; margin-left: 5px;">Delete</label>
						</div>
					</div>
				</div>


				<div class="form-group row">
					<div class="col-xs-10">
						<h5 style="margin-bottom: 12px;">@lang('admin.roles.fleets')</h5>
					</div>
					<div class="col-xs-12">
						<div class="col-xs-2">
							<?php
								 $fleet_view = check_permission('fleet_view',$id);
								 ?>
								<input <?php if($fleet_view==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.fleet.index" name="roles[fleet_view]" id="fleet_view" >
								<label style="float: left; margin-left: 5px;">View</label>
						</div>

						<div class="col-xs-2">
							<?php
								 $fleet_create = check_permission('fleet_create',$id);
								 ?>
								<input <?php if($fleet_create==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.fleet.create" name="roles[fleet_create]" id="fleet_create" >
								<label style="float: left; margin-left: 5px;">Create</label>
						</div>

						<div class="col-xs-2">
							<?php
								 $fleet_edit = check_permission('fleet_edit',$id);
								 ?>
								<input <?php if($fleet_edit==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.fleet.edit" name="roles[fleet_edit]" id="fleet_edit" >
								<label style="float: left; margin-left: 5px;">Edit</label>
						</div>

						<div class="col-xs-2">
							<?php
								 $fleet_delete = check_permission('fleet_delete',$id);
								 ?>
								<input <?php if($fleet_delete==1) { echo 'checked=checked'; }  ?>  style="float: left;" type="checkbox" value="admin.fleet.destroy" name="roles[fleet_delete]" id="fleet_delete" >
								<label style="float: left; margin-left: 5px;">Delete</label>
						</div>
					</div>
				</div>


				<div class="form-group row">
					<div class="col-xs-10">
						<h5 style="margin-bottom: 12px;">@lang('admin.roles.account_owners')</h5>
					</div>
					<div class="col-xs-12">
						<div class="col-xs-2">
							<?php
								 $account_view = check_permission('account_view',$id);
							?>
								<input <?php if($account_view==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.account-manager.index" name="roles[account_view]" id="account_view" >
								<label style="float: left; margin-left: 5px;">View</label>
						</div>

						<div class="col-xs-2">
							<?php
								 $account_create = check_permission('account_create',$id);
							?>
								<input <?php if($account_create==1) { echo 'checked=checked'; }  ?>  style="float: left;" type="checkbox" value="admin.account-manager.create" name="roles[account_create]" id="account_create" >
								<label style="float: left; margin-left: 5px;">Create</label>
						</div>

						<div class="col-xs-2">
							<?php
								 $account_edit = check_permission('account_edit',$id);
							?>
								<input <?php if($account_edit==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.account-manager.edit" name="roles[account_edit]" id="account_edit" >
								<label style="float: left; margin-left: 5px;">Edit</label>
						</div>

						<div class="col-xs-2">
							<?php
								 $account_delete = check_permission('account_delete',$id);
							?>
								<input <?php if($account_delete==1) { echo 'checked=checked'; }  ?>  style="float: left;" type="checkbox" value="admin.account-manager.destroy" name="roles[account_delete]" id="account_delete" >
								<label style="float: left; margin-left: 5px;">Delete</label>
						</div>
					</div>
				</div>

				<div class="form-group row">
					<div class="col-xs-10">
						<h5 style="margin-bottom: 12px;">@lang('admin.roles.statements')</h5>
					</div>
					<div class="col-xs-12">
						<div class="col-xs-2">
							<?php
								 $overall_statement = check_permission('overall_statement',$id);
							?>
								<input <?php if($overall_statement==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.ride.statement" name="roles[overall_statement]" id="statement" >
								<label style="float: left; margin-left: 5px;">Overall</label>
						</div>

						<div class="col-xs-2">
							<?php
								 $statement_provider = check_permission('statement_provider',$id);
							?>
								<input <?php if($statement_provider==1) { echo 'checked=checked'; }  ?>  style="float: left;" type="checkbox" value="admin.ride.statement.provider" name="roles[statement_provider]" id="statement_provider" >
								<label style="float: left; margin-left: 5px;">Provider</label>
						</div>

						<div class="col-xs-2">
							<?php
								 $statement_today = check_permission('statement_today',$id);
							?>
								<input <?php if($statement_today==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.ride.statement.today" name="roles[statement_today]" id="statement_today" >
								<label style="float: left; margin-left: 5px;">Daily</label>
						</div>

						<div class="col-xs-2">
							<?php
								 $statement_monthly = check_permission('statement_monthly',$id);
							?>
								<input <?php if($statement_monthly==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.ride.statement.monthly" name="roles[statement_monthly]" id="statement_monthly" >
								<label style="float: left; margin-left: 5px;">Monthly</label>
						</div>

							<div class="col-xs-2">
								<?php
								 $statement_yearly = check_permission('statement_yearly',$id);
							?>
								<input <?php if($statement_yearly==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.ride.statement.yearly" name="roles[statement_yearly]" id="statement_yearly" >
								<label style="float: left; margin-left: 5px;">Yearly</label>
						</div>
					</div>
				</div>

		

				 <div class="form-group row">
					<div class="col-xs-10">
						<h5 style="margin-bottom: 12px;">@lang('admin.roles.map')</h5>
					</div>
					<div class="col-xs-12">
						<div class="col-xs-4">
							<?php
								 $map_view = check_permission('map_view',$id);
							?>
								<input <?php if($map_view==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.map.index" name="roles[map_view]" id="map" >
								<label style="float: left; margin-left: 5px;">View</label>
						</div>
					</div>
				</div>


				<div class="form-group row">
					<div class="col-xs-10">
						<h5 style="margin-bottom: 12px;">@lang('admin.roles.geo-fencing')</h5>
					</div>
					<div class="col-xs-12">
						<div class="col-xs-2">
								<?php
								 $geo_fencing_view = check_permission('geo_fencing_view',$id);
								 ?>
								<input <?php if($geo_fencing_view==1) { echo 'checked=checked'; } ?>  style="float: left;" type="checkbox" value="admin.geo-fencing.index" name="roles[geo_fencing_view]" id="geo_fencing_view" >
								<label style="float: left; margin-left: 5px;">View</label>
						</div>

						<div class="col-xs-2">
								<?php
								 $geo_fencing_create = check_permission('geo_fencing_create',$id);
								 ?>
								<input <?php if($geo_fencing_create==1) { echo 'checked=checked'; } ?>  style="float: left;" type="checkbox" value="admin.geo-fencing.create" name="roles[geo_fencing_create]" id="geo_fencing_create" >
								<label style="float: left; margin-left: 5px;">Create</label>
						</div>

						<div class="col-xs-2">
								<?php
								 $geo_fencing_edit = check_permission('geo_fencing_edit',$id);
								 ?>
								<input <?php if($geo_fencing_edit==1) { echo 'checked=checked'; } ?>  style="float: left;" type="checkbox" value="admin.geo-fencing.edit" name="roles[geo_fencing_edit]" id="geo_fencing_edit" >
								<label style="float: left; margin-left: 5px;">Edit</label>
						</div>

						<div class="col-xs-2">
								<?php
								 $geo_fencing_delete = check_permission('geo_fencing_delete',$id);
								 ?>
								<input <?php if($geo_fencing_delete==1) { echo 'checked=checked'; } ?>  style="float: left;" type="checkbox" value="admin.geo-fencing.destroy" name="roles[geo_fencing_delete]" id="geo_fencing_delete" >
								<label style="float: left; margin-left: 5px;">Delete</label>
						</div>
					</div>
				</div>


				 <div class="form-group row">
					<div class="col-xs-10">
						<h5 style="margin-bottom: 12px;">@lang('admin.roles.rating_reviews')</h5>
					</div>
					<div class="col-xs-12">
						<div class="col-xs-2">
							<?php
								 $user_review = check_permission('user_review',$id);
							?>
								<input <?php if($user_review==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.user.review" name="roles[user_review]" id="review_user" >
								<label style="float: left; margin-left: 5px;">User Ratings</label>
						</div>

							<div class="col-xs-2">
								<?php
								 $provider_review = check_permission('provider_review',$id);
							    ?>
								<input <?php if($provider_review==1) { echo 'checked=checked'; }  ?>  style="float: left;" type="checkbox" value="admin.provider.review" name="roles[provider_review]" id="review_provider" >
								<label style="float: left; margin-left: 5px;">Provider Ratings</label>
						</div>

					</div>
				</div>


				<div class="form-group row">
					<div class="col-xs-10">
						<h5 style="margin-bottom: 12px;">@lang('admin.roles.request_history')</h5>
					</div>
					<div class="col-xs-12">
						<div class="col-xs-2">
							<?php
								 $requests_index = check_permission('requests_index',$id);
							?>
								<input <?php if($requests_index==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.requests.index" name="roles[requests_index]" id="requests" >
								<label style="float: left; margin-left: 5px;">View</label>
						</div>

					</div>
				</div>


				<div class="form-group row">
					<div class="col-xs-10">
						<h5 style="margin-bottom: 12px;">@lang('admin.roles.scheduled_rides')</h5>
					</div>
					<div class="col-xs-12">
						<div class="col-xs-2">
							<?php
								 $requests_scheduled = check_permission('requests_scheduled',$id);
							?>
								<input <?php if($requests_scheduled==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.requests.scheduled" name="roles[requests_scheduled]" id="scheduled" >
								<label style="float: left; margin-left: 5px;">View</label>
						</div>

					</div>
				</div>

			    <div class="form-group row">
					<div class="col-xs-10">
						<h5 style="margin-bottom: 12px;">@lang('admin.roles.service_types')</h5>
					</div>
					<div class="col-xs-12">
					<div class="col-xs-2">
						<?php
								 $service = check_permission('service',$id);
							?>
								<input <?php if($service==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.service.index" name="roles[service]" id="service" >
								<label style="float: left; margin-left: 5px;">View</label>
						</div>

						<div class="col-xs-2">
							<?php
								 $service_create = check_permission('service_create',$id);
							?>
								<input <?php if($service_create==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.service.create" name="roles[service_create]" id="service_create" >
								<label style="float: left; margin-left: 5px;">Create</label>
						</div>

						<div class="col-xs-2">
							<?php
								 $service_edit = check_permission('service_edit',$id);
							?>
								<input <?php if($service_edit==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.service.edit" name="roles[service_edit]" id="service_edit" >
								<label style="float: left; margin-left: 5px;">Edit</label>
						</div>

						<div class="col-xs-2">
							<?php
								 $service_delete = check_permission('service_delete',$id);
							?>
								<input <?php if($service_delete==1) { echo 'checked=checked'; }  ?>  style="float: left;" type="checkbox" value="admin.service.destroy" name="roles[service_delete]" id="service_delete" >
								<label style="float: left; margin-left: 5px;">Delete</label>
						</div>

					</div>
				</div>

				 <div class="form-group row">
					<div class="col-xs-10">
						<h5 style="margin-bottom: 12px;">@lang('admin.roles.documents')</h5>
					</div>
					<div class="col-xs-12">
					<div class="col-xs-2">
						<?php
						    $document = check_permission('document',$id);
						?>
								<input <?php if($document==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.document.index" name="roles[document]" id="document" >
								<label style="float: left; margin-left: 5px;">View</label>
						</div>

						<div class="col-xs-2">
							<?php
						      $document_create = check_permission('document_create',$id);
						    ?>
								<input <?php if($document_create==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.document.create" name="roles[document_create]" id="document_create" >
								<label style="float: left; margin-left: 5px;">Create</label>
						</div>

						<div class="col-xs-2">
							<?php
						      $document_edit = check_permission('document_edit',$id);
						    ?>
								<input <?php if($document_edit==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.document.edit" name="roles[document_edit]" id="document_edit" >
								<label style="float: left; margin-left: 5px;">Edit</label>
						</div>

						<div class="col-xs-2">
							<?php
						      $document_delete = check_permission('document_delete',$id);
						    ?>
								<input <?php if($document_delete==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.document.destroy" name="roles[document_delete]" id="document_delete" >
								<label style="float: left; margin-left: 5px;">Delete</label>
						</div>

					</div>
				</div>


			    <div class="form-group row">
					<div class="col-xs-10">
						<h5 style="margin-bottom: 12px;">@lang('admin.roles.promocode')</h5>
					</div>
					<div class="col-xs-12">
					<div class="col-xs-2">
						   <?php
						      $promocode = check_permission('promocode',$id);
						    ?>
								<input <?php if($promocode==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.promocode.index" name="roles[promocode]" id="promocode" >
								<label style="float: left; margin-left: 5px;">View</label>
						</div>

						<div class="col-xs-2">
							<?php
						      $promocode_create = check_permission('promocode_create',$id);
						    ?>
								<input <?php if($promocode_create==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value=admin.promocode.create" name="roles[promocode_create]" id="promocode_create" >
								<label style="float: left; margin-left: 5px;">Create</label>
						</div>

						<div class="col-xs-2">
							<?php
						      $promocode_edit = check_permission('promocode_edit',$id);
						    ?>
								<input <?php if($promocode_edit==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.promocode.edit" name="roles[promocode_edit]" id="promocode_edit" >
								<label style="float: left; margin-left: 5px;">Edit</label>
						</div>

						<div class="col-xs-2">
							<?php
						      $promocode_delete = check_permission('promocode_delete',$id);
						    ?>
								<input <?php if($promocode_delete==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.promocode.destroy" name="roles[promocode_delete]" id="promocode_delete" >
								<label style="float: left; margin-left: 5px;">Delete</label>
						</div>

					</div>
				</div>


				   <div class="form-group row">
					<div class="col-xs-10">
						<h5 style="margin-bottom: 12px;">@lang('admin.roles.general')</h5>
					</div>
					<div class="col-xs-12">
					<div class="col-xs-3">
						<?php
						   $payment = check_permission('payment',$id);
						 ?>
								<input <?php if($payment==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.payment" name="roles[payment]" id="payment" >
								<label style="float: left; margin-left: 5px;">Payment History</label>
						</div>

						<div class="col-xs-3">
							<?php
						      $payment_settings = check_permission('payment_settings',$id);
						    ?>
								<input <?php if($payment_settings==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.settings.payment" name="roles[payment_settings]" id="payment_settings" >
								<label style="float: left; margin-left: 5px;">Payment Settings</label>
						</div>

						<div class="col-xs-3">
							<?php
						      $settings = check_permission('settings',$id);
						    ?>
								<input <?php if($settings==1) { echo 'checked=checked'; }  ?>  style="float: left;" type="checkbox" value="admin.settings" name="roles[settings]" id="settings" >
								<label style="float: left; margin-left: 5px;">Site Settings</label>
						</div>

						<div class="col-xs-3">
							<?php
						      $privacy = check_permission('privacy',$id);
						    ?>
								<input <?php if($privacy==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.privacy" name="roles[privacy]" id="privacy" >
								<label style="float: left; margin-left: 5px;">Privacy</label>
						</div>

						<div class="col-xs-3">
							<?php
						      $terms = check_permission('terms',$id);
						    ?>
								<input <?php if($terms==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.terms" name="roles[terms]" id="terms" >
								<label style="float: left; margin-left: 5px;">Terms</label>
						</div>

						<div class="col-xs-3">
							<?php
						      $about_us = check_permission('about_us',$id);
						    ?>
								<input <?php if($about_us==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.about_us" name="roles[about_us]" id="about_us" >
								<label style="float: left; margin-left: 5px;">About Us</label>
						</div>



						<div class="col-xs-3">
							<?php
						      $help = check_permission('help',$id);
						    ?>
								<input <?php if($help==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.help" name="roles[help]" id="help" >
								<label style="float: left; margin-left: 5px;">Help</label>
						</div>

						<div class="col-xs-3">
							<?php
						      $faq = check_permission('faq',$id);
						    ?>
								<input <?php if($faq==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.faq" name="roles[faq]" id="faq" >
								<label style="float: left; margin-left: 5px;">FAQ</label>
						</div>

						<div class="col-xs-3">
							<?php
						      $offers = check_permission('offers',$id);
						    ?>
								<input <?php if($offers==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.offers" name="roles[offers]" id="offers" >
								<label style="float: left; margin-left: 5px;">Offers</label>
						</div>



						<div class="col-xs-3">
							<?php
						      $send_push = check_permission('send_push',$id);
						    ?>
								<input <?php if($send_push==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.push" name="roles[send_push]" id="send_push" >
								<label style="float: left; margin-left: 5px;">Custom Push</label>
						</div>


						<div class="col-xs-3">
							<?php
						      $translation = check_permission('translation',$id);
						    ?>
								<input <?php if($translation==1) { echo 'checked=checked'; }  ?>  style="float: left;" type="checkbox" value="admin.translation" name="roles[translation]" id="translation" >
								<label style="float: left; margin-left: 5px;">Translations</label>
						</div>

						<div class="col-xs-3">
							<?php
						      $profile = check_permission('profile',$id);
						    ?>
								<input <?php if($profile==1) { echo 'checked=checked'; }  ?>  style="float: left;" type="checkbox" value="admin.profile" name="roles[profile]" id="profile" >
								<label style="float: left; margin-left: 5px;">Account Settings</label>
						</div>


						<div class="col-xs-3">
							<?php
						      $password = check_permission('password',$id);
						    ?>
								<input <?php if($password==1) { echo 'checked=checked'; }  ?> style="float: left;" type="checkbox" value="admin.password" name="roles[password]" id="password" >
								<label style="float: left; margin-left: 5px;">Change Password</label>
						</div>


					</div>
				</div>



	
		
	

				<div class="form-group row">
					<label for="zipcode" class="col-xs-12 col-form-label"></label>
					<div class="col-xs-10">
						<button type="submit" class="btn btn-primary">@lang('admin.roles.update')</button>
						<a href="{{route('admin.roles.index')}}" class="btn btn-default">@lang('admin.cancel')</a>
					</div>
				</div>
			</form>
		</div>
    </div>
</div>

@endsection

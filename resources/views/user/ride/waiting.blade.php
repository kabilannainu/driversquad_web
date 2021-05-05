@extends('user.layout.base')

@section('title', 'On Ride')

@section('content')

<div class="col-md-9">
    <div class="dash-content">
    	@include('common.notify')
		<div class="row no-margin">
		    <div class="col-md-12">
		        <h4 class="page-title" id="ride_status"></h4>
		    </div>
		</div>
		
		<div class="row no-margin">
		        <div class="col-md-6" id="container" >
		    		<p>Loading...</p>                             
		        </div>

		        <div class="col-md-6">
		            <dl class="dl-horizontal left-right">
		                <dt>@lang('user.request_id')</dt>
		                <dd>{{$request->id}}</dd>
		                <dt>@lang('user.time')</dt>
		                <dd>{{date('d-m-Y H:i A',strtotime($request->assigned_at))}}</dd>
		            </dl> 
		            <div class="user-request-map">

		                <div class="from-to row no-margin">
		                    <div class="from">
		                        <h5>FROM</h5>
		                        <p>{{$request->s_address}}</p>
		                    </div>
		                    <div class="to">
		                        <h5>TO</h5>
		                        <p>{{$request->d_address}}</p>
		                    </div>
		                    <div class="type">
		                    	<h5>TYPE</h5>
		                        <p>{{$request->service_type->name}}</p>
		                    </div>
		                </div>
		                <?php 
		                    $map_icon = asset('asset/img/marker-start.png');
		                    $static_map = "https://maps.googleapis.com/maps/api/staticmap?autoscale=1&size=600x450&maptype=roadmap&format=png&visual_refresh=true&markers=icon:".$map_icon."%7C".$request->s_latitude.",".$request->s_longitude."&markers=icon:".$map_icon."%7C".$request->d_latitude.",".$request->d_longitude."&path=color:0x191919|weight:8|enc:".$request->route_key."&key=".Setting::get('map_key'); ?>

		                    <div class="map-image">
		                    	<img src="{{$static_map}}">
		                    </div>                               
		            </div>                          
		        </div>
		</div>
	</div>
</div>

@endsection

@section('scripts')

    <script type="text/javascript" src="{{asset('asset/js/rating.js')}}"></script>    
	<script type="text/javascript">
		$('.rating').rating();
	</script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/react/0.13.3/react.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/react/0.13.3/JSXTransformer.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script> 
    
    <script type="text/jsx"> 

    	function call(amount, email, name , request_id) {
            
            var options = {
                "key": "<?php echo Setting::get('rzp_key'); ?>",
                "amount": amount*100,
                "name": "Merchant Name",
                "description": "Purchase Description",
                "image": "{{ asset('asset/img/site_logo.png') }}",
                "handler": function (response){

                    $.ajax({
                        type: "POST",
                        url: "{{url('rzp/flow')}}",
                        data:{ payment_id: response.razorpay_payment_id ,request_id: request_id},           
                        dataType: "json",
                        success: function(data) {
                        
                            swal({
                                    title: "Success",
                                    text: data.message,
                                    type: "success",
                                    confirmButtonClass: "btn-success",
                                },
                                function(){
                                     window.location.href="{{url('/dashboard')}}";
                                });

                        }
                    });    

                },
                "prefill": {
                    "name":name ,
                    "email": email
                },
                "notes": {
                    "address": ""
                },
                "theme": {
                    "color": "#e86609"
                }
            };
            var rzp1 = new Razorpay(options);
            rzp1.open();           
        }

    	function interval(){
	        clearInterval(this.updateInterval);
	    }

		var MainComponent = React.createClass({
			getInitialState: function () {
                    return {data: [], currency : "{{Setting::get('currency')}}"};
                },
			componentDidMount: function(){
				$.ajax({
			      url: "{{url('status')}}",
			      type: "GET"})
			      .done(function(response){

				        this.setState({
				            data:response.data[0]
				        });

				    }.bind(this));

			    this.updateInterval = setInterval(this.checkRequest, 5000);

            	interval = interval.bind(this);
			},
			checkRequest : function(){
				$.ajax({
			      url: "{{url('status')}}",
			      type: "GET"})
			      .done(function(response){
				        this.setState({
				            data:response.data[0]
				        });

				    }.bind(this));
			},
			render: function(){
				return (
					<div>
						<SwitchState checkState={this.state.data} currency={this.state.currency} />
					</div>
				);
			}
		});

		var SwitchState = React.createClass({

			componentDidMount: function() {
				this.changeLabel;
			},

			changeLabel : function(){
				if(this.props.checkState == undefined){
					window.location.reload();
				}else if(this.props.checkState != ""){

					if(this.props.checkState.status == 'SEARCHING'){
						$("#ride_status").text("@lang('user.ride.finding_driver')");
					}else if(this.props.checkState.status == 'STARTED'){
						var provider_name = this.props.checkState.provider.first_name;
						$("#ride_status").text(provider_name+" @lang('user.ride.accepted_ride')");
					}else if(this.props.checkState.status == 'ARRIVED'){
						var provider_name = this.props.checkState.provider.first_name;
						$("#ride_status").text(provider_name+" @lang('user.ride.arrived_ride')");
					}else if(this.props.checkState.status == 'PICKEDUP'){
						$("#ride_status").text("@lang('user.ride.onride')");
					}else if(this.props.checkState.status == 'DROPPED'){
						$("#ride_status").text("@lang('user.ride.waiting_payment')");
					}else if(this.props.checkState.status == 'COMPLETED'){
						var provider_name = this.props.checkState.provider.first_name;
						$("#ride_status").text("@lang('user.ride.rate_and_review') " +provider_name );
					}
					setTimeout(function(){
						$('.rating').rating();
					},400);

				}else{
					$("#ride_status").text('Text will appear here');
				}
			},
			render: function(){

				if(this.props.checkState != ""){

					this.changeLabel();
					if(this.props.checkState.status == 'SEARCHING'){
						return (
							<div>
								<Searching checkState={this.props.checkState} />
							</div>
						);
					}else if(this.props.checkState.status == 'STARTED'){
						return (
							<div>
								<Accepted checkState={this.props.checkState} />
							</div>
						);
					}else if(this.props.checkState.status == 'ARRIVED'){
						return (
							<div>
								<Arrived checkState={this.props.checkState} />
							</div>
						);
					}else if(this.props.checkState.status == 'PICKEDUP'){
						return (
							<div>
								<Pickedup checkState={this.props.checkState} />
							</div>
						);
					}else if((this.props.checkState.status == 'DROPPED' || this.props.checkState.status == 'COMPLETED') && this.props.checkState.payment_mode == 'CASH' && this.props.checkState.paid == 0){
						return (
							<div>
								<DroppedAndCash checkState={this.props.checkState} currency={this.props.currency} />
							</div>
						);
					}else if((this.props.checkState.status == 'DROPPED' || this.props.checkState.status == 'COMPLETED') && this.props.checkState.payment_mode == 'CARD' && this.props.checkState.paid == 0){
						return (
							<div>
								<DroppedAndCard checkState={this.props.checkState} currency={this.props.currency} />
							</div>
						);
					}else if((this.props.checkState.status == 'DROPPED' || this.props.checkState.status == 'COMPLETED') && this.props.checkState.payment_mode == 'razorpay' && this.props.checkState.paid == 0){
						return (
							<div>
								<DroppedAndRazorpay checkState={this.props.checkState} currency={this.props.currency} />
							</div>
						);
					}else if((this.props.checkState.status == 'DROPPED' || this.props.checkState.status == 'COMPLETED') && this.props.checkState.payment_mode == 'CC_AVANUE' && this.props.checkState.paid == 0){
						return (
							<div>
								<DroppedAndCCAvanue checkState={this.props.checkState} currency={this.props.currency} />
							</div>
						);
					}else if(this.props.checkState.status == 'COMPLETED'){
						return (
							<div>
								<Review checkState={this.props.checkState} />
							</div>
						);
					}
				}else{
					return ( 
						<p></p>
					 );
				}
			}
		});

		var Searching = React.createClass({
			render: function(){
				return (
					<form action="{{url('cancel/ride')}}" method="POST">
						{{ csrf_field() }}</input>
						<input type="hidden" name="request_id" value={this.props.checkState.id} />
			            <div className="status">
			                <h6>@lang('user.status')</h6>
			                <p>@lang('user.ride.finding_driver')</p>
			            </div>

		            	<button type="submit" className="full-primary-btn fare-btn">@lang('user.ride.cancel_request')</button> 
		            </form>
				);
			}
		});

		var Accepted = React.createClass({
			render: function(){
				return (
					<form action="{{url('cancel/ride')}}" method="POST">
						{{ csrf_field() }}</input>
					<input type="hidden" name="request_id" value={this.props.checkState.id} />
						<div className="status">
			                <h6>@lang('user.status')</h6>
			                <p>@lang('user.ride.accepted_ride')</p>
			            </div>
			            <CancelReason/>
		            	<button type="button" className="full-primary-btn" data-toggle="modal" data-target="#cancel-reason">@lang('user.ride.cancel_request')</button>
		            	<br/>
		            		<h5><strong>@lang('user.ride.ride_details')</strong></h5>
		            	<div className="driver-details">
			            	<dl className="dl-horizontal left-right">
			            		<dt>@lang('user.booking_id')</dt>
				                <dd>{this.props.checkState.booking_id}</dd>
				                <dt>@lang('user.driver_name')</dt>
				                <dd>{this.props.checkState.provider.first_name} {this.props.checkState.provider.last_name}</dd>
				                <dt>@lang('user.service_number')</dt>
				                <dd>{this.props.checkState.provider_service.service_number}</dd>
				                <dt>@lang('user.service_model')</dt>
				                <dd>{this.props.checkState.provider_service.service_model}</dd>
				                <dt>@lang('user.driver_rating')</dt>
				                <dd>
				                	<div className="rating-outer">
			                            <input type="hidden" value={this.props.checkState.provider.rating} name="rating" className="rating"/>
			                        </div>
				                </dd>
				                <dt>@lang('user.payment_mode')</dt>
				                <dd>{this.props.checkState.payment_mode}</dd>
				                <dt>@lang('user.otp')</dt>
				                <dd>{this.props.checkState.otp}</dd>
				            </dl> 
			            </div>

		            </form>
				);
			}
		});

		var CancelReason = React.createClass({

		getInitialState: function () {
                    return {reason: ''};
                },

                handleCancelReason : function(event){ 
				this.setState({reason: event.target.value});
			},

			render: function(){
				return (
					<div id="cancel-reason" className="modal fade" role="dialog">
						<div className="modal-dialog">
							<div className="modal-content">
								<div className="modal-header">
									<button type="button" className="close" data-dismiss="modal">&times;</button>
									<h4 className="modal-title">@lang('user.ride.cancel_request')</h4>
								</div>
								<div className="modal-body">
									<textarea className="form-control" name="cancel_reason" placeholder="@lang('user.ride.cancel_reason')" onChange={this.handleCancelReason.bind(this)} row="5">{this.state.reason}</textarea>
								</div>
								{this.state.reason ?
								<div className="modal-footer">
									<button type="submit" className="full-primary-btn fare-btn">@lang('user.ride.cancel_request')</button>
								</div>
								: '' }
							</div>
						</div>
					</div>
				);
			}
		});

		var Arrived = React.createClass({
			render: function(){
				return (
					<form action="{{url('cancel/ride')}}" method="POST">
						{{ csrf_field() }}</input>
					<input type="hidden" name="request_id" value={this.props.checkState.id} />
						<div className="status">
			                <h6>@lang('user.status')</h6>
			                <p>@lang('user.ride.arrived_ride')</p>
			            </div>
			            <CancelReason/>
		            	<button type="button" className="full-primary-btn" data-toggle="modal" data-target="#cancel-reason">@lang('user.ride.cancel_request')</button> 
		            	<br/>
		            		<h5><strong>@lang('user.ride.ride_details')</strong></h5>
		            	<div className="driver-details">
			            	<dl className="dl-horizontal left-right">
			            		<dt>@lang('user.booking_id')</dt>
				                <dd>{this.props.checkState.booking_id}</dd>
				                <dt>@lang('user.driver_name')</dt>
				                <dd>{this.props.checkState.provider.first_name} {this.props.checkState.provider.last_name}</dd>
				                <dt>@lang('user.service_number')</dt>
				                <dd>{this.props.checkState.provider_service.service_number}</dd>
				                <dt>@lang('user.service_model')</dt>
				                <dd>{this.props.checkState.provider_service.service_model}</dd>
				                <dt>@lang('user.driver_rating')</dt>
				                <dd>
				                	<div className="rating-outer">
			                            <input type="hidden" value={this.props.checkState.provider.rating} name="rating" className="rating"/>
			                        </div>
				                </dd>
				                <dt>@lang('user.payment_mode')</dt>
				                <dd>{this.props.checkState.payment_mode}</dd>
				                 <dt>@lang('user.otp')</dt>
				                <dd>{this.props.checkState.otp}</dd>
				            </dl> 
			            </div>
		            </form>
				);
			}
		});

		var Pickedup = React.createClass({
			render: function(){
				return (
				<div>
					<div className="status">
		                <h6>@lang('user.status')</h6>
		                <p>@lang('user.ride.onride')</p>
		            </div>
		            <br/>
	            		<h5><strong>@lang('user.ride.ride_details')</strong></h5>
	            	<div className="driver-details">
		            	<dl className="dl-horizontal left-right">
		            		<dt>@lang('user.booking_id')</dt>
				            <dd>{this.props.checkState.booking_id}</dd>
			                <dt>@lang('user.driver_name')</dt>
			                <dd>{this.props.checkState.provider.first_name} {this.props.checkState.provider.last_name}</dd>
			                <dt>@lang('user.service_number')</dt>
				                <dd>{this.props.checkState.provider_service.service_number}</dd>
				                <dt>@lang('user.service_model')</dt>
				                <dd>{this.props.checkState.provider_service.service_model}</dd>
			                <dt>@lang('user.driver_rating')</dt>
				                <dd>
				                	<div className="rating-outer">
			                            <input type="hidden" value={this.props.checkState.provider.rating} name="rating" className="rating"/>
			                        </div>
				                </dd>
			                <dt>@lang('user.payment_mode')</dt>
			                <dd>{this.props.checkState.payment_mode}</dd>
			                
			            </dl> 
		            </div>
		        </div>
				);
			}
		});

		var DroppedAndCash = React.createClass({


			getInitialState: function() {
	        return { 
	        hide1: true,
	        hide2: true,
	         };
	        },
	        
	        onClick1: function() {
	        this.setState({ hide1: true });
	        },

	        onClick2: function() {
	        this.setState({ hide2: true });
	        }, 

	        onClickplus1: function() {
	        this.setState({ hide1: false });
	        console.log('sadfasdf');
	        }, 

	        onClickplus2: function() {
	        this.setState({ hide2: false });
	        },  
		 
			render: function(){

			 var hide = {
	          display: 'none'
	        };
	        var show = {
	          display: 'block'
	        };

				return (
				<div>
					<div className="status">
		                <h6>@lang('user.status')</h6>
		                <p>@lang('user.ride.dropped_ride')</p>
		            </div>
		            <br/>
		            	<h5><strong>@lang('user.ride.ride_details')</strong></h5>
		            	<dl className="dl-horizontal left-right">
		            		<dt>@lang('user.booking_id')</dt>
				            <dd>{this.props.checkState.booking_id}</dd>
		            		<dt>@lang('user.driver_name')</dt>
			                <dd>{this.props.checkState.provider.first_name} {this.props.checkState.provider.last_name}</dd>
			                <dt>@lang('user.service_number')</dt>
				                <dd>{this.props.checkState.provider_service.service_number}</dd>
				                <dt>@lang('user.service_model')</dt>
				                <dd>{this.props.checkState.provider_service.service_model}</dd>
			                <dt>@lang('user.driver_rating')</dt>
			                <dd>
			                	<div className="rating-outer">
		                            <input type="hidden" value={this.props.checkState.provider.rating} name="rating" className="rating"/>
		                        </div>
			                </dd>
		            		<dt>@lang('user.payment_mode')</dt>
                        	<dd>{this.props.checkState.payment_mode}</dd>
                        	
                        </dl>
		            	<h5><strong>@lang('user.ride.invoice')</strong></h5>
		            	<dl className="dl-horizontal left-right"> 
                            @if($request->service_required == 'none' || $request->service_required == 'outstation')
                            <dt>@lang('user.ride.distance_travelled')</dt>
                            <dd>{this.props.checkState.distance} kms</dd>
                            @if($request->service_type->distance != 0)
                            <div className="offerchanges"><span className="offerchangeone">{this.props.checkState.service_type.distance}Km</span> Price Of <span className="offerchangetwo">{this.props.currency}{this.props.checkState.service_type.price * this.props.checkState.service_type.distance}</span> Deducted From Your Fare </div>
                            @endif
                            <dt>@lang('user.ride.base_price')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.fixed}</dd>
                            <dt>@lang('user.ride.distance_price')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.distance}</dd>
                           
                            <dt>Non Cluster price</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.non_geo_price}</dd>

                          
                            <dt style={ (this.state.hide1==true) ? hide : show }><i>Geo Distance Price</i></dt>
                            <dd style={ (this.state.hide1==true) ? hide : show }><i>{this.props.currency}{this.props.checkState.payment.geo_fencing_total}</i></dd>
                             <dt style={ (this.state.hide1==true) ? hide : show }><i>Non Geo Distance Price</i></dt>
                            <dd style={ (this.state.hide1==true) ? hide : show }><i>{this.props.currency}{this.props.checkState.payment.none_geo_fencing_total}</i></dd> 

                            <dt>@lang('user.ride.minutes_price')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.minute}</dd>

                            <dt style={ (this.state.hide2==true) ? hide : show }><i>Geo Minute Price</i></dt>
                            <dd style={ (this.state.hide2==true) ? hide : show }><i>{this.props.currency}{this.props.checkState.payment.geo_fencing_minute}</i></dd>
                             <dt style={ (this.state.hide2==true) ? hide : show }><i>Non Geo Minute Price</i></dt>
                            <dd style={ (this.state.hide2==true) ? hide : show }><i>{this.props.currency}{this.props.checkState.payment.non_geo_fencing_minute}</i></dd>

                            @elseif($request->service_required == 'rental')
                            <dt>Rental Fare</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.minute}</dd>
                            @endif
                            <dt>@lang('user.ride.tax_price')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.tax}</dd>
                            {this.props.checkState.use_wallet ?
								<span>
								<dt>@lang('user.ride.detection_wallet')</dt>
                            	<dd>{this.props.currency}{this.props.checkState.payment.wallet}</dd>  
                            	</span>
                            : ''
                            }

                            {this.props.checkState.payment.discount ?
	                            <span>
	                            <dt>@lang('user.ride.promotion_applied')</dt>
	                            <dd>{this.props.currency}{this.props.checkState.payment.discount}</dd>  
	                            </span>
	                        : ''
	                        }
	                        {this.props.checkState.payment.eta_discount ?
								<span>
								<dt>@lang('user.ride.eta_discount')</dt>
                            	<dd>{this.props.currency}{this.props.checkState.payment.eta_discount}</dd>  
                            	</span>
                            : ''
                            }
                            {this.props.checkState.payment.night_fare ?
								<span>
								<dt>@lang('user.ride.night_fare')</dt>
                            	<dd>{this.props.currency}{this.props.checkState.payment.night_fare}</dd>  
                            	</span>
                            : ''
                            }
	                  
                            <dt className="big">@lang('user.ride.total')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.total}</dd> 
                            <dt className="big">@lang('user.ride.amount_paid')</dt>
                            <dd className="big">{this.props.currency}{this.props.checkState.payment.payable}</dd>
                        </dl>
		        </div>
				);
			}
		});

		var DroppedAndCard = React.createClass({

		getInitialState: function() {
	        return { 
	        hide1: true,
	        hide2: true,
	         };
	        },
	        
	        onClick1: function() {
	        this.setState({ hide1: true });
	        },

	        onClick2: function() {
	        this.setState({ hide2: true });
	        }, 

	        onClickplus1: function() {
	        this.setState({ hide1: false });
	        console.log('sadfasdf');
	        }, 

	        onClickplus2: function() {
	        this.setState({ hide2: false });
	        },  
		 
			render: function(){

			 var hide = {
	          display: 'none'
	        };
	        var show = {
	          display: 'block'
	        };
 
				return (
				<div>
					<form method="POST" action="{{url('/payment')}}">
						{{ csrf_field() }}</input>
					<div className="status">
		                <h6>@lang('user.status')</h6>
		                <p>@lang('user.ride.dropped_ride')</p>
		            </div>
		            	<br/>
		            	<h5><strong>@lang('user.ride.ride_details')</strong></h5>
		            	<dl className="dl-horizontal left-right">
		            		<dt>@lang('user.booking_id')</dt>
				            <dd>{this.props.checkState.booking_id}</dd>
		            		<dt>@lang('user.driver_name')</dt>
			                <dd>{this.props.checkState.provider.first_name} {this.props.checkState.provider.last_name}</dd>
			                <dt>@lang('user.service_number')</dt>
				                <dd>{this.props.checkState.provider_service.service_number}</dd>
				                <dt>@lang('user.service_model')</dt>
				                <dd>{this.props.checkState.provider_service.service_model}</dd>
			                <dt>@lang('user.driver_rating')</dt>
			                <dd>
			                	<div className="rating-outer">
		                            <input type="hidden" value={this.props.checkState.provider.rating} name="rating" className="rating"/>
		                        </div>
			                </dd>
		            		<dt>@lang('user.payment_mode')</dt>
                        	<dd>{this.props.checkState.payment_mode}</dd> 
                        </dl>
		            	<h5><strong>@lang('user.ride.invoice')</strong></h5>
		            	<input type="hidden" name="request_id" value={this.props.checkState.id} />
		            	<dl className="dl-horizontal left-right">
            			 	@if($request->service_required == 'none' || $request->service_required == 'outstation')
            			 	<dt>@lang('user.ride.distance_travelled')</dt>
                            <dd>{this.props.checkState.distance} kms</dd>
                            <dt>@lang('user.ride.base_price')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.fixed}</dd>
                            <dt>@lang('user.ride.distance_price')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.distance}</dd>
                            <dt>Non Cluster price</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.non_geo_price}</dd>

                            <dt style={ (this.state.hide1==true) ? hide : show }><i>Geo Distance Price</i></dt>
                            <dd style={ (this.state.hide1==true) ? hide : show }><i>{this.props.currency}{this.props.checkState.payment.geo_fencing_total}</i></dd>
                             <dt style={ (this.state.hide1==true) ? hide : show }><i>Non Geo Distance Price</i></dt>
                            <dd style={ (this.state.hide1==true) ? hide : show }><i>{this.props.currency}{this.props.checkState.payment.none_geo_fencing_total}</i></dd> 

                            <dt>@lang('user.ride.minutes_price')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.minute}</dd>

                            <dt style={ (this.state.hide2==true) ? hide : show }><i>Geo Minute Price</i></dt>
                            <dd style={ (this.state.hide2==true) ? hide : show }><i>{this.props.currency}{this.props.checkState.payment.geo_fencing_minute}</i></dd>
                             <dt style={ (this.state.hide2==true) ? hide : show }><i>Non Geo Minute Price</i></dt>
                            <dd style={ (this.state.hide2==true) ? hide : show }><i>{this.props.currency}{this.props.checkState.payment.non_geo_fencing_minute}</i></dd>
                            @elseif($request->service_required == 'rental')
                            <dt>Rental Fare</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.minute}</dd>
                            @endif
                            <dt>@lang('user.ride.tax_price')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.tax}</dd>
                            <dt>@lang('user.ride.total')</dt>
                            {this.props.checkState.use_wallet ?
								<span>
								<dt>@lang('user.ride.detection_wallet')</dt>
                            	<dd>{this.props.currency}{this.props.checkState.payment.wallet}</dd>  
                            	</span>
                            : ''
                            }
                            {this.props.checkState.payment.discount ?
								<span>
								<dt>@lang('user.ride.promotion_applied')</dt>
                            	<dd>{this.props.currency}{this.props.checkState.payment.discount}</dd>  
                            	</span>
                            : ''
                            }
                          	{this.props.checkState.payment.eta_discount ?
								<span>
								<dt>@lang('user.ride.eta_discount')</dt>
                            	<dd>{this.props.currency}{this.props.checkState.payment.eta_discount}</dd>  
                            	</span>
                            : ''
                            }
                            {this.props.checkState.payment.night_fare ?
								<span>
								<dt>@lang('user.ride.night_fare')</dt>
                            	<dd>{this.props.currency}{this.props.checkState.payment.night_fare}</dd>  
                            	</span>
                            : ''
                            }
                            <dd>{this.props.currency}{this.props.checkState.payment.total}</dd> 
                            <dt className="big">@lang('user.ride.amount_paid')</dt>
                            <dd className="big">{this.props.currency}{this.props.checkState.payment.total}</dd>
                        </dl>
                    	<button type="submit" className="full-primary-btn fare-btn">CONTINUE TO PAY - {this.props.currency}{this.props.checkState.payment.payable}</button>   
                    </form>
		        </div>
				);
			}
		});



		var DroppedAndRazorpay = React.createClass({

		 submitrazorpay: function(e){
               
                      
                call(this.props.checkState.payment.payable, this.props.checkState.user.email, this.props.checkState.user.first_name , this.props.checkState.id);         
            },



		getInitialState: function() {
	        return { 
	        hide1: true,
	        hide2: true,
	         };
	        },
	        
	        onClick1: function() {
	        this.setState({ hide1: true });
	        },

	        onClick2: function() {
	        this.setState({ hide2: true });
	        }, 

	        onClickplus1: function() {
	        this.setState({ hide1: false });
	        console.log('sadfasdf');
	        }, 

	        onClickplus2: function() {
	        this.setState({ hide2: false });
	        },  
		 
			render: function(){

			 var hide = {
	          display: 'none'
	        };
	        var show = {
	          display: 'block'
	        };
 
				return (
				<div>
					<form method="POST" action="{{url('/payment')}}">
						{{ csrf_field() }}</input>
					<div className="status">
		                <h6>@lang('user.status')</h6>
		                <p>@lang('user.ride.dropped_ride')</p>
		            </div>
		            	<br/>
		            	<h5><strong>@lang('user.ride.ride_details')</strong></h5>
		            	<dl className="dl-horizontal left-right">
		            		<dt>@lang('user.booking_id')</dt>
				            <dd>{this.props.checkState.booking_id}</dd>
		            		<dt>@lang('user.driver_name')</dt>
			                <dd>{this.props.checkState.provider.first_name} {this.props.checkState.provider.last_name}</dd>
			                <dt>@lang('user.service_number')</dt>
				                <dd>{this.props.checkState.provider_service.service_number}</dd>
				                <dt>@lang('user.service_model')</dt>
				                <dd>{this.props.checkState.provider_service.service_model}</dd>
			                <dt>@lang('user.driver_rating')</dt>
			                <dd>
			                	<div className="rating-outer">
		                            <input type="hidden" value={this.props.checkState.provider.rating} name="rating" className="rating"/>
		                        </div>
			                </dd>
		            		<dt>@lang('user.payment_mode')</dt>
                        	<dd>{this.props.checkState.payment_mode}</dd> 
                        </dl>
		            	<h5><strong>@lang('user.ride.invoice')</strong></h5>
		            	<input type="hidden" name="request_id" value={this.props.checkState.id} />
		            	<dl className="dl-horizontal left-right">
            			 	@if($request->service_required == 'none' || $request->service_required == 'outstation')
            			 	<dt>@lang('user.ride.distance_travelled')</dt>
                            <dd>{this.props.checkState.distance} kms</dd>
                            <dt>@lang('user.ride.base_price')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.fixed}</dd>
                            <dt>@lang('user.ride.distance_price')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.distance}</dd>
                            <dt>Non Cluster price</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.non_geo_price}</dd>

                            <dt style={ (this.state.hide1==true) ? hide : show }><i>Geo Distance Price</i></dt>
                            <dd style={ (this.state.hide1==true) ? hide : show }><i>{this.props.currency}{this.props.checkState.payment.geo_fencing_total}</i></dd>
                             <dt style={ (this.state.hide1==true) ? hide : show }><i>Non Geo Distance Price</i></dt>
                            <dd style={ (this.state.hide1==true) ? hide : show }><i>{this.props.currency}{this.props.checkState.payment.none_geo_fencing_total}</i></dd> 

                            <dt>@lang('user.ride.minutes_price')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.minute}</dd>

                            <dt style={ (this.state.hide2==true) ? hide : show }><i>Geo Minute Price</i></dt>
                            <dd style={ (this.state.hide2==true) ? hide : show }><i>{this.props.currency}{this.props.checkState.payment.geo_fencing_minute}</i></dd>
                             <dt style={ (this.state.hide2==true) ? hide : show }><i>Non Geo Minute Price</i></dt>
                            <dd style={ (this.state.hide2==true) ? hide : show }><i>{this.props.currency}{this.props.checkState.payment.non_geo_fencing_minute}</i></dd>
                            @elseif($request->service_required == 'rental')
                            <dt>Rental Fare</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.minute}</dd>
                            @endif
                            <dt>@lang('user.ride.tax_price')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.tax}</dd>
                            <dt>@lang('user.ride.total')</dt>
                            {this.props.checkState.use_wallet ?
								<span>
								<dt>@lang('user.ride.detection_wallet')</dt>
                            	<dd>{this.props.currency}{this.props.checkState.payment.wallet}</dd>  
                            	</span>
                            : ''
                            }
                            {this.props.checkState.payment.discount ?
								<span>
								<dt>@lang('user.ride.promotion_applied')</dt>
                            	<dd>{this.props.currency}{this.props.checkState.payment.discount}</dd>  
                            	</span>
                            : ''
                            }
                          	{this.props.checkState.payment.eta_discount ?
								<span>
								<dt>@lang('user.ride.eta_discount')</dt>
                            	<dd>{this.props.currency}{this.props.checkState.payment.eta_discount}</dd>  
                            	</span>
                            : ''
                            }
                            {this.props.checkState.payment.night_fare ?
								<span>
								<dt>@lang('user.ride.night_fare')</dt>
                            	<dd>{this.props.currency}{this.props.checkState.payment.night_fare}</dd>  
                            	</span>
                            : ''
                            }
                            <dd>{this.props.currency}{this.props.checkState.payment.total}</dd> 
                            <dt className="big">@lang('user.ride.amount_paid')</dt>
                            <dd className="big">{this.props.currency}{this.props.checkState.payment.total}</dd>
                        </dl>

                        <button type="button" id="rzp-button1" onClick={this.submitrazorpay.bind(this)} data-bookingid={this.props.checkState.booking_id} className="full-primary-btn fare-btn">CONTINUE TO PAY - {this.props.currency}{this.props.checkState.payment.payable}</button>

                    </form>
		        </div>
				);
			}
		});


		var DroppedAndCCAvanue = React.createClass({

		getInitialState: function() {
	        return { 
	        hide1: true,
	        hide2: true,
	         };
	        },
	        
	        onClick1: function() {
	        this.setState({ hide1: true });
	        },

	        onClick2: function() {
	        this.setState({ hide2: true });
	        }, 

	        onClickplus1: function() {
	        this.setState({ hide1: false });
	        console.log('sadfasdf');
	        }, 

	        onClickplus2: function() {
	        this.setState({ hide2: false });
	        },  
		 
			render: function(){

			 var hide = {
	          display: 'none'
	        };
	        var show = {
	          display: 'block'
	        };
 
				return (
				<div>
					<form method="POST" action="{{url('/payment')}}">
						{{ csrf_field() }}</input>
					<div className="status">
		                <h6>@lang('user.status')</h6>
		                <p>@lang('user.ride.dropped_ride')</p>
		            </div>
		            	<br/>
		            	<h5><strong>@lang('user.ride.ride_details')</strong></h5>
		            	<dl className="dl-horizontal left-right">
		            		<dt>@lang('user.booking_id')</dt>
				            <dd>{this.props.checkState.booking_id}</dd>
		            		<dt>@lang('user.driver_name')</dt>
			                <dd>{this.props.checkState.provider.first_name} {this.props.checkState.provider.last_name}</dd>
			                <dt>@lang('user.service_number')</dt>
				                <dd>{this.props.checkState.provider_service.service_number}</dd>
				                <dt>@lang('user.service_model')</dt>
				                <dd>{this.props.checkState.provider_service.service_model}</dd>
			                <dt>@lang('user.driver_rating')</dt>
			                <dd>
			                	<div className="rating-outer">
		                            <input type="hidden" value={this.props.checkState.provider.rating} name="rating" className="rating"/>
		                        </div>
			                </dd>
		            		<dt>@lang('user.payment_mode')</dt>
                        	<dd>{this.props.checkState.payment_mode}</dd>
                        	<dt>@lang('user.ride.km')</dt>
                        	<dd>{this.props.checkState.distance} kms</dd>
                        </dl>
		            	<h5><strong>@lang('user.ride.invoice')</strong></h5>
		            	<input type="hidden" name="request_id" value={this.props.checkState.id} />
		            	<dl className="dl-horizontal left-right">
                            @if($request->service_required == 'none' || $request->service_required == 'outstation')
            			 	<dt>@lang('user.ride.distance_travelled')</dt>
                            <dd>{this.props.checkState.distance} kms</dd>
                            <dt>@lang('user.ride.base_price')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.fixed}</dd>
                            <dt>@lang('user.ride.distance_price')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.distance}</dd>
                            <dt>Non Cluster price</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.non_geo_price}</dd>

                            <dt style={ (this.state.hide1==true) ? hide : show }><i>Geo Distance Price</i></dt>
                            <dd style={ (this.state.hide1==true) ? hide : show }><i>{this.props.currency}{this.props.checkState.payment.geo_fencing_total}</i></dd>
                             <dt style={ (this.state.hide1==true) ? hide : show }><i>Non Geo Distance Price</i></dt>
                            <dd style={ (this.state.hide1==true) ? hide : show }><i>{this.props.currency}{this.props.checkState.payment.none_geo_fencing_total}</i></dd> 

                            <dt>@lang('user.ride.minutes_price')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.minute}</dd>

                            <dt style={ (this.state.hide2==true) ? hide : show }><i>Geo Minute Price</i></dt>
                            <dd style={ (this.state.hide2==true) ? hide : show }><i>{this.props.currency}{this.props.checkState.payment.geo_fencing_minute}</i></dd>
                             <dt style={ (this.state.hide2==true) ? hide : show }><i>Non Geo Minute Price</i></dt>
                            <dd style={ (this.state.hide2==true) ? hide : show }><i>{this.props.currency}{this.props.checkState.payment.non_geo_fencing_minute}</i></dd>
                            @elseif($request->service_required == 'rental')
                            <dt>Rental Fare</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.minute}</dd>
                            @endif
                            <dt>@lang('user.ride.tax_price')</dt>
                            <dd>{this.props.currency}{this.props.checkState.payment.tax}</dd>
                            <dt>@lang('user.ride.total')</dt>
                            {this.props.checkState.use_wallet ?
								<span>
								<dt>@lang('user.ride.detection_wallet')</dt>
                            	<dd>{this.props.currency}{this.props.checkState.payment.wallet}</dd>  
                            	</span>
                            : ''
                            }
                            {this.props.checkState.payment.discount ?
								<span>
								<dt>@lang('user.ride.promotion_applied')</dt>
                            	<dd>{this.props.currency}{this.props.checkState.payment.discount}</dd>  
                            	</span>
                            : ''
                            }
                          	{this.props.checkState.payment.eta_discount ?
								<span>
								<dt>@lang('user.ride.eta_discount')</dt>
                            	<dd>{this.props.currency}{this.props.checkState.payment.eta_discount}</dd>  
                            	</span>
                            : ''
                            }
                            {this.props.checkState.payment.night_fare ?
								<span>
								<dt>@lang('user.ride.night_fare')</dt>
                            	<dd>{this.props.currency}{this.props.checkState.payment.night_fare}</dd>  
                            	</span>
                            : ''
                            }
                            <dd>{this.props.currency}{this.props.checkState.payment.total}</dd> 
                            <dt className="big">@lang('user.ride.amount_paid')</dt>
                            <dd className="big">{this.props.currency}{this.props.checkState.payment.total}</dd>
                        </dl>
                    	<button type="submit" className="full-primary-btn fare-btn">CONTINUE TO PAY - {this.props.currency}{this.props.checkState.payment.payable}</button>   
                    </form>
		        </div>
				);
			}
		});

		var Review = React.createClass({
			render: function(){
				interval();
				return (
				<form method="POST" action="{{url('/rate')}}">
				{{ csrf_field() }}</input>
                    <div className="rate-review">
                        <label>@lang('user.ride.rating')</label>
                        <div className="rating-outer">
                            <input type="hidden" value="1" name="rating" className="rating"/>
                        </div>
						<input type="hidden" name="request_id" value={this.props.checkState.id} />
                        <label>@lang('user.ride.comment')</label>
                        <textarea className="form-control" name="comment" placeholder="Write Comment"></textarea>
                    </div>
                    <button type="submit" className="full-primary-btn fare-btn">SUBMIT</button>   
                </form>

				);
			}
		});


		React.render(<MainComponent/>,document.getElementById("container"));
	</script>

@endsection
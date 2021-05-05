@extends('dispatcher.layout.base')

@section('title', 'Add Geo Fencing ')

@section('content')
<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white">
            <a href="{{ route('dispatcher.geo-fencing.index') }}" class="btn btn-default pull-right"><i class="fa fa-angle-left"></i> @lang('admin.back')</a>

            <h5 style="margin-bottom: 2em;">@lang('admin.geo_fencing.Add_Geo_Fencing')</h5>

            <form class="form-horizontal" action="{{route('dispatcher.geo-fencing.store')}}" method="POST" enctype="multipart/form-data" role="form">
                {{ csrf_field() }}
                <div class="form-group row">
                    <label for="name" class="col-xs-12 col-form-label">@lang('admin.geo_fencing.City_Name')</label>
                    <div class="col-xs-6">
                        <input class="form-control" type="text" value="{{ old('city_name') }}" name="city_name" required id="city_name" placeholder="City Name">
                    </div>
                </div>

                <div class="form-group row">
                    <label for="picture" class="col-xs-12 col-form-label">
                    @lang('admin.geo_fencing.Ranges')</label>
                    <div class="col-xs-10">

                        <div id="map" style="margin-left: 16px;"></div>
                        <div id="bar">
                            <p><a id="clear" href="#">Click here</a> to clear map.</p>
                        </div>
                        <input type="hidden" name="ranges" class="ranges" value="">

                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-xs-10">
                        <div class="row">
                            <div class="col-xs-12 col-sm-6 col-md-3">
                                <a href="{{ route('dispatcher.service.index') }}" class="btn btn-danger btn-block">@lang('admin.cancel')</a>
                            </div>
                            <div class="col-xs-12 col-sm-6 offset-md-6 col-md-3">
                                <button type="submit" class="btn btn-primary btn-block">@lang('admin.geo_fencing.Add_Geo_Fencing')</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')

<script src="https://maps.googleapis.com/maps/api/js?key={{Setting::get('map_key')}}&libraries=places,drawing&callback=initMap" async defer></script>
<script>
    var map;
    var polygon;
    var input = document.getElementById('pac-input');
    var s_latitude = document.getElementById('latitude');
    var s_longitude = document.getElementById('longitude');
    var s_address = document.getElementById('address');
    var arr = [];
    var selectedShape;

    var old_latlng = new Array();
    var markers = new Array();

    var OldPath = JSON.parse($('input.ranges').val());


    function initMap() {

        var userLocation = new google.maps.LatLng(3.1390, 101.6869);

        map = new google.maps.Map(document.getElementById('map'), {
            center: userLocation,
            zoom: 15,
        });

        var drawingManager = new google.maps.drawing.DrawingManager({
            drawingMode: google.maps.drawing.OverlayType.POLYGON,
            drawingControl: true,
            drawingControlOptions: {
                position: google.maps.ControlPosition.TOP_CENTER,
                drawingModes: [google.maps.drawing.OverlayType.POLYGON]
            },
            polygonOptions: {
                editable: true, 
                draggable: true,
                fillColor: '#ff0000', 
                strokeColor: '#ff0000', 
                strokeWeight: 1
            }
        });
        drawingManager.setMap(map);

        google.maps.event.addListener(drawingManager, 'overlaycomplete', function(e){
            var bounds = [];
            var layer_bounds = [];
            var newShape = e.overlay;
            if (e.type == google.maps.drawing.OverlayType.POLYGON) {
                var locations = e.overlay.getPath().getArray();
                arr.push(e);
                $.each(locations, function(index, value){
                    var markerLat = (value.lat()).toFixed(6);
                    var markerLng = (value.lng()).toFixed(6);
                    layer_bounds.push(new google.maps.LatLng((value.lat()).toFixed(6), (value.lng()).toFixed(6)));
                    json = {
                        'lat': markerLat,
                        'lng': markerLng
                    };
                    bounds.push(json);                  
                });
                $('input.ranges').val(JSON.stringify(bounds));

                overlayClickListener(e.overlay);
                drawingManager.setOptions({drawingMode:null,drawingControl:false});
                setSelection(newShape);
            }
        });

        $(document).on('click', '#clear', function(ev) {
            drawingManager.setMap(map);
            polygon.setMap(null);
            deleteSelectedShape();
            $('input.ranges').val('');
            ev.preventDefault();
            return false;
        });

        var old_polygon = [];

        $(OldPath).each(function(index, value){
            old_polygon.push(new google.maps.LatLng(value.lat, value.lng));
            old_latlng.push(new google.maps.LatLng(value.lat, value.lng));
        });
            
        polygon = new google.maps.Polygon({
            path: old_polygon,
            strokeColor: "#ff0000",
            strokeOpacity: 0.8,
            // strokeWeight: 1,
            fillColor: "#ff0000",
            fillOpacity: 0.3,
            editable: true,
            draggable: true,
        });
        
        polygon.setMap(map);
    }

    function createMarker(markerOptions) {
        var marker = new google.maps.Marker(markerOptions);
        markers.push(marker);
        old_latlng.push(marker.getPosition());
        return marker;
    }

    function overlayClickListener(overlay) {
        google.maps.event.addListener(overlay, "mouseup", function(event){
            var arr_loc = [];
            var locations = overlay.getPath().getArray();
            $.each(locations, function(index, value){
                var locLat = (value.lat()).toFixed(6);
                var locLng = (value.lng()).toFixed(6);
                ltlg = {
                    'lat': locLat,
                    'lng': locLng
                };
                arr_loc.push(ltlg);                 
            });
            $('input.ranges').val(JSON.stringify(arr_loc));
        });
    }

    function setSelection (shape) {
        if (shape.type == 'polygon') {
            clearSelection();
            shape.setEditable(true);
        }
        selectedShape = shape;
    }

    function clearSelection () {
        if (selectedShape) {
            console.log(selectedShape.type);
            if (selectedShape.type == 'polygon') {
                selectedShape.setEditable(false);
            }
            
            selectedShape = null;
        }
    }

    function deleteSelectedShape () {
        if (selectedShape) {
            $('input.ranges').val('');
            selectedShape.setMap(null);
        }
    }
</script>

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

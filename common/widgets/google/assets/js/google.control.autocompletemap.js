(function($) {

    $.fn.autoGMap = function(method) {

        var $this = $(this);
        var addressData = {
            route: '',
            street_number: '',
            locality: '',
            district: '',
            political: '',
            sublocality: '',
            sublocality_level_1: '',
            administrative_area_level_1: '',
            postal_code: '',
            country: '',
            country_short_name: '',
            latitude: '',
            longitude: '',
            formated_address: '',
        };
        
        var methods = {
            init: function(options) {
                    return this.each(function() {

                        var pluginOptions, map, geoCoder, autoInput, hiddenInput, autocomplete, marker, infoWindow;
                        var form = null;
                        
                        if ((typeof google !== 'object') || (typeof google.maps !== 'object')){
                            $('#' + options.addressInput).prop('disabled', true);
                            console.error('Google Maps Js does not loaded.');
                            return false;
                        }
                        console.info('Google Maps Js was loaded.');
                        
                        pluginOptions = {
                            center: new google.maps.LatLng(0.0, 0.0),
                            zoom: 8,
                            mapTypeId: google.maps.MapTypeId.ROADMAP,
                            id: "map_canvas",
                            addressInput: null,
                            extContainer: null,
                            controlBtn: null,
                            myLocationBtn: null,
                            detailInputs: null,
                        };            
                        pluginOptions = $.extend(pluginOptions, options);
                        
                        var data = $this.data('gMap');
                        if (!data) {
                            map = new google.maps.Map($this[0], pluginOptions);
                            geoCoder = new google.maps.Geocoder();
                            marker = new google.maps.Marker({
                                map: map,
                                anchorPoint: new google.maps.Point(0, -29),
                                position: pluginOptions['center'],
                                animation: google.maps.Animation.DROP,
                            });
                            infoWindow = new google.maps.InfoWindow();

                            data = {
                                target: $this,          // <div>
                                map: map,               // google map
                                geoCoder: geoCoder,     // google geoCoder
                                autoInput: null,        // autocomplete <input>
                                autocomplete: null,     // autocomplete google object
                                settings: pluginOptions,   // map options
                                marker: marker,         // map marker
                                infoWindow: infoWindow  // marker info window
                            };

                            $(this).data('gMap', data);
                            
                            form = $('#' + pluginOptions.addressInput).closest('form');
                            for (var component in addressData) {
                                hiddenInput = pluginOptions.addressInput + '[' + component + ']';
                                $(form).append('<input type="hidden" name="' + hiddenInput + '">');
                            }                            
                            //console.info('gMap object was defined.');
                        }else{
                            //console.error('gMap object does not defined.');
                        }

                        autoInput = $('#' + pluginOptions.addressInput);
                        autocomplete = new google.maps.places.Autocomplete(document.getElementById(pluginOptions.addressInput));
                        autocomplete.bindTo('bounds', data.map);

                        /*
                        if ((typeof autocomplete.gm_accessors_ !== 'object')){
                            console.error('Autocomplete input does not created.');
                        }else{
                            //text = objToString(autocomplete);
                            var cache = [];
                            var text = JSON.stringify(autocomplete, function(key, value) {
                                if (typeof value === 'object' && value !== null) {
                                    if (cache.indexOf(value) !== -1) {
                                        // Circular reference found, discard key
                                        return;
                                    }
                                    // Store value in our collection
                                    cache.push(value);
                                }
                                return value;
                            });
                            cache = null;
                            console.log(text);
                        }
                        */
                        
                        autoInput.blur(function(){
                            var value = $(this).val().trim();
                            var place = autocomplete.getPlace();
                            if((value == '') && !place){
                                $(this).closest('.gmap-container').removeClass('has-error').addClass('has-success');
                                marker.setVisible(false);
                                clearAddressData();
                            }else if((value.legth > 0) && (place.length > 0) && (value != place)){
                                $(this).closest('.gmap-container').removeClass('has-success').addClass('has-error');
                                marker.setVisible(false);
                                clearAddressData();
                            }else{
                                $(this).closest('.gmap-container').removeClass('has-error').addClass('has-success');
                            };
                        });

                        autoInput.keypress(function(event){
                            if ( event.which == 13 ) {
                                event.preventDefault();
                            }
                        });

                        form = autoInput.closest('form');
                        var modalDIV = form.closest('.modal');
                        if(modalDIV.length > 0){
                            var zIndex = modalDIV.css('z-index');
                            $('div.pac-container').css({zIndex: zIndex + 1});
                        }
                        
                        if(pluginOptions.extContainer){
                            var extContainer = form.find(pluginOptions.extContainer);
                            if(extContainer.length > 0){
                                if(extContainer.hasClass('collapse-panel')){
                                    extContainer.on('shown.bs.collapse', function () {
                                        var mapObj = data.map;
                                        google.maps.event.trigger(mapObj, "resize");
                                        mapObj.setCenter(data.marker.position);
                                    })                                    
                                }
                            }
                        }
                        
                        if(pluginOptions.controlBtn && (pluginOptions.controlBtn == true)){
                            pluginOptions.controlBtn = autoInput.closest('.gmap-container ').find('.input-group-btn .btn-open-map');
                        }

                        if(pluginOptions.controlBtn){
                            var controlBtn = form.find(pluginOptions.controlBtn);
                            if((pluginOptions.center.lat == null) || (pluginOptions.center.lng == null)){
                                controlBtn.prop('disabled', true);
                            }
                            if(controlBtn.length > 0){
                                $(controlBtn).click(function() {
                                    var gmapControl = $(this).closest('.gmap-container').find('.gmap-control');
                                    if(gmapControl.hasClass('hidden')){
                                        gmapControl.removeClass('hidden');
                                        setAddress(data.marker.position, true);
                                    }else{
                                        gmapControl.fadeToggle( "fast", "linear" );
                                    }
                                    var mapObj = data.map;
                                    google.maps.event.trigger(mapObj, "resize");
                                    mapObj.setCenter(data.marker.position);
                                });
                            }
                        }

                        if(pluginOptions.myLocationBtn && (pluginOptions.myLocationBtn == true)){
                            pluginOptions.myLocationBtn = autoInput.closest('.gmap-container ').find('.input-group-btn .btn-my-location');
                        }

                        if(pluginOptions.myLocationBtn){
                            var myLocationBtn = form.find(pluginOptions.myLocationBtn);
                            if((pluginOptions.center.lat == null) || (pluginOptions.center.lng == null)){
                                myLocationBtn.prop('disabled', true);
                            }
                            if(myLocationBtn.length > 0){
                                $(myLocationBtn).click(function() {
                                    var gmapControl = $(this).closest('.gmap-container').find('.gmap-control');
                                    if(gmapControl.hasClass('hidden')){
                                        gmapControl.removeClass('hidden');
                                    }
                                    var mapObj = data.map;
                                    google.maps.event.trigger(mapObj, "resize");
                                    if (geoPosition.init()) {
                                        geoPosition.getCurrentPosition(successGetMyLocation, errorHandler, {timeout: 5000});
                                    } else {
                                        error('Sorry, we are not able to use browser geolocation to find you.');
                                    }
                                });
                            }
                        }

                        google.maps.event.addListener(autocomplete, 'place_changed', placeChanged);
                        
                        $(this).data('gMap', $.extend(
                            data, {
                                autoInput: autoInput,
                                autocomplete: autocomplete,
                            })
                        );
                        
                        google.maps.event.addListener(data.map, 'click', function(event) {
                            if(event){
                                placeMarker(event.latLng);
                            }else{
                                placeMarker(pluginOptions.center);
                            }
                            $(this).closest('.gmap-container').removeClass('has-error').addClass('has-success');
                        });
                        
                        console.info('gMap object initialisation was complete.');
                        
                        //google.maps.event.addListener(data.marker, 'click', toggleBounce);
/*
                        data.geoCoder.geocode({'latLng': pluginOptions.center},
                            function(results, status) {
                                if (status == google.maps.GeocoderStatus.OK) {
                                    var district = '';
                                    for (var result in results) {
                                        var aComponents = results[result].address_components;
                                        for (var aComponent in aComponents) {
                                            var rTypes = aComponents[aComponent].types;
                                            for (var rType in rTypes) {
                                                if(rTypes[rType] == 'neighborhood'){
                                                    district = aComponents[aComponent].long_name;
                                                    break;
                                                }
                                            }
                                            if(district != ''){
                                                break;
                                            }
                                        }
                                        if(district != ''){
                                            break;
                                        }
                                    }
                                    if(district != ''){
                                        results[0].address_components.push({
                                            long_name: district,
                                            types: ['district']
                                        });
                                    }                                    
                                    if (results[0]) {
                                        saveAddress(results[0], true);
                                    }
                                }
                            }
                        );
                */
                    });
            },
            refresh: function(options){
                var data = $this.data('gMap');
                var pluginOptions = {};            
                pluginOptions = $.extend(pluginOptions, options);
                var mapObj = data.map;
                google.maps.event.trigger(mapObj, "resize");
                data.map.setCenter(data.marker.position);
            },            
            replace: function(latLng){
                var data = $this.data('gMap');
                data.map.setCenter(latLng);
                //data.map.setZoom(17);
                placeMarker(latLng);
            },
            destroy: function( ) {
                return this.each(function() {
                    $(window).unbind('.gMap');
                    data.map.remove();
                    $this.removeData('gMap');
                })
            },
        };

        if (!method || (typeof method === 'object')) {
            return methods.init.apply(this, arguments);
        } else if (methods[method]) {
            return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
        } else {
            $.error('Method ' + method + ' not exist in jQuery.gMap');
        };

        function placeChanged(){
            var data = $this.data('gMap');
            
            data.infoWindow.close();
            data.marker.setVisible(false);
            var place = data.autocomplete.getPlace();
            if (!place || !place.geometry) {
                $(data.autoInput).closest('.gmap-container').removeClass('has-success').addClass('has-error');
                data.marker.setVisible(false);
                clearAddressData();
                return false;
            }

            // If the place has a geometry, then present it on a map.
            if (place.geometry.viewport) {
              data.map.fitBounds(place.geometry.viewport);
            } else {
              data.map.setCenter(place.geometry.location);
            }
            data.map.setZoom(16);  // Why 17? Because it looks good.

            //data.marker.setIcon(/** @type {google.maps.Icon} */({
            //  url: place.icon,
            //  size: new google.maps.Size(71, 71),
            //  origin: new google.maps.Point(0, 0),
            //  anchor: new google.maps.Point(17, 34),
            //  scaledSize: new google.maps.Size(35, 35)
            //}));
            
            data.marker.setPosition(place.geometry.location);
            data.marker.setVisible(true);

            setAddress(place);
           // var address = saveAddress();
        }

        function placeMarker(location) {
            var data = $this.data('gMap');
            
            data.infoWindow.close();
            
            if (data.marker) { //on vérifie si le marqueur existe
                data.marker.setPosition(location); //on change sa position
            } else {
                data.marker = new google.maps.Marker({//on créé le marqueur
                    position: location,
                    map: data.map
                });
            }
            setAddress(location);
            $this.data('gMap').map.setCenter(data.marker.position);
        }

        function setAddress(location, firstTime) {
            var data = $this.data('gMap');
            var place = null; 
            if(location.geometry != undefined){
                place = location;
                location = place.geometry.location;
            }
            
            data.geoCoder.geocode({'latLng': location},
                function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        var district = '';
                        for (var result in results) {
                            var aComponents = results[result].address_components;
                            for (var aComponent in aComponents) {
                                var rTypes = aComponents[aComponent].types;
                                for (var rType in rTypes) {
                                    if(rTypes[rType] == 'neighborhood'){
                                        district = aComponents[aComponent].long_name;
                                        break;
                                    }
                                }
                                if(district != ''){
                                    break;
                                }
                            }
                            if(district != ''){
                                break;
                            }
                        }
                        if(district != ''){
                            results[0].address_components.push({
                                long_name: district,
                                types: ['district']
                            });
                        }
                        if (results[0]) {
                            data.autoInput.val(results[0].formatted_address);
                            var address = saveAddress(results[0], firstTime);
                            var placeName = place ? place.name : (address ? address.route + ' ' + address.street_number : '');
                            data.infoWindow.setContent(
                                "<div id='gm-info-window'>" +
                                    "<strong>" + placeName + "</strong>" +
                                    //"<br>" + address.formated_address +
                                    "<hr style='margin-top: 5px; margin-bottom: 5px;'>" +
                                    "Latitude: " + address.latitude +
                                    "<br>"+
                                    "Longitude: " + address.longitude +
                                "</div>"
                            );
                            data.infoWindow.open(data.map, data.marker);
                            $(data.autoInput).closest('.gmap-container').removeClass('has-error').addClass('has-success');
                            
                            var form = $('#' + data.settings.addressInput).closest('form');
                            if(data.settings.controlBtn){            
                                var controlBtn = form.find(data.settings.controlBtn);
                                controlBtn.prop('disabled', false);
                            }
                            if(data.settings.myLocationBtn){            
                                var myLocationBtn = form.find(data.settings.myLocationBtn);
                                myLocationBtn.prop('disabled', false);
                            }
                            
                        } else {
                            data.autoInput.val("No results");
                        }
                    } else {
                        data.autoInput.val(status);
                    }
                }
            );
        }
        
        function saveAddress(place, firstTime) {
            var componentAddress = {
                route: 'long_name',
                street_number: 'short_name',
                locality: 'long_name',
                district: 'long_name',
                political: 'long_name',
                sublocality: 'long_name',
                sublocality_level_1: 'long_name',
                administrative_area_level_1: 'short_name',
                administrative_area_level_2: 'short_name',
                postal_code: 'short_name',
                country: 'long_name'
            };

            var data = $this.data('gMap');
            var detailInputs = data.settings.detailInputs;
            
            if(detailInputs && !firstTime){
                for (var component in addressData) {
                    if(detailInputs[component]){
                        $(detailInputs[component]).val('');
                    }
                }                  
            }
                    
            if(!place){
                // Get the place details from the place object.
                place = data.autocomplete.getPlace();
            }
            
            clearAddressData();

            // Get each component of the address from the place details
            // and fill the corresponding field of the address.
            for (var i = 0; i < place.address_components.length; i++) {
                var addressType = place.address_components[i].types[0];
                if (componentAddress[addressType]) {
                    var value = place.address_components[i][componentAddress[addressType]];
                    addressData[addressType] = value;
                    if(addressType == 'country'){
                        addressData['country_short_name'] = place.address_components[i]['short_name'];
                    } else if(addressType == 'administrative_area_level_2'){
                        addressData['administrative_area_level_1'] = place.address_components[i]['short_name']
                    }
                    if(detailInputs && (detailInputs[addressType]) && !firstTime){
                        if($(detailInputs[addressType]).hasClass('tt-input')){
                            $(detailInputs[addressType]).typeahead('val', value);
                        }else{
                            $(detailInputs[addressType]).prop('value', value);
                        }
                    }
                }
            }
            
            addressData.latitude = place.geometry.location.lat();
            addressData.longitude = place.geometry.location.lng();
            addressData.formated_address = place.formatted_address;
            $(this).data('gMap', $.extend(
                data, {
                    addressData: addressData,
                })
            );
    
            fillAddressHiddenInput();
            return addressData;
        };
        
        function clearAddressData(){
            for (var component in addressData) {
                addressData[component] = '';
            }            
            fillAddressHiddenInput();
        }

        function fillAddressHiddenInput(){
            var data = $this.data('gMap');
            var form = $('#' + data.settings.addressInput).closest('form');
            var hiddenInput;
            for (var component in addressData) {
                hiddenInput = data.settings.addressInput + '[' + component + ']';
                form.find('[name="' + hiddenInput + '"]').val(addressData[component]);
            }      
        }
        
/*
        function toggleBounce() {
            if (data.marker.getAnimation() != null) {
                data.marker.setAnimation(null);
            } else {
                data.marker.setAnimation(google.maps.Animation.DROP);
            }
        }
*/
        function successGetMyLocation(position) {
            var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
            placeMarker(latlng);
        }

        function errorHandler(err) {
            //document.location.href = '/place/index?errorLocate';
        }

        return this;
    };

})(jQuery);
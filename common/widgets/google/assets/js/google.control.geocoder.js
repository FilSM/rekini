(function($) {

    $.fn.geoCoder = function(method) {
        
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

                    var pluginOptions, geoCoder, autoInput, hiddenInput, autocomplete;
                    var form = null;

                    if ((typeof google !== 'object') || (typeof google.maps !== 'object')){
                        $('#' + options.addressInput).prop('disabled', true);
                        console.error('Google Maps Js does not loaded.');
                        return false;
                    }
                    console.info('Google Maps Js was loaded.');

                    pluginOptions = {
                        id: "geocoder_canvas",
                        addressInput: null,
                        detailInputs: null,
                    };            
                    pluginOptions = $.extend(pluginOptions, options);

                    var data = $this.data('geoCoder');
                    if (!data) {
                    	geoCoder = new google.maps.Geocoder();
                        data = {
                            target: $this,          // <div>
                            geoCoder: geoCoder,     // google geoCoder
                            autoInput: null,        // autocomplete <input>
                            autocomplete: null,     // autocomplete google object
                            settings: pluginOptions,   // map options
                        };
                        $(this).data('geoCoder', data);
                            form = $('#' + pluginOptions.addressInput).closest('form');
                            for (var component in addressData) {
                                hiddenInput = pluginOptions.addressInput + '[' + component + ']';
                                $(form).append('<input type="hidden" name="' + hiddenInput + '">');
                            }                            
                    }

                    autoInput = $('#' + pluginOptions.addressInput);
                    autocomplete = new google.maps.places.Autocomplete(document.getElementById(pluginOptions.addressInput));
                    
                    autoInput.blur(function(){
                        var value = $(this).val().trim();
                        var place = autocomplete.getPlace();
                        if((value == '') && !place){
                            clearAddressData();
                        }else if((value.legth > 0) && (place.length > 0) && (value != place)){
                            clearAddressData();
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

                    google.maps.event.addListener(autocomplete, 'place_changed', placeChanged);
                        
                    $(this).data('geoCoder', $.extend(
                        data, {
                            autoInput: autoInput,
                            autocomplete: autocomplete,
                        })
                    );

                    console.info('geoCoder object initialisation was complete.');
                });
            },
            destroy: function( ) {
                return this.each(function() {
                    $(window).unbind('.geoCoder');
                    $this.removeData('geoCoder');
                })
            },

            getAddress: function( ) {
                return this.each(function() {
                    var data = $(this).data('geoCoder');
                    return data.addressData;
                })
            },
        };

        if (!method || (typeof method === 'object')) {
            return methods.init.apply(this, arguments);
        } else if (methods[method]) {
            return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
        } else {
            $.error('Method ' + method + ' not exist in jQuery.geoCoder');
        };

        function placeChanged(){
            var data = $this.data('geoCoder');
            
            var place = data.autocomplete.getPlace();
            if (!place || !place.geometry) {
                clearAddressData();
                return false;
            }

            setAddress(place);
        }

        function setAddress(location, firstTime) {
            var data = $this.data('geoCoder');
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
                            saveAddress(results[0], firstTime);
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

            var data = $this.data('geoCoder');
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
            
            addressData.formated_address = place.formatted_address;
            $(this).data('geoCoder', $.extend(
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
            var data = $this.data('geoCoder');
            var form = $('#' + data.settings.addressInput).closest('form');
            var hiddenInput;
            for (var component in addressData) {
                hiddenInput = data.settings.addressInput + '[' + component + ']';
                form.find('[name="' + hiddenInput + '"]').prop('value', addressData[component]);
            }      
        }
        return this;
    };

})(jQuery);
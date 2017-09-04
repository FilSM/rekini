(function($) {

    var methods = {
        init: function(options) {
            return this.each(function() {

                var pluginOptions = $.extend(
                        {
                            center: new google.maps.LatLng(0.0, 0.0),
                            zoom: 8,
                            mapTypeId: google.maps.MapTypeId.ROADMAP,
                            id: "map_canvas",
                            addressInput: null
                        },
                options
                        );
                var $mapControl = $(this);
                var data = $mapControl.data('gMap');

                if (!data) {
                    var map = new google.maps.Map($mapControl[0], pluginOptions);
                    var geo = new google.maps.Geocoder();
                    var marker = new google.maps.Marker({
                        position: pluginOptions['center'],
                        animation: google.maps.Animation.DROP,
                        map: map,
                    });
                    var infowindow = new google.maps.InfoWindow();
                    $(this).data('gMap', {
                        target: $mapControl,
                        mapObject: map,
                        geoObject: geo,
                        settings: pluginOptions,
                        mapMarker: marker,
                        markerInfowindow: infowindow
                    });
                }
            });
        },
        destroy: function( ) {

            return this.each(function() {

                var $this = $(this);
                var data = $this.data('gMap');

                // пространства имён рулят!!11
                $(window).unbind('.gMap');
                data.mapObject.remove();
                $this.removeData('gMap');

            })

        },
        setAddress: function( ) {
            return this.each(function() {
                var data = $(this).data('gMap');
                return data.addressData;
            })
        },
        placeMarker: function(location) {
            return this.each(function() {
                var $mapControl = $(this);
                var data = $mapControl.data('gMap');
                if (data.mapMarker) { //on vérifie si le marqueur existe
                    data.mapMarker.setPosition(location); //on change sa position
                } else {
                    data.mapMarker = new google.maps.Marker({//on créé le marqueur
                        position: location,
                        map: data.mapObject
                    });
                }
            })
        },
    };

    $.fn.gMap = function(method) {

        var $this = $(this);
        //var data = $this.data('gMap');

        if (methods[method]) {
            return methods[ method ].apply(this, Array.prototype.slice.call(arguments, 1));
        } else if (typeof method === 'object' || !method) {

            methods.init.apply(this, arguments);

            var data = $this.data('gMap');
            if (data && data.settings.addressInput) {
                google.maps.event.addListener(data.mapObject, 'click', function(event) {
                    placeMarker(event.latLng);
                });
                $('#' + data.settings.addressInput).bindTo('bounds', data.mapObject);

                //google.maps.event.addListener(data.mapMarker, 'click', toggleBounce);
            }

            return this;
        } else {
            $.error('Method ' + method + ' not exist in jQuery.gMap');
        }
        ;

        function placeMarker(location) {
            if (data.mapMarker) { //on vérifie si le marqueur existe
                data.mapMarker.setPosition(location); //on change sa position
            } else {
                data.mapMarker = new google.maps.Marker({//on créé le marqueur
                    position: location,
                    map: data.mapObject
                });
            }
            setAddress(location);
        }

        function setAddress(location) {
            data.geoObject.geocode({'latLng': location},
            function(results, status) {
                var addressInput = $('#' + data.settings.addressInput);
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                        addressInput.val(results[0].formatted_address);
                        var addressInputData = $(addressInput).data('geoCoder');
                        var geoObject = $(addressInput).data('geoCoder').geoObject;
                        addressInputData.geoObject.changed();
                    }
                    else {
                        addressInput.val("No results");
                    }
                }
                else {
                    addressInput.val(status);
                }
            });
        }
/*
        function toggleBounce() {
            if (data.mapMarker.getAnimation() != null) {
                data.mapMarker.setAnimation(null);
            } else {
                data.mapMarker.setAnimation(google.maps.Animation.DROP);
            }
        }
*/
        return this;
    };

})(jQuery);
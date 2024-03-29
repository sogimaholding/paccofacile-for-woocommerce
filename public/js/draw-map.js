jQuery(document).ready(function() {

    var $mappa = jQuery('.paccofacile-map');
    var postcode = $mappa.attr('data-postcode');
    var city = $mappa.attr('data-city');
    var carrier_id = $mappa.attr('data-carrier-id');
    var nonce = $mappa.attr('data-store-nonce');

    // @todo : chiamata ajax per recuperare la lista dei locker più vicini a postcode/city in ordine di vicinanza

    //console.log(paccofacile_ajax_object);

    /* console.log(postcode);
    console.log(city);

    console.log(ajaxurl); */

    var dataJson = {
        "postcode": postcode,
        "city": city,
        "carrier_id": carrier_id,
        "action": 'get_lockers',
        "_wpnonce": nonce
    };

    

    function getCityCoordinates(city, postcode) {

        return new Promise((resolve, reject) => {
            dataJsonMapPosition = {
                "postcode": postcode,
                "city": city,
                "action": 'getCityCoordinates',
                "_wpnonce": nonce
            };

            jQuery.ajax({
                type: 'POST',
                url: paccofacile_ajax_object.ajaxurl,
                data: dataJsonMapPosition,
                error: function(jqXHR, textStatus, errorThrown){
                    console.error(jqXHR);
                    console.error("The following error occured: " + textStatus);
                    console.error(errorThrown);
                },
                success: function(responseCityCoordinates) {
                    /* console.log(responseCityCoordinates); */
                    const coordinates = { longitude: responseCityCoordinates.data.longitude, latitude: responseCityCoordinates.data.latitude };

                    resolve(coordinates);
                }
            });
        })
    }

    
    if( $mappa.length > 0 ) {

        jQuery.ajax({
            type: 'POST',
            url: paccofacile_ajax_object.ajaxurl,
            data: dataJson,
            error: function(jqXHR, textStatus, errorThrown){
                console.error(jqXHR);
                console.error("The following error occured: " + textStatus);
                console.error(errorThrown);
            },
            success: function(response) {
                if(response.status == 400) {
                    console.log(response.message);
                    for(var i=0; i < response.message.length; i++) {
                        var error_div = jQuery('<div>'+response.message[i]+'</div>').addClass('notice notice-error');
                        var form = 
                        
                        form.prepend(error_div);
                    }
                } else if(response.status == 401) {
                    console.log(response.message);
                } else if(response.status == 200) {
                    var response = response.data;
    
                    var items = response;
    
                    function updateMap(items, coordinates) {
                    
                        // has the function initialized after the event trigger?
                        var lockers = [];
                        //jQuery('.woocommerce-shipping-fields #shipping_locker_field .woocommerce-input-wrapper').html('');
                        jQuery('#paccofacile-lockers-list').html('');
    
                        jQuery.each(items, function( index, value) {
                            var feature = new ol.Feature({
                                geometry: new ol.geom.Point(ol.proj.fromLonLat([value.longitude, value.latitude])),
                                name: value.description,
                            });
                    
                            const iconStyle = new ol.style.Style({
                                image: new ol.style.Icon({
                                    anchor: [0.5, 32],
                                    anchorXUnits: 'fraction',
                                    anchorYUnits: 'pixels',
                                    src: paccofacile_help_var.pluginUrl + '/images/marker_32.png',
                                }),
                            });
                    
                            feature.setStyle(iconStyle);
    
                            var active_locker = jQuery('#paccofacile-lockers-list').attr('data-active');
    
                            if( active_locker && active_locker == value.id_locker) {
                                var checked = 'checked';
                            } else {
                                var checked = '';
                            }
    
                            //var to_append = '<div class="item d-flex justify-content-between px-2 '+checked+'"><label>'+value.distance+'km - <b>'+value.description+'</b> '+value.address+' '+value.building_number+' '+value.city+' - '+value.postcode+' ('+value.province+')</label><a id="shipping_locker_'+value.id_locker+'" name="shipping_locker" data-address="'+value.description+' - '+value.address+' '+value.building_number+' '+value.city+' - '+value.postcode+' ('+value.province+') '+value.postcode+'" class="button confermaLocker" data-locker-id="'+value.id_locker+'">Scegli</a></div>';
                            var to_append = '<div class="item"><label><input type="radio" id="shipping_locker_'+value.id_locker+'" name="shipping_locker" '+checked+' value="'+value.id_locker+'">'+value.description+' <span>'+value.address+' '+value.city+' - '+value.postcode+' ('+value.province+')</span></label></div>';
                            
                            /* if( jQuery('.paccofacile-map').length == 0 ) {
                                console.log('length == 0');
                                jQuery('#shipping_locker_field').prepend('<div id="paccofacile-map" class="paccofacile-map" data-postcode="" data-city="" data-carrier-id=""><div id="popup" class="ol-popup"><a href="#" id="popup-closer" class="ol-popup-closer"></a><div id="popup-content"></div></div></div>');
                            } */
                            //jQuery('.woocommerce-shipping-fields #shipping_locker_field .woocommerce-input-wrapper').append(to_append);
    
                            jQuery('#paccofacile-lockers-list').append(to_append);
    
                    
                            lockers.push(feature);
                        });
                    
                        var map = new ol.Map({
                            target: 'paccofacile-map',
                            layers: [
                            new ol.layer.Tile({
                                source: new ol.source.OSM()
                            })
                            ],
                            view: new ol.View({
                            center: ol.proj.fromLonLat([coordinates.longitude, coordinates.latitude]),
                            zoom: 12
                            })
                        });
                        
                        var marker = new ol.layer.Vector({
                            source: new ol.source.Vector({
                                features: lockers
                            })
                        });
                        map.addLayer(marker);
                        
                        var container = document.getElementById('popup');
                        var content = document.getElementById('popup-content');
                        var closer = document.getElementById('popup-closer');
                        
                        var overlay = new ol.Overlay({
                            element: container,
                            autoPan: true,
                            autoPanAnimation: {
                                duration: 250
                            },
                            positioning: 'bottom-center'
                        });
                        map.addOverlay(overlay);
                        
                        if(closer) {
                            closer.onclick = function() {
                                overlay.setPosition(undefined);
                                closer.blur();
                                return false;
                            };
                        }
                        
                        map.on('singleclick', function (event) {
                            if (map.hasFeatureAtPixel(event.pixel) === true) {
                                var coordinate = event.coordinate;
                    
                                map.forEachFeatureAtPixel(event.pixel, function (feature, layer) {
                                    content.innerHTML = '<b>'+feature.get("name")+'</b>';
                                });
                    
                                overlay.setPosition(coordinate);
                            } else {
                                overlay.setPosition(undefined);
                                closer.blur();
                            }
                        });
    
                        
                        
                    }
    
                    getCityCoordinates(dataJson.city, dataJson.postcode).then((data) => {
                        var coordinates = data;
    
                        updateMap(items, coordinates);
                    });
    
                    
    
                    jQuery( 'body.woocommerce-checkout' ).on( 'updated_shipping_method', function(){
                        var city = jQuery('.woocommerce-shipping-fields #shipping_city').val();
                        var postcode = jQuery('.woocommerce-shipping-fields #shipping_postcode').val();
    
                        //console.log('updated_shipping_method');
    
                        getCityCoordinates(city, postcode).then((data) => {
                            var coordinates = data;
        
                            updateMap(items, coordinates);
                        });
                    });
    
                    jQuery( 'body.woocommerce-cart' ).on( 'updated_cart_totals', function(){
    
                        var city = jQuery('.shipping-calculator-form #calc_shipping_city').val();
                        var postcode = jQuery('.shipping-calculator-form #calc_shipping_postcode').val();
    
                        getCityCoordinates(city, postcode).then((data) => {
                            var coordinates = data;
        
                            updateMap(items, coordinates);
                        });
    
                    });
    
                }
                
            }
        });
    }


    jQuery('.woocommerce-cart .checkout-button').click(function(event) {

        event.preventDefault();
        var locker_id = jQuery('input[name="shipping_locker"]:checked').val();
        var nonce = jQuery('#woocommerce-cart-nonce').val();
        

        var dataJson = {
            "locker_id": locker_id,
            "action": 'locker_id_session',
            "_wpnonce": nonce
        };
        var btn = jQuery(this);
        var href = btn.attr('href');
    
        jQuery.ajax({
            type: 'POST',
            url: paccofacile_ajax_object.ajaxurl,
            //dataType: 'application/json',
            data: dataJson,
            error: function(jqXHR, textStatus, errorThrown){
                console.error(jqXHR);
                console.error("The following error occured: " + textStatus);
                console.error(errorThrown);
            },
            success: function(response) {
                /* console.log(response); */
                window.location.href = href;
            }
        })

    });


    jQuery(document).on('change', '.woocommerce-checkout #paccofacile-lockers-list input[type="radio"]', function() {
        var current_val = jQuery(this).val();

        jQuery('#shipping_locker').val(current_val);
    });



    jQuery('.woocommerce-checkout #shipping_locker_field').find('input').attr('type', 'hidden');
    


});



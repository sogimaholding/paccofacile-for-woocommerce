jQuery(document).ready(function() {

    let params = (new URL(document.location)).searchParams;
    let tab = params.get("tab");

    

    // @todo : chiamata ajax per recuperare la lista dei locker più vicini a postcode/city in ordine di vicinanza

    //console.log(paccofacile_ajax_object);

    /* console.log(postcode);
    console.log(city);

    console.log(ajaxurl); */

    

    if(tab == 'shipping_services') {

        var $mappa = jQuery('.paccofacile-map');
        var postcode = $mappa.attr('data-postcode');
        var city = $mappa.attr('data-city');
        var carrier_id = $mappa.attr('data-carrier-id');
        var nonce = $mappa.attr('data-store-nonce');

        var dataJson = {
            "postcode": postcode,
            "city": city,
            "carrier_id": carrier_id,
            "action": 'get_lockers',
            "_wpnonce": nonce
        };

        if($mappa && postcode && city) {

            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                //dataType: 'application/json',
                data: dataJson,
                error: function(jqXHR, textStatus, errorThrown){
                    console.error(jqXHR);
                    console.error("The following error occured: " + textStatus);
                    console.error(errorThrown);
                },
                success: function(response) {
                    /* console.log(response); */
                    if(response.status == 400) {
                        console.log(response);
                        /* for(var i=0; i < response.message.length; i++) { */
                            var error_div = jQuery('<div>'+response.message+'</div>').addClass('notice notice-error');
        
                            jQuery('.paccofacile_config .wrap').prepend(error_div);
                            
                        /* } */
                    } else if(response.status == 401) {
                        console.log(response.message);
                    } else if(response.status == 200) {
                        var response = response.data;
        
                        var items = response;
        
                        //console.log(response);
        
                        function updateMap(items) {
                            
                            // Code stuffs
                        
                            // has the function initialized after the event trigger?
                            //console.log('on updated_shipping_method: function fired'); 
                            var lockers = [];
                            jQuery('.woocommerce-shipping-fields #shipping_locker_field .woocommerce-input-wrapper').html('');
        
                            var chosen_locker = jQuery('.paccofacile-lockers-list').attr('data-pickup-locker');
        
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
                                        src: paccofacile_help_var.site_url + '/images/marker_32.png',
                                    }),
                                });
                        
                                feature.setStyle(iconStyle);
        
                                if(chosen_locker && chosen_locker == value.id_locker) {
                                    var checked = 'checked';
                                } else {
                                    var checked = '';
                                }
        
                                var to_append = '<div class="item d-flex justify-content-between px-2 '+checked+'"><label><small>'+value.distance+'km - <b>'+value.description+'</b> '+value.address+' '+value.building_number+' '+value.city+' - '+value.postcode+' ('+value.province+')</small></label><a id="shipping_locker_'+value.id_locker+'" name="shipping_locker" data-address="'+value.description+' - '+value.address+' '+value.building_number+' '+value.city+' - '+value.postcode+' ('+value.province+') '+value.postcode+'" class="button confermaLocker" data-locker-id="'+value.id_locker+'">Scegli</a></div>';
        
                                jQuery('.paccofacile-lockers-list').append(to_append);
        
                        
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
                                center: ol.proj.fromLonLat([items[0].longitude, items[0].latitude]),
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
        
                        updateMap(items);
                        /* console.log('log admin'); */
        
                        jQuery( document.body ).on( 'updated_shipping_method', function(){
                            updateMap(items);
                        });
                        /* jQuery( '.manage_pickup_modal_open' ).click(function() {
                            updateMap(items);
                        }); */
        
                    }
                    
                    //self.parent.tb_remove();
                }
            });

        }
    
        
    }



});



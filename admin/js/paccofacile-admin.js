(function ( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	$(function () {
		$( '.postbox-container .save_order' ).on( 'click', function () {
			// console.log( 'salavataggio premuto' );
			$( '#paccofacile' ).find( 'input, textarea, select' ).removeAttr( 'required' );
			// $('[name="paccofacile_billing_detail"]').removeAttr('required');
		});

		$( '.button[name="paccofacile_pay_order"]' ).on( 'click', function(event) {
			event.preventDefault();

			// console.log($(this).closest('.paccofacile_pay_order_form'));
			var container = $(this).closest('.paccofacile_pay_order_form');

			/* console.log(required);
			console.log(paccofacile_billing_detail); */

			var dataString = container.find('input, textarea, select').serialize();
			dataString += '&action=paccofacile_pay_order';

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: dataString,
				error: function(jqXHR, textStatus, errorThrown){
					console.error(jqXHR);
					console.error("The following error occured: " + textStatus, errorThrown);
				},
				success: function(data) {
					
					location.reload();

				}
			});




		});
		
		
		$('.button[name="paccofacile_ship_with"]').on('click', function(event) {
			event.preventDefault();

			var container = $(this).closest('.paccofacile_ship_with_form');

			var dataString = container.find('input, textarea, select').serialize();

			//console.log(dataString);

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: dataString,
				error: function(jqXHR, textStatus, errorThrown){
					console.error(jqXHR);
					console.error("The following error occured: " + textStatus, errorThrown);
				},
				success: function(data) {
					
					console.log(data);
					location.reload();

				}
			});




		});


		$('.add_carrier_form').on('submit', function(event) {
			event.preventDefault();

			var $lista_corrieri = $('.lista_corrieri:not(.api)');

			var $corriere = $(this).parent().parent();
			var $submit_button = $corriere.find('.add_carrier_button');
			
			var dataString = $(this).serialize();
			/* console.log(dataString); */
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: dataString,
				error: function(jqXHR, textStatus, errorThrown){
					console.error(jqXHR);
					console.error("The following error occured: " + textStatus, errorThrown);
				},
				success: function(data) {
					$corriere.addClass('spento');
					$submit_button.attr('disabled', 'disabled');

					if(data.image_url) {
						var image_html = '<div class="image"><img src="'+data.image_url+'" alt=""></div>';
					} else {
						var image_html = '';
					}

					$lista_corrieri.prepend('<div class="corriere serviceid_'+data.service_id+'" data-service-id="'+data.service_id+'"><div class="inner">'+image_html+'<h3>'+data.carrier_name+' '+data.service_name+' - '+data.box_type+'</h3><p>'+data.carrier_ship_time+'</p><form action="" class="delete_carrier_form" method="post"><input type="hidden" name="nonce" value="'+data.nonce+'" /><input type="hidden" name="action" value="delete_carrier" /><input type="hidden" name="post_id" value="'+data.new_post_ID+'"> <input type="submit" class="button button-primary delete_carrier" value="'+data.delete_button_label+'"></form></div></div>');

				}
			});
		});

		$(document).on('click', '.confermaLocker[name="shipping_locker"]', function() {
			//event.preventDefault();
			console.log('conferma locker');
			var locker_id = $(this).attr('data-locker-id');

			var form = $(this).closest('form');

			form.append('<input type="hidden" name="shipping_locker" value="'+locker_id+'">');

			var dataString = form.serialize();

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: dataString,
				error: function(jqXHR, textStatus, errorThrown){
					console.error(jqXHR);
					console.error("The following error occured: " + textStatus, errorThrown);
				},
				success: function(data) {
					console.log(data);

					self.parent.tb_remove();

				}
			});
		});
		
		
		$('.delete_carrier_form').on('submit', function(event) {
			event.preventDefault();

			var $corriere = $(this).parent().parent();
			var service_id = $corriere.attr('data-service-id');
			
			var $corriere_api = $('#add_courier_modal .serviceid_'+service_id);
			var $corriere_api_add_button = $corriere_api.find('.add_carrier_button');

			var $spinner = $(this).find('.spinner');

			$spinner.css('visibility','visible');
			
			var dataString = $(this).serialize();
			/* console.log(dataString); */
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: dataString,
				error: function(jqXHR, textStatus, errorThrown){
					console.error(jqXHR);
					console.error("The following error occured: " + textStatus, errorThrown);
				},
				success: function(data) {
					$corriere.remove();
					
					$corriere_api.removeClass('spento');
					$corriere_api_add_button.removeAttr('disabled');

					$spinner.css('visibility','hidden');
				}
			});
		});

		

		$( 'input[name="paccofacile_billing_detail"]' ).on('change', function() {
			var val = $(this).val();
			if( 2 == val ) {
				$( '.paccofacile_billing_bill' ).show();
			} else {
				$( '.paccofacile_billing_bill' ).hide();
			}
		});



		$('.add_box_form').on('submit', function(event) {
			event.preventDefault();

			var $lista_imballi = $('.lista_imballi');

			/* var $corriere = $(this).parent().parent();
			var $submit_button = $corriere.find('.add_box_button'); */
			
			//var dataString = $(this).serialize();

			var tipo = $(this).find('input[name="paccofacile_box_type"]:checked').val();

			//console.log(tipo);

			var dataString = {
				"action": $(this).find('[name="action"]').val(),
				"nome": $(this).find('[name="box_name"]').val(),
				"tipo": tipo,
				"_wpnonce": $(this).find('[name="_wpnonce"]').val(),
			};



			if(tipo == 1) { // pacco
				dataString.dim1 = $(this).find('input[name="dim1"]').val();
				dataString.dim2 = $(this).find('input[name="dim2"]').val();
				dataString.dim3 = $(this).find('input[name="dim3"]').val();
				dataString.volume = dataString.dim1 * dataString.dim2 * dataString.dim3;
				dataString.peso_max = $(this).find('input[name="max_weight"]').val();
			} else if(tipo == 2) { // busta
				var variante = $(this).find('[name="envelope_type"] option:selected');

				dataString.tipo_variante = variante.val();
				
				dataString.dim1 = variante.attr('data-dim1');
				dataString.dim2 = variante.attr('data-dim2');
				dataString.dim3 = variante.attr('data-dim3');
				dataString.volume = dataString.dim1 * dataString.dim2 * dataString.dim3;
				dataString.peso_max = variante.attr('data-pesomax');
			} else if(tipo == 3) { // pallet
				var variante = $(this).find('[name="pallet_type"] option:selected');

				dataString.tipo_variante = variante.val();
				
				dataString.dim1 = variante.attr('data-dim1');
				dataString.dim2 = variante.attr('data-dim2');
				dataString.dim3 = 0;
				dataString.altezza_max = $(this).find('input[name="max_height"]').val();
				dataString.volume = dataString.dim1 * dataString.dim2 * dataString.altezza_max;
				dataString.peso_max = variante.attr('data-pesomax');
			}

			if(dataString.action == 'edit_box') {
				dataString.imballo_id = $(this).find('input[name="imballo_id"]').val();
			}

			//console.log(dataString);
			//console.log('ajaxurl --> '+ajaxurl);


			/*
			"dim1": 10,
			"dim2": 10,
			"dim3": 10,
			"peso_max": 1,
			"volume": 1000,
			"altezza_max": 0
			*/

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: dataString,
				error: function(jqXHR, textStatus, errorThrown){
					console.error(jqXHR);
					console.error("The following error occured: " + textStatus, errorThrown);
				},
				success: function(data) {
					
					self.parent.tb_remove();
					
					document.location.reload();
					
					
				}
			});
		});

		$('.delete_box_form').on('submit', function(event) {
			event.preventDefault();

			var $shipping_box = $(this).parent().parent();
			var $spinner = $(this).find('.spinner');

			$spinner.css('visibility','visible');
			
			var dataString = $(this).serialize();

			//console.log(dataString);
			/* console.log(dataString); */
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: dataString,
				error: function(jqXHR, textStatus, errorThrown){
					console.error(jqXHR);
					console.error("The following error occured: " + textStatus, errorThrown);
				},
				success: function(data) {
					$shipping_box.remove();
					$spinner.css('visibility','hidden');
				}
			});
		});

		/* $( '.paccofacile_pallet_type' ).hide();
		$( '.paccofacile_pallet_max_height' ).hide();
		$( '.paccofacile_envelope_type' ).hide(); */

		function show_hide_tipo_fields( type ) {
			if(type == '1') { // pacco
				$( '.paccofacile_box_fields' ).show();
				$( '.paccofacile_pallet_type' ).hide();
				$( '.paccofacile_pallet_max_height' ).hide();
				$( '.paccofacile_envelope_type' ).hide();
			} else if(type == '3') { // pallet
				$( '.paccofacile_pallet_type' ).show();
				$( '.paccofacile_pallet_max_height' ).show();
				$( '.paccofacile_box_fields' ).hide();
				$( '.paccofacile_envelope_type' ).hide();
			} else if(type == '2') { // busta
				$( '.paccofacile_envelope_type' ).show();
				$( '.paccofacile_box_fields' ).hide();
				$( '.paccofacile_pallet_type' ).hide();
				$( '.paccofacile_pallet_max_height' ).hide();
			}
		}

		$( 'input[name="paccofacile_box_type"]' ).on('change', function() {
			var type = $(this).val();
			show_hide_tipo_fields( type );
		});

		$( '.modale-modifica' ).on('click', function() {
			var imballo_id = $(this).attr('data-imballo-id');

			/* console.log('modale aperta id --> '+imballo_id); */

			var modale = $('.edit_box_modal_'+imballo_id);
			var type = modale.find('input[name="paccofacile_box_type"]:checked').val();

			show_hide_tipo_fields( type );
		});


		function pfwcShippingConditionShowHideMinAmountFields( el ) {
			var form = $( el ).closest( 'form' );
			var activationConditionField = $( el ).closest( 'tr' );

			var arrayToHide = [];
			var arrayToShow = [];

			var minWeightField = $( '#woocommerce_paccofacile_shipping_method_min_weight', form );
			var maxWeightField = $( '#woocommerce_paccofacile_shipping_method_max_weight', form );
			
			var minPriceField = $( '#woocommerce_paccofacile_shipping_method_min_price', form );
			var maxPriceField = $( '#woocommerce_paccofacile_shipping_method_max_price', form );

			if ( 'none' === $( el ).val() ) {

				arrayToHide.push(minWeightField.attr('id'));
				//minWeightField.hide();

				arrayToHide.push(maxWeightField.attr('id'));
				//maxWeightField.hide();
				
				arrayToHide.push(minPriceField.attr('id'));
				//minPriceField.hide();

				arrayToHide.push(maxPriceField.attr('id'));
				//maxPriceField.hide();

				//activationConditionField.find('th, td').css('border-bottom', '1px solid #f8f8f8');
			} else if ( 'by_weight' === $( el ).val() ) {
				
				arrayToShow.push(minWeightField.attr('id'));
				//minWeightField.show();

				arrayToShow.push(maxWeightField.attr('id'));
				//maxWeightField.show();

				arrayToHide.push(minPriceField.attr('id'));
				//minPriceField.hide();

				arrayToHide.push(maxPriceField.attr('id'));
				//maxPriceField.hide();

				// minWeightField.find( 'th, td' ).css('border-bottom', 'none');
				// activationConditionField.find('th, td').css('border-bottom', 'none');
			} else if ( 'by_price' === $( el ).val() ) {
				arrayToHide.push(minWeightField.attr('id'));
				//minWeightField.hide();

				arrayToHide.push(maxWeightField.attr('id'));
				//maxWeightField.hide();

				arrayToShow.push(minPriceField.attr('id'));
				//minPriceField.show();

				arrayToShow.push(maxPriceField.attr('id'));
				//maxPriceField.show();

				//minPriceField.find( 'th, td' ).css('border-bottom', 'none');
				//activationConditionField.find('th, td').css('border-bottom', 'none');
			}

			arrayToHide.map(function(value) {
				var field = $( '#'+value, form ).closest( 'tr' );
				if(field.length > 0) {
					field.hide();
				} else {
					$( '#'+value, form ).closest( 'fieldset' ).hide();
					$('label[for="'+value+'"]').hide();
				}
			});
			
			arrayToShow.map(function(value) {
				var field = $( '#'+value, form ).closest( 'tr' );
				if(field.length > 0) {
					field.show();
				} else {
					$( '#'+value, form ).closest( 'fieldset' ).show();
					$('label[for="'+value+'"]').show();
				}
			})


		}
		
		function pfwcShippingConditionShowHidePriceVariationFields( el ) {
			var form = $( el ).closest( 'form' );

			//console.log('form', form);

			var arrayToHide = [];
			var arrayToShow = [];

			var priceVariationField = form.find('#woocommerce_paccofacile_shipping_method_price_variation');

			var variationTypeField = $( '#woocommerce_paccofacile_shipping_method_price_variation_type', form );
			var variationAmountField = $( '#woocommerce_paccofacile_shipping_method_price_variation_amount', form );
			var variationPercentageField = $( '#woocommerce_paccofacile_shipping_method_price_variation_percentage', form );

			if ( 'none' === $( el ).val() ) {
				
				arrayToHide.push(variationTypeField.attr('id'));
				arrayToHide.push(variationAmountField.attr('id'));
				arrayToHide.push(variationPercentageField.attr('id'));

			} else {
				arrayToShow.push(variationTypeField.attr('id'));
				
				//variationTypeField.show();
				if( variationTypeField.val() == 'fixed' ) {
					arrayToShow.push(variationAmountField.attr('id'));
					arrayToHide.push(variationPercentageField.attr('id'));

					//variationAmountField.show();
					//variationPercentageField.hide();
				} else if( variationTypeField.val() == 'percentage' ) {
					arrayToHide.push(variationAmountField.attr('id'));
					arrayToShow.push(variationPercentageField.attr('id'));

					//variationAmountField.hide();
					//variationPercentageField.show();
				}
				/* variationTypeField.find('#woocommerce_paccofacile_shipping_method_price_variation_type').trigger( 'change' ); */

				//priceVariationField.find('th, td').css('border-bottom', 'none');
				//variationTypeField.find( 'th, td' ).css('border-bottom', 'none');
			}

			arrayToHide.map(function(value) {
				var field = $( '#'+value, form ).closest( 'tr' );
				if(field.length > 0) {
					field.hide();
				} else {
					$( '#'+value, form ).closest( 'fieldset' ).hide();
					$('label[for="'+value+'"]').hide();
				}
			});
			
			arrayToShow.map(function(value) {
				var field = $( '#'+value, form ).closest( 'tr' );
				if(field.length > 0) {
					field.show();
				} else {
					$( '#'+value, form ).closest( 'fieldset' ).show();
					$('label[for="'+value+'"]').show();
				}
			})
		}
		
		function pfwcShippingConditionShowHidePriceVariationTypeFields( el ) {
			var form = $( el ).closest( 'form' );
			
			//var priceVariationField = $( '#woocommerce_paccofacile_shipping_method_price_variation', form );
			//var variationTypeField = $( el ).closest( 'tr' );

			arrayToHide = [];
			arrayToShow = [];

			var variationAmountField = $( '#woocommerce_paccofacile_shipping_method_price_variation_amount', form );
			var variationPercentageField = $( '#woocommerce_paccofacile_shipping_method_price_variation_percentage', form );

			//priceVariationField.find('th, td').css('border-bottom', 'none');
			//variationTypeField.find( 'th, td' ).css('border-bottom', 'none');
			
			if ( 'fixed' === $( el ).val() ) {
				arrayToShow.push(variationAmountField.attr('id'));
				//variationAmountField.show();
				
				arrayToHide.push(variationPercentageField.attr('id'));
				//variationPercentageField.hide();
			} else if( 'percentage' === $( el ).val() ) {
				arrayToHide.push(variationAmountField.attr('id'));
				//variationAmountField.hide();

				arrayToShow.push(variationPercentageField.attr('id'));
				//variationPercentageField.show();
			}

			arrayToHide.map(function(value) {
				var field = $( '#'+value, form ).closest( 'tr' );
				if(field.length > 0) {
					field.hide();
				} else {
					$( '#'+value, form ).closest( 'fieldset' ).hide();
					$('label[for="'+value+'"]').hide();
				}
			});
			
			arrayToShow.map(function(value) {
				var field = $( '#'+value, form ).closest( 'tr' );
				if(field.length > 0) {
					field.show();
				} else {
					$( '#'+value, form ).closest( 'fieldset' ).show();
					$('label[for="'+value+'"]').show();
				}
			})
		}

		$( document.body ).on( 'change', '#woocommerce_paccofacile_shipping_method_activation_condition', function() {
			pfwcShippingConditionShowHideMinAmountFields( this );
		});
		
		$( document.body ).on( 'change', '#woocommerce_paccofacile_shipping_method_price_variation, #woocommerce_paccofacile_shipping_method_price_variation_type', function() {
			pfwcShippingConditionShowHidePriceVariationFields( this );
		});
		
		$( document.body ).on( 'change', '#woocommerce_paccofacile_shipping_method_price_variation_type', function() {
			pfwcShippingConditionShowHidePriceVariationTypeFields( this );
		});

		// Change while load.
		$( '#woocommerce_paccofacile_shipping_method_activation_condition' ).trigger( 'change' );
		$( '#woocommerce_paccofacile_shipping_method_price_variation' ).trigger( 'change' );
		$( document.body ).on( 'wc_backbone_modal_loaded', function( evt, target ) {
			if ( 'wc-modal-shipping-method-settings' === target ) {
				pfwcShippingConditionShowHideMinAmountFields( $( '#wc-backbone-modal-dialog #woocommerce_paccofacile_shipping_method_activation_condition', evt.currentTarget ) );
				pfwcShippingConditionShowHidePriceVariationFields( $( '#wc-backbone-modal-dialog #woocommerce_paccofacile_shipping_method_price_variation', evt.currentTarget ) );
			}
		} );


		$('.modal_customes_form [name="customes_submit"]').on('click',function(event) {
			event.preventDefault();
			
			//var dataString = $('.modal_customes_form').serialize();
			var dataString = '';
			var dataArray = [];
			$('.modal_customes_form input').each(function() {
				if($(this).val() != '' && $(this).val() != null) {
					dataArray.push(`${$(this).attr('name')}=${$(this).val()}`);
				}
			});

			dataArray.push('action=add_shipping_customes');
			

			dataString = dataArray.join('&');

			// single=Single&multiple=Multiple&multiple=Multiple3&check=check2&radio=radio1

			//console.log(dataString);
			var form = $('.modal_customes_form');
			/* console.log(form); */
			
			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: dataString,
				error: function(jqXHR, textStatus, errorThrown){
					console.error(jqXHR);
					console.error("The following error occured: " + textStatus, errorThrown);
				},
				success: function(data) {
					console.log(dataString);
					if(data.status == 400) {
						console.log(data.message);
						for(var i=0; i < data.message.length; i++) {
							var error_div = $('<div>'+data.message[i]+'</div>').addClass('notice notice-error');
							
							form.prepend(error_div);
						}
					} else if(data.status == 401) {
						console.log(data.message);
					} else if(data.status == 200) {
						console.log('success');
					}

					
					
					//self.parent.tb_remove();
				}
			});
		});

		/* $('.js_field-city').select2(); */
		$('.js_field-city').select2({
			ajax: {
				type: 'POST',
                url: ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function (data) {
                    return {
                        iso_code: $(this).closest('.edit_address').find('#_shipping_country').val(),
						city: $(this).val(),
						action: 'search_locality',
						woocommerce_meta_nonce: $(this).closest('#woocommerce-order-data').find('#woocommerce_meta_nonce').val(),
                    };
                },
                processResults: function (response) {
					/* console.log(response.data); */
                    return {
                        results: response.data
                    };
                },
                cache: true
            }
		});

	});

})( jQuery );

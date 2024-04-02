<?php
/**
 * WooCommerce functions
 *
 * Filters and actions that edits woocommerce.

 * @link       #
 * @since      1.0.0
 * @package    Paccofacile
 * @subpackage Paccofacile/includes
 * @author     Francesco Barberini <supporto.tecnico@paccofacile.it>
 */

require_once PACCOFACILE_PATH . '/includes/class-paccofacile-api.php';

/**
 * Paccofacile shipping method function
 *
 * @return void
 */
function paccofacile_shipping_method() {
	if ( ! class_exists( 'Paccofacile_Shipping_Method' ) ) {
		/**
		 * Paccofacile_Shipping_Method
		 */
		class Paccofacile_Shipping_Method extends WC_Shipping_Method {
			/**
			 * Price policy
			 *
			 * @var [type]
			 */
			public $price_policy;

			/**
			 * Type class cost calculation.
			 *
			 * @var [type]
			 */
			public $class_cost_calculation_type;

			/**
			 * Service id.
			 *
			 * @var int
			 */
			public $service_id;

			/**
			 * Constructor for your shipping class
			 *
			 * @access public
			 * @param int $instance_id Instance id.
			 * @return void
			 */
			public function __construct( $instance_id = 0 ) {
				parent::__construct( $instance_id );

				$this->set_available_shipping_methods();
			}

			/**
			 * Set available shipping mehods.
			 *
			 * @return void
			 */
			public function set_available_shipping_methods() {

				$this->id = 'paccofacile_shipping_method';

				$this->method_title = __( 'Paccofacile.it Shipping', 'paccofacile-for-woocommerce' );
				$this->title        = $this->get_option( 'title', __( 'Paccofacile.it Shipping', 'paccofacile-for-woocommerce' ) );

				$this->method_description = __( 'Custom Shipping Method for Paccofacile.it', 'paccofacile-for-woocommerce' );

				$this->init();

				$this->enabled  = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
				$this->supports = array(
					'shipping-zones',
					'instance-settings',
					'instance-settings-modal',
				);
			}

			/**
			 * Init your settings
			 *
			 * @access public
			 * @return void
			 */
			public function init() {
				// Load the settings API.
				$this->init_form_fields();
				$this->init_settings();

				$this->price_policy                = $this->get_option( 'price_policy', __( 'Paccofacile.it prices', 'paccofacile-for-woocommerce' ) );
				$this->carrier                     = $this->get_option( 'carrier', 'notset' );
				$this->class_cost_calculation_type = $this->get_option( 'class_cost_calculation_type', 'class' );

				if ( 'notset' !== $this->carrier && 'none' !== $this->carrier ) {
					$this->service_id = substr( $this->carrier, strpos( $this->carrier, '_' ) + 1 );

					$service = get_available_shipping_methods( $this->service_id );
					if ( $service->have_posts() ) {
						foreach ( $service->posts as $corriere ) {
							$this->method_title = get_the_title( $corriere->ID );
						}
					}
				} elseif ( 'none' === $this->carrier ) {
					$this->service_id   = 'none';
					$this->method_title = 'Tutti i corrieri';
				}

				// Save settings in admin if you have any defined.
				add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
			}

			/**
			 * Define settings field for this shipping

			 * @return void
			 */
			public function init_form_fields() {

				$this->instance_form_fields = include 'settings-paccofacile-shipping.php';
			}

			/**
			 * This function is used to calculate the shipping cost. Within this function we can check for weights, dimensions and other parameters.
			 *
			 * @access public
			 * @param array $package Parcels.
			 * @return void
			 */
			public function calculate_shipping( $package = array() ) {

				if ( 'none' !== $this->service_id ) {
					$rate = array(
						'label'     => $this->get_option( 'title', __( 'Paccofacile.it Shipping Method', 'paccofacile-for-woocommerce' ) ),
						'cost'      => '0',
						'calc_tax'  => 'per_item',
						'meta_data' => array(
							'service_id' => $this->service_id,
						),
					);
					// Register the rate.
					$this->add_rate( $rate );
				} else {
					$carriers = get_available_shipping_methods();
					if ( $carriers->have_posts() ) {
						while ( $carriers->have_posts() ) {
							$carriers->the_post();
							$this->service_id = get_post_meta( get_the_ID(), 'service_id', true );

							$rate = array(
								'id'        => 'paccofacile_shipping_' . $this->service_id,
								'label'     => $this->get_option( 'title', __( 'Paccofacile.it Shipping Method', 'paccofacile-for-woocommerce' ) ),
								'cost'      => '0',
								'calc_tax'  => 'per_item',
								'meta_data' => array(
									'service_id' => $this->service_id,
								),
							);
							// Register the rate.
							$this->add_rate( $rate );
						}
						$carriers->reset_postdata();
					}
				}
			}
		}
	}
}

add_action( 'woocommerce_shipping_init', 'paccofacile_shipping_method' );

/**
 * Add Paccofacile Shipping Method.
 *
 * @param array $methods Methods.
 * @return array
 */
function add_paccofacile_shipping_method( $methods ) {
	$methods['paccofacile_shipping_method'] = 'Paccofacile_Shipping_Method';
	return $methods;
}

add_filter( 'woocommerce_shipping_methods', 'add_paccofacile_shipping_method' );


/**
 * Get available shipping methods.
 *
 * @param mixed $service_id Service id.
 * @return $carriers
 */
function get_available_shipping_methods( $service_id = false ) {
	$service = array();

	if ( false !== $service_id ) {
		$service = array(
			'meta_key'   => 'service_id',
			'meta_value' => $service_id,
		);
	}

	$args_carriers = array(
		'post_type' => 'carrier',
	);
	$args_carriers = array_merge( $args_carriers, $service );

	$carriers = new WP_Query( $args_carriers );

	return $carriers;
}

/**
 * Validate order.
 *
 * @param [type] $posted Posted data.
 * @return void
 */
function paccofacile_validate_order( $posted ) {

	$packages = WC()->shipping->get_packages();

	$chosen_methods = WC()->session->get( 'chosen_shipping_methods' );

	if ( is_array( $chosen_methods ) && in_array( 'paccofacile_shipping_method', $chosen_methods ) ) {

		foreach ( $packages as $i => $package ) {

			if ( 'paccofacile_shipping_method' !== $chosen_methods[ $i ] ) {

				continue;
			}

			$weight = 0;

			foreach ( $package['contents'] as $item_id => $values ) {
				$_product = $values['data'];
				$weight   = $weight + $_product->get_weight() * $values['quantity'];
			}

			$weight = wc_get_weight( $weight, 'kg' );

			/*
			Commented out code.
			if ( $weight > $weightLimit ) {

				$message = sprintf( __( 'Sorry, %d kg exceeds the maximum weight of %d kg for %s', 'paccofacile-for-woocommerce' ), $weight, $weightLimit, $Paccofacile_Shipping_Method->title );

				$messageType = "error";

				if ( ! wc_has_notice( $message, $messageType ) ) {

					wc_add_notice( $message, $messageType );

				}
			}
			*/
		}
	}
}

add_action( 'woocommerce_review_order_before_cart_contents', 'paccofacile_validate_order', 10 );
add_action( 'woocommerce_after_checkout_validation', 'paccofacile_validate_order', 10 );

/**
 * Create parcels
 *
 * @param array $products Products array.
 * @return array
 */
function create_parcels_object( $products ) {

	$boxes = prepare_boxes_payload_bin_packing();

	$paccofacile_api = Paccofacile_Api::get_instance();

	$payload_binpacking = array(
		'unit_misure_weight'    => 'kg',
		'unit_misure_dimension' => 'cm',
		'imballo_list'          => $boxes,
	);

	// Get cart items for the current shipping package (to calculate package weight).
	$packable_items   = array();
	$unpackable_items = array();
	$parcels_products = array();

	foreach ( $products as $item ) {
		$prod_id        = $item['product_id'];
		$no_pack_needed = get_post_meta( $prod_id, 'no_pack_needed', true );

		if ( ! $item['data'] ) {
			$item['data'] = wc_get_product( $item['product_id'] );
		}

		switch ( get_option( 'woocommerce_weight_unit' ) ) {
			case 'kg':
				$item_weight = $item['data']->get_weight();
				break;
			case 'g':
				$item_weight = $item['data']->get_weight() / 1000;
				break;
			case 'lbs':
				$item_weight = $item['data']->get_weight() / 2.205;
				break;
			case 'oz':
				$item_weight = $item['data']->get_weight() / 35.274;
				break;
			default:
				$item_weight = $item['data']->get_weight();
				break;
		}

		switch ( get_option( 'woocommerce_dimension_unit' ) ) {
			case 'cm':
				$item_width  = $item['data']->get_width();
				$item_height = $item['data']->get_height();
				$item_depth  = $item['data']->get_length();
				break;
			case 'm':
				$item_width  = $item['data']->get_width() * 100;
				$item_height = $item['data']->get_height() * 100;
				$item_depth  = $item['data']->get_length() * 100;
				break;
			case 'mm':
				$item_width  = $item['data']->get_width() * 0.1;
				$item_height = $item['data']->get_height() * 0.1;
				$item_depth  = $item['data']->get_length() * 0.1;
				break;
			case 'in':
				$item_width  = $item['data']->get_width() * 2.54;
				$item_height = $item['data']->get_height() * 2.54;
				$item_depth  = $item['data']->get_length() * 2.54;
				break;
			case 'yd':
				$item_width  = $item['data']->get_width() * 91.44;
				$item_height = $item['data']->get_height() * 91.44;
				$item_depth  = $item['data']->get_length() * 91.44;
				break;
			default:
				$item_width  = $item['data']->get_width();
				$item_height = $item['data']->get_height();
				$item_depth  = $item['data']->get_length();
				break;
		}

		if ( ! $no_pack_needed ) {
			$temp_packable_item['name']     = wp_json_encode(
				array(
					'id'   => $prod_id,
					'name' => $item['data']->get_name(),
				)
			);
			$temp_packable_item['width']    = $item_width;
			$temp_packable_item['height']   = $item_height;
			$temp_packable_item['depth']    = $item_depth;
			$temp_packable_item['weight']   = $item_weight;
			$temp_packable_item['quantity'] = $item['quantity'];

			$packable_items[] = $temp_packable_item;

		} else {
			$temp_unpackable_item['shipment_type'] = 1;
			$parcels_item_temp['box_width']        = $item_width;
			$temp_unpackable_item['dim1']          = $item_width;
			$parcels_item_temp['box_depth']        = $item_depth;
			$temp_unpackable_item['dim2']          = $item_depth;
			$parcels_item_temp['box_height']       = $item_height;
			$temp_unpackable_item['dim3']          = $item_height;
			$parcels_item_temp['box_weight']       = $item_weight;
			$temp_unpackable_item['weight']        = $item_weight;

			$parcels_item_temp['box_id']   = '';
			$parcels_item_temp['box_name'] = '';
			$parcels_item_temp['box_type'] = 1;
			$parcels_item_temp['products'] = array(
				array(
					'id'     => $prod_id,
					'name'   => $item['data']->get_name(),
					'width'  => $item_width,
					'depth'  => $item_depth,
					'height' => $item_height,
				),
			);

			for ( $i = 0; $i < $item['quantity']; $i++ ) {
				$unpackable_items[] = $temp_unpackable_item;
				$parcels_products[] = $parcels_item_temp;
			}
		}
	}

	$payload_binpacking['articolo_list'] = $packable_items;

	$response_binpacking = array();
	if ( count( $packable_items ) > 0 ) {
		// ALGORITMO BIN PACKING.
		$response_binpacking = $paccofacile_api->post( 'bin-packing', array(), $payload_binpacking );
	}

	$parcels = $unpackable_items;

	if ( array_key_exists( 'data', $response_binpacking ) && ! empty( $response_binpacking['data'] ) ) {
		$binpacking_data       = $response_binpacking['data'];
		$count_binpacking_data = count( $binpacking_data );
		for ( $i = 0; $i < $count_binpacking_data; $i++ ) {

			$box_data = json_decode( $binpacking_data[ $i ]['box_name'], true );

			$temp_parcel_order['box_type'] = $box_data['box_type'];
			$temp_parcel['shipment_type']  = $box_data['box_type'];
			$temp_parcel_order['box_id']   = $box_data['box_id'];
			$temp_parcel_order['box_name'] = $box_data['name'];

			if ( 5 === $box_data['box_type'] ) {
				if ( 13 === $binpacking_data[ $i ]['box_depth'] ) {
					$temp_parcel['default_size'] = 'LETTERA';
				} elseif ( 29 === $binpacking_data[ $i ]['box_depth'] ) {
					$temp_parcel['default_size'] = 'PICCOLA';
				} elseif ( 38 === $binpacking_data[ $i ]['box_depth'] ) {
					$temp_parcel['default_size'] = 'MEDIA';
				}
			} else {
				$temp_parcel['dim1']   = $binpacking_data[ $i ]['box_width'];
				$temp_parcel['dim2']   = $binpacking_data[ $i ]['box_depth'];
				$temp_parcel['dim3']   = $binpacking_data[ $i ]['box_height'];
				$temp_parcel['weight'] = $binpacking_data[ $i ]['total_box_weight'];
			}

			$temp_parcel_order['box_width']  = $binpacking_data[ $i ]['box_width'];
			$temp_parcel_order['box_depth']  = $binpacking_data[ $i ]['box_depth'];
			$temp_parcel_order['box_height'] = $binpacking_data[ $i ]['box_height'];
			$temp_parcel_order['box_weight'] = $binpacking_data[ $i ]['total_box_weight'];

			$temp_parcel_order['products'] = array();
			$count_binpacking_prodotti     = count( $binpacking_data[ $i ]['prodotti'] );
			for ( $cont = 0; $cont < $count_binpacking_prodotti; $cont++ ) {
				foreach ( $binpacking_data[ $i ]['prodotti'][ $cont ] as $livello => $prodotti ) {
					$count_prodotti = count( $prodotti );
					for ( $c = 0; $c < $count_prodotti; $c++ ) {
						$prod_data = json_decode( $prodotti[ $c ]['name'], true );

						$temp_prodotto['id']     = $prod_data['id'];
						$temp_prodotto['name']   = $prod_data['name'];
						$temp_prodotto['width']  = $prodotti[ $c ]['width'];
						$temp_prodotto['depth']  = $prodotti[ $c ]['depth'];
						$temp_prodotto['height'] = $prodotti[ $c ]['height'];

						$temp_parcel_order['products'][] = $temp_prodotto;
					}
				}
			}

			$parcels_products[] = $temp_parcel_order;
			$parcels[]          = $temp_parcel;
		}
	}

	$_SESSION['paccofacile_parcels_order'] = $parcels_products;
	$_SESSION['paccofacile_parcels']       = $parcels;

	return $parcels;
}

/**
 * Prepare boxes payload for bin packing call
 *
 * @return array
 */
function prepare_boxes_payload_bin_packing() {
	$plugin         = new Paccofacile();
	$shipping_boxes = $plugin->get_shipping_boxes();
	$imballi        = array();

	if ( ! empty( $shipping_boxes ) ) {
		foreach ( $shipping_boxes as $package ) {
			$item_box             = array();
			$item_box['box_type'] = $package['tipo'];

			$box_name = array(
				'box_id'   => $package['imballo_id'],
				'name'     => $package['nome'],
				'box_type' => $item_box['box_type'],
			);

			$item_box['name'] = wp_json_encode( $box_name );

			$item_box['width']  = $package['dim1'];
			$item_box['depth']  = $package['dim2'];
			$item_box['height'] = $package['dim3'];
			$item_box['weight'] = $package['peso_max'];

			if ( 1 === $item_box['box_type'] ) {
				// Pacco.
				$item_box['max_height'] = $package['dim3'];
				$item_box['box_height'] = 0;
				$item_box['tare']       = 0;

			} elseif ( 3 === $item_box['box_type'] ) {
				// Pallet.
				$pallet_type            = $package['tipo_variante'];
				$item_box['max_height'] = $package['altezza_max'];

				if ( 4 === $pallet_type ) {
					$item_box['box_height'] = 15;
					$item_box['tare']       = 14;
				} elseif ( 5 === $pallet_type ) {
					$item_box['box_height'] = 15;
					$item_box['tare']       = 6;
				} elseif ( 6 === $pallet_type ) {
					$item_box['box_height'] = 15;
					$item_box['tare']       = 10;
				}
			} elseif ( 2 === $item_box['box_type'] ) {
				// Busta.
				$item_box['max_height'] = $package['dim3'];
				$item_box['box_height'] = 0;
				$item_box['tare']       = 0;
			}

			$imballi[] = $item_box;
		}
		wp_reset_postdata();
	}

	return $imballi;
}

add_filter( 'woocommerce_package_rates', 'paccofacile_package_rates', 10, 2 );
/**
 * Paccofacile package rates
 *
 * @param array $rates WooCommerce rates.
 * @param array $package Shipping data.
 * @return array
 */
function paccofacile_package_rates( $rates, $package ) {

	$debug = false;

	$paccofacile_api = Paccofacile_Api::get_instance();

	// Initializing.
	$volume = 0;
	$length = 0;
	$width  = 0;
	$height = 0;
	$weight = 0;

	$store_address   = get_option( 'woocommerce_store_address' );
	$store_address_2 = get_option( 'woocommerce_store_address_2' );
	$store_city      = get_option( 'woocommerce_store_city' );
	$store_postcode  = get_option( 'woocommerce_store_postcode' );

	// The country/state.
	$store_raw_country = get_option( 'woocommerce_default_country' );

	// Split the country/state.
	$split_country = explode( ':', $store_raw_country );

	// Country and state separated.
	$store_country = $split_country[0];
	$store_state   = $split_country[1];

	$destination_country  = $package['destination']['country'];
	$destination_postcode = $package['destination']['postcode'];
	$destination_city     = $package['destination']['city'];
	$destination_state    = $package['destination']['state'];

	$packable_items   = array();
	$unpackable_items = array();

	$response = array();

	if ( is_checkout() || is_cart() ) {

		$parcels = create_parcels_object( $package['contents'] );

		$response = $paccofacile_api->calculate_quote( $store_country, $store_state, $store_postcode, $store_city, $destination_country, $destination_state, $destination_postcode, $destination_city, $parcels, false );

	}

	if ( array_key_exists( 'data', $response ) && ! empty( $response['data'] ) ) {
		$spedizioni = $response['data']['services_available'];

		$filtered_methods = paccofacile_validate_shipping_methods( $spedizioni );

		$registered_shipping_methods = WC()->shipping->get_shipping_methods();

		foreach ( $registered_shipping_methods as $method ) {
			$id          = $method->id;
			$instance_id = $method->instance_id;
			$settings    = $method->instance_settings;

			$method_item                = $settings;
			$method_item['id']          = $id;
			$method_item['instance_id'] = $instance_id;

			$shipping_methods_settings[] = $method_item;
		}

		$total_cart_price  = 0;
		$total_cart_weight = 0;

		foreach ( $package['contents'] as $item ) {
			$total_cart_weight += $item['data']->get_weight() * $item['quantity'];
			$total_cart_price  += $item['data']->get_price() * $item['quantity'];
		}

		foreach ( $rates as $key => $rate ) {

			if ( 'paccofacile_shipping_method' === $rates[ $key ]->method_id ) {

				$meta       = $rates[ $key ]->get_meta_data();
				$service_id = $meta['service_id'];

				$array_serviceids = array_column( $filtered_methods, 'service_id' );

				$method_k = array_search( $service_id, $array_serviceids );

				if ( false !== $method_k ) {
					$amount = $filtered_methods[ $method_k ]['price_total']['taxable_amount'];

					$rates[ $key ]->label = $filtered_methods[ $method_k ]['carrier'] . ' ' . $filtered_methods[ $method_k ]['name'];

					// FILTRO A SECONDA DELLE RESTRIZIONI DEL METODO DI SPEDIZIONE IMPOSTATE.
					$array_instanceids = array_column( $shipping_methods_settings, 'instance_id' );
					$rate_instance_id  = $rates[ $key ]->get_instance_id();
					$settings_key      = array_search( $rate_instance_id, $array_instanceids );

					if ( false !== $settings_key ) {
						// Settings trovate.

						// PRICE VARIATION.
						if ( 'increase' === $shipping_methods_settings[ $settings_key ]['price_variation'] ) {
							if ( 'fixed' === $shipping_methods_settings[ $settings_key ]['price_variation_type'] ) {
								if ( '' !== $shipping_methods_settings[ $settings_key ]['price_variation_amount'] && is_numeric( $shipping_methods_settings[ $settings_key ]['price_variation_amount'] ) ) {
									$amount += $shipping_methods_settings[ $settings_key ]['price_variation_amount'];
								}
							} elseif ( 'percentage' === $shipping_methods_settings[ $settings_key ]['price_variation_type'] ) {
								if ( '' !== $shipping_methods_settings[ $settings_key ]['price_variation_percentage'] && is_numeric( $shipping_methods_settings[ $settings_key ]['price_variation_percentage'] ) ) {
									$valore_percentuale = $amount * $shipping_methods_settings[ $settings_key ]['price_variation_percentage'] / 100;
									$amount            += $valore_percentuale;
								}
							}
						} elseif ( 'decrease' === $shipping_methods_settings[ $settings_key ]['price_variation'] ) {
							if ( 'fixed' === $shipping_methods_settings[ $settings_key ]['price_variation_type'] ) {
								if ( '' !== $shipping_methods_settings[ $settings_key ]['price_variation_amount'] && is_numeric( $shipping_methods_settings[ $settings_key ]['price_variation_amount'] ) ) {
									$amount -= $shipping_methods_settings[ $settings_key ]['price_variation_amount'];
								}
							} elseif ( 'percentage' === $shipping_methods_settings[ $settings_key ]['price_variation_type'] ) {
								if ( '' !== $shipping_methods_settings[ $settings_key ]['price_variation_percentage'] && is_numeric( $shipping_methods_settings[ $settings_key ]['price_variation_percentage'] ) ) {
									$valore_percentuale = $amount * $shipping_methods_settings[ $settings_key ]['price_variation_percentage'] / 100;
									$amount            -= $valore_percentuale;
								}
							}
						}

						$rates[ $key ]->cost = $amount;
						$taxes               = WC_Tax::calc_shipping_tax( $amount, WC_Tax::get_shipping_tax_rates() );
						$rates[ $key ]->set_taxes( $taxes );

						if ( 'by_price' === $shipping_methods_settings[ $settings_key ]['activation_condition'] ) {
							$min_price = $shipping_methods_settings[ $settings_key ]['min_price'];
							$max_price = $shipping_methods_settings[ $settings_key ]['max_price'];
							if ( '' === $shipping_methods_settings[ $settings_key ]['min_price'] ) {
								$min_price = 0;
							}

							if ( ( $total_cart_price < $min_price ) || ( '' !== $max_price && $total_cart_price > $max_price ) ) {
								unset( $rates[ $key ] );
							}
						} elseif ( 'by_weight' === $shipping_methods_settings[ $settings_key ]['activation_condition'] ) {
							$min_weight = $shipping_methods_settings[ $settings_key ]['min_weight'];
							$max_weight = $shipping_methods_settings[ $settings_key ]['max_weight'];
							if ( '' === $shipping_methods_settings[ $settings_key ]['min_weight'] ) {
								$min_weight = 0;
							}

							if ( ( $total_cart_weight < $min_weight ) || ( '' !== $max_weight && $total_cart_weight > $max_weight ) ) {
								unset( $rates[ $key ] );
							}
						}
					}
				} else {
					unset( $rates[ $key ] );
				}
			}
		}
	} else {
		foreach ( $rates as $key => $rate ) {
			if ( 'paccofacile_shipping_method' == $rates[ $key ]->method_id ) {
				unset( $rates[ $key ] );
			}
		}
	}

	return $rates;
}

/**
 * Paccofacile create order
 *
 * @param int    $order_id Paccofacile order id.
 * @param array  $posted_data Pasted data.
 * @param [type] $order Order object.
 * @return void
 */
function paccofacile_create_order( $order_id, $posted_data, $order ) {
	global $woocommerce;
	$cart = $woocommerce->cart->get_cart();

	$paccofacile_api = Paccofacile_Api::get_instance();

	$shipping_method       = $posted_data['shipping_method'][0];
	$array_shipping_method = explode( '_', $shipping_method );
	$service_id            = $array_shipping_method[ count( $array_shipping_method ) - 1 ];

	// VERIFICO SE IL CLIENTE HA SCELTO UN METODO SPEDIZIONE PACCOFACILE.
	$shipping_methods   = $order->get_items( 'shipping' );
	$shipping_method_id = false;
	foreach ( $shipping_methods as $shipping_method ) {
		$shipping_method_id = $shipping_method->get_method_id();
		break;
	}

	if ( $shipping_method_id && 'paccofacile_shipping_method' === $shipping_method_id ) {

		$carriers = get_available_shipping_methods( $service_id );
		if ( $carriers->have_posts() ) {
			while ( $carriers->have_posts() ) {
				$carriers->the_post();
				$carrier_id = get_post_meta( get_the_ID(), 'carrier_id', true );
			}
			$carriers->reset_postdata();
		}

		$volume = 0;
		$length = 0;
		$width  = 0;
		$height = 0;
		$weight = 0;

		$store_address   = get_option( 'woocommerce_store_address' );
		$store_address_2 = get_option( 'woocommerce_store_address_2' );
		$store_city      = get_option( 'woocommerce_store_city' );
		$store_postcode  = get_option( 'woocommerce_store_postcode' );
		$store_name      = get_option( 'woocommerce_store_name' );

		$store_raw_country = get_option( 'woocommerce_default_country' );
		$split_country     = explode( ':', $store_raw_country );
		$store_country     = $split_country[0];
		$store_state       = $split_country[1];
		$store_locker_id   = get_option( 'paccofacile_pickup_locker_' . $carrier_id );

		$store_building_number = '' . get_option( 'woocommerce_store_building_number' );
		$store_phone           = get_option( 'woocommerce_store_phone' );
		$store_email           = get_option( 'woocommerce_store_email' );

		$destination_country         = $posted_data['shipping_country'];
		$destination_postcode        = $posted_data['shipping_postcode'];
		$destination_city            = $posted_data['shipping_city'];
		$destination_address         = $posted_data['shipping_address_1'];
		$destination_building_number = $posted_data['shipping_building_number'];
		$destination_intercom_code   = $posted_data['shipping_intercom_code'];
		$destination_province        = $posted_data['shipping_state'];
		$destination_email           = ( $posted_data['shipping_email'] ) ? $posted_data['shipping_email'] : $posted_data['billing_email'];
		$destination_phone           = ( $posted_data['shipping_phone'] ) ? $posted_data['shipping_phone'] : $posted_data['billing_phone'];
		$destination_first_name      = $posted_data['shipping_first_name'];
		$destination_last_name       = $posted_data['shipping_last_name'];
		$destination_email           = ( $posted_data['shipping_email'] ) ? $posted_data['shipping_email'] : $posted_data['billing_email'];
		$destination_id_locker       = ( $posted_data['shipping_locker'] ) ? $posted_data['shipping_locker'] : '';
		$destination_note            = $posted_data['order_comments'];

		if ( isset( $_SESSION['paccofacile_parcels'] ) ) {
			$parcels = $_SESSION['paccofacile_parcels'];
		} else {
			$parcels = array();
		}
		$response_preventivo      = $paccofacile_api->calculate_quote( $store_country, $store_state, $store_postcode, $store_city, $destination_country, $destination_province, $destination_postcode, $destination_city, $parcels, $service_id );
		$response_preventivo_data = $response_preventivo['data'];

		$pickup_date  = $response_preventivo_data['services_available'][0]['pickup_date']['first_date'];
		$pickup_range = $response_preventivo_data['services_available'][0]['pickup_date']['first_date_range'];

		$customes_required = $response_preventivo_data['services_available'][0]['customes_required'];

		if ( 0 !== $customes_required ) {
			$amount  = number_format( (float) $order->get_total() - $order->get_total_tax() - $order->get_total_shipping() - $order->get_shipping_tax(), wc_get_price_decimals(), '.', '' );
			$customs = array(
				'amount'   => array(
					'value'    => $amount,
					'currency' => 'EUR',
				),
				'articles' => array(
					array(
						'amount'                        => array(
							'value'    => $amount,
							'currency' => 'EUR',
						),
						'quantity'                      => 1,
						'weight'                        => $weight,
						'description'                   => 'Merce',
						'iso_code_country_manufactured' => 'IT',
					),
				),
			);
			update_post_meta( $order->get_id(), 'customes_required', 1 );
		} else {
			$customs = array();
			update_post_meta( $order->get_id(), 'customes_required', 0 );
		}

		$payload_ordine = array(
			'external_order_id'     => $order->get_id(),
			'external_service_name' => 'woocommerce',
			'shipment_service'      => array(
				'shipment_type'        => 1,
				'pickup_date'          => $pickup_date,
				'pickup_range'         => $pickup_range,
				'service_id'           => $service_id,
				'parcels'              => $parcels,
				'package_content_type' => 'GOODS',
			),
			'pickup'                => array(
				'iso_code'            => $store_country,
				'postal_code'         => $store_postcode,
				'city'                => $store_city,
				'header_name'         => $store_name,
				'address'             => $store_address,
				'building_number'     => $store_building_number,
				'StateOrProvinceCode' => $store_state,
				'phone'               => $store_phone,
				'email'               => $store_email,
				'note'                => '',
				'locker_id'           => $store_locker_id,
			),
			'destination'           => array(
				'iso_code'            => $destination_country,
				'postal_code'         => $destination_postcode,
				'city'                => $destination_city,
				'header_name'         => $destination_first_name . ' ' . $destination_last_name,
				'address'             => $destination_address,
				'building_number'     => $destination_building_number,
				'StateOrProvinceCode' => $destination_province,
				'phone'               => $destination_phone,
				'email'               => $destination_email,
				'note'                => $destination_note,
				'locker_id'           => $destination_id_locker,
			),
			'customs'               => $customs,
		);

		$response_ordine      = $paccofacile_api->post( 'shipment/save', array(), $payload_ordine );
		$response_ordine_data = $response_ordine['data'];

		if ( isset( $_SESSION['paccofacile_parcels_order'] ) ) {
			update_post_meta( $order_id, 'order_parcels', filter_var( wp_json_encode( $_SESSION['paccofacile_parcels_order'] ), FILTER_SANITIZE_STRING ) );
		}
		update_post_meta( $order_id, 'paccofacile_order_payload', $payload_ordine );

		if ( 200 === $response_ordine['code'] ) {
			delete_post_meta( $order_id, 'shipment_draft_id' );
			update_post_meta( $order_id, 'shipment_id', $response_ordine_data['shipment']['shipment_id'] );

			if ( $destination_id_locker ) {
				update_post_meta( $order_id, 'destination_locker_id', $destination_id_locker );
			}

			if ( 1 === $response_ordine_data['shipment']['consolidation']['is_service_consolidation'] ) {
				// SERVIZIO CONSOLIDABILE.
				update_post_meta( $order_id, 'shipment_consolidabile', 1 );
			}
		} elseif ( 400 === $response_ordine['code'] && array_key_exists( 'destination', $response_ordine['header']['notification']['messages']['errors'] ) ) {
			$shipment_draft_id = $response_ordine['header']['notification']['messages']['shipment_draft_id'];

			delete_post_meta( $order_id, 'shipment_id' );
			update_post_meta( $order_id, 'shipment_draft_id', $shipment_draft_id );
		}
	}
}
add_action( 'woocommerce_checkout_order_processed', 'paccofacile_create_order', 10, 3 );


add_filter( 'woocommerce_checkout_fields', 'paccofacile_shipping_phone_checkout', 20 );
/**
 * Add shipping additional fields
 *
 * @param array $fields array of checkout fields.
 * @return array
 */
function paccofacile_shipping_phone_checkout( $fields ) {
	$fields['shipping']['shipping_phone'] = array(
		'label'    => __( 'Phone', 'paccofacile-for-woocommerce' ),
		'required' => false,
		'class'    => array( 'form-row-wide' ),
		'priority' => 25,
	);
	$fields['shipping']['shipping_email'] = array(
		'label'    => __( 'Email Address', 'paccofacile-for-woocommerce' ),
		'required' => false,
		'class'    => array( 'form-row-wide' ),
		'priority' => 25,
	);
	unset( $fields['shipping']['shipping_address_2'] );
	unset( $fields['billing']['billing_address_2'] );
	$fields['shipping']['shipping_building_number'] = array(
		'label'    => __( 'Building number', 'paccofacile-for-woocommerce' ),
		'required' => true,
		'class'    => array( 'form-row-wide' ),
		'priority' => 55,
	);
	$fields['billing']['billing_building_number']   = array(
		'label'    => __( 'Building number', 'paccofacile-for-woocommerce' ),
		'required' => true,
		'class'    => array( 'form-row-wide' ),
		'priority' => 55,
	);
	$fields['shipping']['shipping_intercom_code']   = array(
		'label'    => __( 'Intercom code', 'paccofacile-for-woocommerce' ),
		'required' => false,
		'class'    => array( 'form-row-wide' ),
		'priority' => 60,
	);
	return $fields;
}



add_action( 'woocommerce_admin_order_data_after_shipping_address', 'paccofacile_shipping_locker_info' );
/**
 * Paccofacile locker info
 *
 * @param [type] $order Order object.
 * @return void
 */
function paccofacile_shipping_locker_info( $order ) {
	$destination_locker_id = get_post_meta( $order->get_id(), 'destination_locker_id', true );

	$paccofacile_api = Paccofacile_Api::get_instance();

	if ( $destination_locker_id ) {
		$locker_details = $paccofacile_api->get( 'lockers/' . $destination_locker_id );
		$locker_details = $locker_details['data'];

		$opening_hours = '';
		if ( $locker_details['opening_hours'] ) {
			$opening_hours = '<br /><b>' . esc_html__( 'Opening hours', 'paccofacile-for-woocommerce' ) . '</b>: ' . $locker_details['opening_hours'];
		}
		echo '<p><b>' . esc_html__( 'Locker ID', 'paccofacile-for-woocommerce' ) . '</b>: ' . esc_html( $destination_locker_id ) . '<br />
		<b>' . esc_html__( 'Address', 'paccofacile-for-woocommerce' ) . '</b>: ' . esc_html( $locker_details['address'] ) . ' ' . esc_html( $locker_details['building_number'] ) . ' - ' . esc_html( $locker_details['city'] ) . ' (' . esc_html( $locker_details['province'] ) . ') ' . esc_html( $locker_details['postcode'] ) . esc_html( $opening_hours ) . '</p>';

	}
}

/**
 * Define the woocommerce_ship_to_different_address_checked callback
 *
 * @param [type] $value Value of woocommerce_ship_to_different_address_checked.
 * @return mixed Value woocommerce_ship_to_different_address_checked.
 */
function paccofacile_woocommerce_ship_to_different_address_checked( $value ) {
	$current_shipping_method = WC()->session->get( 'chosen_shipping_methods' );

	$current_method_strarray = explode( '_', $current_shipping_method[0] );
	if ( ! empty( $current_method_strarray ) && 'paccofacile' === $current_method_strarray[0] ) {
		$service_id = end( $current_method_strarray );

		$args_carriers = array(
			'post_type'  => 'carrier',
			'meta_key'   => 'service_id',
			'meta_value' => $service_id,
		);

		$carriers = new WP_Query( $args_carriers );

		$pickup_type = 1;
		$carrier_id  = '';
		if ( $carriers->have_posts() ) :
			while ( $carriers->have_posts() ) :
				$carriers->the_post();
				$pickup_type = get_post_meta( get_the_ID(), 'pickup_type', true );
				$carrier_id  = get_post_meta( get_the_ID(), 'carrier_id', true );
			endwhile;
			$carriers->reset_postdata();
		endif;

		/* @todo: controllare se il metodo di spedizione scelto è compatibile con locker (meta data?) */
		if ( 4 === (int) $pickup_type || 5 === (int) $pickup_type ) {
			return 1;
		}
	}

	return $value;
}
add_filter( 'woocommerce_ship_to_different_address_checked', 'paccofacile_woocommerce_ship_to_different_address_checked', 10, 1 );



add_filter( 'woocommerce_checkout_fields', 'paccofacile_locker_checkout_fields' );
/**
 * Add locker checkout fields
 *
 * @param array $fields Array of fields.
 * @return array
 */
function paccofacile_locker_checkout_fields( $fields ) {
	$fields['shipping']['shipping_locker'] = array(
		'label'    => __( 'Locker', 'paccofacile-for-woocommerce' ),
		'type'     => 'text',
		'required' => true,
		'class'    => array( 'form-row-wide' ),
		'priority' => 100,
	);

	return $fields;
}


add_filter( 'woocommerce_default_address_fields', 'paccofacile_checkout_fields_labels' );
/**
 * Change the checkout address field label
 *
 * @param array $fields Checkout fields.
 * @return array
 */
function paccofacile_checkout_fields_labels( $fields ) {
	$fields['address_1']['label'] = __( 'Address', 'paccofacile-for-woocommerce' );

	return $fields;
}


add_filter( 'woocommerce_checkout_get_value', 'paccofacile_shipping_locker_field_value', 5, 2 );
/**
 * Locker field
 *
 * @param [type] $value The input value.
 * @param [type] $input The input name.
 * @return [mixed]
 */
function paccofacile_shipping_locker_field_value( $value, $input ) {
	// $items = WC()->cart->get_cart();
	// $item  = reset( $items );
	$active_locker = WC()->session->get( 'locker_id' );

	if ( is_checkout() && $active_locker && in_array( $input, array( 'shipping_locker' ) ) ) {
		$value = $active_locker;
	}
	return $value;
}


add_action( 'woocommerce_after_checkout_shipping_form', 'paccofacile_locker_checkout_map' );
/**
 * Checkout locker map
 *
 * @param [type] $checkout Checkout.
 * @return void
 */
function paccofacile_locker_checkout_map( $checkout ) {

	global $woocommerce;
	$current_shipping_method = WC()->session->get( 'chosen_shipping_methods' );
	$postcode                = $woocommerce->customer->get_shipping_postcode();
	$city                    = $woocommerce->customer->get_shipping_city();

	$current_method_strarray = explode( '_', $current_shipping_method[0] );
	if ( ! empty( $current_method_strarray ) && 'paccofacile' === $current_method_strarray[0] ) {
		$service_id = end( $current_method_strarray );

		$args_carriers = array(
			'post_type'  => 'carrier',
			'meta_key'   => 'service_id',
			'meta_value' => $service_id,
		);

		$carriers = new WP_Query( $args_carriers );

		$pickup_type = 1;
		$carrier_id  = '';
		if ( $carriers->have_posts() ) :
			while ( $carriers->have_posts() ) :
				$carriers->the_post();
				$pickup_type = get_post_meta( get_the_ID(), 'pickup_type', true );
				$carrier_id  = get_post_meta( get_the_ID(), 'carrier_id', true );
			endwhile;
			$carriers->reset_postdata();
		endif;

		/* @todo: controllare se il metodo di spedizione scelto è compatibile con locker (meta data?) */
		if ( 4 === (int) $pickup_type || 5 === (int) $pickup_type ) {

			$active_locker_id = WC()->session->get( 'locker_id' );

			?>
			<div id="paccofacile-map" class="paccofacile-map" data-postcode="<?php echo esc_attr( $postcode ); ?>" data-city="<?php echo esc_attr( $city ); ?>" data-carrier-id="<?php echo esc_attr( $carrier_id ); ?>" data-store-nonce="<?php echo esc_attr( wp_create_nonce( 'get_store_locker_nonce' ) ); ?>">
				<div id="popup" class="ol-popup">
					<a href="#" id="popup-closer" class="ol-popup-closer"></a>
					<div id="popup-content"></div>
				</div>
			</div>
			<div id="paccofacile-lockers-list" 
				<?php
				if ( $active_locker_id ) {
					?>
					data-active="<?php echo esc_attr( $active_locker_id ); ?>"
				<?php } ?>>
			</div>
			<?php

		}
	}
}




add_filter( 'woocommerce_general_settings', 'paccofacile_woocommerce_general_settings' );
/**
 * WooCommerce general settings
 *
 * @param array $settings array of WooCommerce settings.
 * @return array
 */
function paccofacile_woocommerce_general_settings( $settings ) {
	$key = 1;

	foreach ( $settings as $values ) {
		$new_settings[ $key ] = $values;
		$key++;

		if ( 'store_address' === $values['id'] && 'sectionend' !== $values['type'] ) {
			$new_settings[ $key ] = array(
				'title'    => __( 'Shop name', 'paccofacile-for-woocommerce' ),
				'desc'     => __( 'Heading name of your business office', 'paccofacile-for-woocommerce' ),
				'id'       => 'woocommerce_store_name', // <= The field ID (important)
				'default'  => '',
				'type'     => 'text',
				'desc_tip' => true,
			);
			$key++;
		}

		// Inserting array just after the post code in "Store Address" section.
		if ( 'woocommerce_store_postcode' === $values['id'] ) {
			$new_settings[ $key ] = array(
				'title'    => __( 'Phone Number', 'paccofacile-for-woocommerce' ),
				'desc'     => __( 'Phone number of your business office', 'paccofacile-for-woocommerce' ),
				'id'       => 'woocommerce_store_phone', // <= The field ID (important)
				'default'  => '',
				'type'     => 'text',
				'desc_tip' => true,
			);
			$key++;
			$new_settings[ $key ] = array(
				'title'    => __( 'Email Address', 'paccofacile-for-woocommerce' ),
				'desc'     => __( 'Email Address of your business office', 'paccofacile-for-woocommerce' ),
				'id'       => 'woocommerce_store_email', // <= The field ID (important)
				'default'  => '',
				'type'     => 'text',
				'desc_tip' => true,
			);
			$key++;
		} elseif ( 'woocommerce_store_address_2' === $values['id'] ) {
			$new_settings[ $key ] = array(
				'title'    => __( 'Building number', 'paccofacile-for-woocommerce' ),
				'desc'     => __( 'Building number of your business office', 'paccofacile-for-woocommerce' ),
				'id'       => 'woocommerce_store_building_number', // <= The field ID (important)
				'default'  => '',
				'type'     => 'text',
				'desc_tip' => true,
			);
			$key++;
		}
	}

	return $new_settings;
}

/**
 * Paccofacile WooCommerce Order Meta Box
 *
 * @param [type] $post Order Post.
 * @return void
 */
function paccofacile_order_meta_box( $post ) {
	$order              = wc_get_order( $post->ID );
	$shipping_methods   = $order->get_items( 'shipping' );
	$shipping_method_id = false;

	foreach ( $shipping_methods as $shipping_method ) {
		$shipping_method_id = $shipping_method->get_method_id();
		break;
	}

	add_meta_box(
		'paccofacile',
		__( 'Paccofacile', 'paccofacile-for-woocommerce' ),
		'paccofacile_credit_meta_box',
		'shop_order',
		'side',
		'core',
	);

	$order_tracking = json_decode( get_post_meta( $post->ID, 'order_tracking', true ), true );
	if ( ! empty( $order_tracking ) ) {
		add_meta_box(
			'paccofacile_tracking',
			__( 'Order Tracking', 'paccofacile-for-woocommerce' ),
			'paccofacile_tracking_meta_box',
			'shop_order',
			'side',
			'core'
		);
	}

	$order_parcels = json_decode( get_post_meta( $post->ID, 'order_parcels', true ), true );
	if ( ! empty( $order_parcels ) ) {
		add_meta_box(
			'paccofacile_parcels',
			__( 'Order Parcels', 'paccofacile-for-woocommerce' ),
			'paccofacile_parcels_meta_box',
			'shop_order',
			'normal',
			'core'
		);
	}
}
add_action( 'add_meta_boxes_shop_order', 'paccofacile_order_meta_box' );

/**
 * Paccofacile Order credit metabox
 *
 * @return void
 */
function paccofacile_credit_meta_box() {
	global $post;

	$paccofacile_api = Paccofacile_Api::get_instance();

	$response_credito      = $paccofacile_api->get( 'customers/credit', array(), array() );
	$response_credito_data = $response_credito['data'];

	$credito = $response_credito_data['credit']['value'];

	$shipment_id              = get_post_meta( $post->ID, 'shipment_id', true ) ? get_post_meta( $post->ID, 'shipment_id', true ) : '';
	$shipment_draft_id        = get_post_meta( $post->ID, 'shipment_draft_id', true ) ? get_post_meta( $post->ID, 'shipment_draft_id', true ) : '';
	$paccofacile_order_id     = get_post_meta( $post->ID, 'paccofacile_order_id', true ) ? get_post_meta( $post->ID, 'paccofacile_order_id', true ) : '';
	$paccofacile_order_status = get_post_meta( $post->ID, 'paccofacile_order_status', true ) ? get_post_meta( $post->ID, 'paccofacile_order_status', true ) : '';
	$is_consolidabile         = get_post_meta( $post->ID, 'shipment_consolidabile', true ) ? get_post_meta( $post->ID, 'shipment_consolidabile', true ) : '';
	$is_consolidato           = get_post_meta( $post->ID, 'shipment_consolidato', true ) ? get_post_meta( $post->ID, 'shipment_consolidato', true ) : '';
	$order_weight             = 0;

	$order_parcels = get_post_meta( $post->ID, 'order_parcels', true ) ? json_decode( get_post_meta( $post->ID, 'order_parcels', true ), true ) : false;

	if ( $order_parcels ) {
		$count_parcels = count( $order_parcels );
		for ( $i = 0; $i < $count_parcels; $i++ ) {
			$order_weight += $order_parcels[ $i ]['box_weight'];
		}
	}

	$order = wc_get_order( $post->ID );

	$fav_address       = '';
	$addresses_options = array();

	$response_addresses      = $paccofacile_api->get( 'billing-address', array(), array() );
	$response_addresses_data = $response_addresses['data'];
	if ( $response_addresses_data ) {
		foreach ( $response_addresses_data as $key => $address ) {
			if ( 1 === $address['is_default'] ) {
				$fav_address = $response_addresses_data[ $key ];
			}
			$label  = ( $address['company'] ) ? $address['company'] : $address['firstname'] . ' ' . $address['lastname'];
			$label .= ' (' . $address['tax_id'] . ')';

			$addresses_options[ $address['address_id'] ] = $label;
		}
	}

	if ( '' === $fav_address ) {
		$fav_address = array( 'address_id' => '' );
	}

	$shipping_methods   = $order->get_items( 'shipping' );
	$shipping_method_id = false;

	foreach ( $shipping_methods as $shipping_method ) {
		$shipping_method_id = $shipping_method->get_method_id();
		break;
	}

	$notice_ship_with_paccofacile = false;
	if ( $shipping_method_id && 'paccofacile_shipping_method' !== $shipping_method_id ) {
		$notice_ship_with_paccofacile = true;
	}

	if ( true === $notice_ship_with_paccofacile ) {
		?>
		

		<div class="paccofacile_ship_with_form">
			<p><?php esc_html_e( "The customer didn't choose a Paccofacile.it shipment.", 'paccofacile-for-woocommerce' ); ?></p>
			
			<input type="hidden" name="paccofacile_meta_field_nonce" value="<?php echo esc_attr( wp_create_nonce() ); ?>">
			<input type="hidden" name="action" value="paccofacile_ship_with" />
			<input type="hidden" name="post_type" value="shop_order">
			<input type="hidden" name="order_id" value="<?php echo esc_attr( $order->get_id() ); ?>">

			<input type="submit" name="paccofacile_ship_with" class="button button-primary" value="<?php esc_attr_e( 'Ship with Paccofacile.it', 'paccofacile-for-woocommerce' ); ?>">
		</div>

	<?php } else { ?>
		<div class="paccofacile_pay_order_form">
			<p><?php esc_html_e( 'Credit left:', 'paccofacile-for-woocommerce' ); ?> <b><?php echo esc_html( $credito ); ?> €</b></p>
			<input type="hidden" name="paccofacile_meta_field_nonce" value="<?php echo esc_attr( wp_create_nonce() ); ?>">
			
			<?php /* <input type="hidden" name="action" value="paccofacile_pay_order" /> */ ?>

			<?php if ( $shipment_id ) { ?>
				<input type="hidden" name="shipment_id" value="<?php echo esc_attr( $shipment_id ); ?>">
			<?php } else { ?>
				<div class="notice notice-error">
					<p><?php esc_html_e( 'Your Paccofacile.it order could not be saved due to a data error. Please check the shipping destination data.', 'paccofacile-for-woocommerce' ); ?></p>
				</div>
			<?php } ?>
			<input type="hidden" name="shipping_amount" value="<?php echo esc_attr( $order->get_shipping_total() ); ?>">
			<?php /* <input type="hidden" name="post_type" value="shop_order"> */ ?>
			<?php /* <input type="hidden" name="order_id" value="<?php echo esc_attr( $order->get_id() ); ?>"> */ ?>
			<p><?php esc_html_e( 'Shipment ID:', 'paccofacile-for-woocommerce' ); ?> 
			<?php
			if ( $shipment_id ) {
				echo '<b>' . esc_html( $shipment_id ) . '</b>';
			} else {
				echo '<b style="color:#b32d2e;">' . esc_html__( 'None', 'paccofacile-for-woocommerce' ) . '</b>';
			}
			?>
			</p>

			<?php if ( $shipment_draft_id ) { ?>
				<p><?php esc_html_e( 'Shipment Draft ID:', 'paccofacile-for-woocommerce' ); ?> <b><?php echo esc_html( $shipment_draft_id ); ?></b></p>
			<?php } ?>

			<?php if ( $paccofacile_order_id ) : ?>
				<p><?php esc_html_e( 'Paccofacile.it Order ID:', 'paccofacile-for-woocommerce' ); ?> <b><?php echo esc_html( $paccofacile_order_id ); ?></b></p>
			<?php endif; ?>

			<hr>

			<?php if ( 'paid' !== $paccofacile_order_status ) : ?>
				<p><?php esc_html_e( 'Shipping costs:', 'paccofacile-for-woocommerce' ); ?> <b><?php echo ( get_post_meta( $order->get_id(), 'paccofacile_shipping_cost', 1 ) ) ? esc_html( get_post_meta( $order->get_id(), 'paccofacile_shipping_cost', 1 ) ) . ' € (' . esc_html( get_post_meta( $order->get_id(), 'paccofacile_shipping_cost_label_iva', 1 ) ) . ')' : esc_html( wc_format_decimal( $order->get_shipping_total(), 2 ) ) . '  €'; ?></b></p>

				<?php if ( 1 === $is_consolidato ) : ?>
					
					<p><?php esc_html_e( 'This shipment has been consolidated with others. To confirm your order you have to proceed to payment.', 'paccofacile-for-woocommerce' ); ?></p>

					<a class="button button-primary" href="<?php esc_attr_e( 'https://pro.paccofacile.it/redirect_to?route=shipment.consolidation_list', 'paccofacile-for-woocommerce' ); ?>" target="_blank"><?php esc_html_e( 'Continue on Paccofacile.it', 'paccofacile-for-woocommerce' ); ?></a>

				<?php elseif ( 1 === $is_consolidabile ) : ?>

					<p><?php esc_html_e( 'This is a consolidable shipment. You have to merge it with other shipments before you can proceed to the payment.', 'paccofacile-for-woocommerce' ); ?></p>

					<a class="button button-primary" href="<?php esc_attr_e( 'https://pro.paccofacile.it/redirect_to?route=shipment.consolidation_list', 'paccofacile-for-woocommerce' ); ?>" target="_blank"><?php esc_html_e( 'Continue on Paccofacile.it', 'paccofacile-for-woocommerce' ); ?></a>

				<?php else : ?>
				
					<fieldset class="form-field paccofacile_billing_detail_field form-field-wide">
						<legend><?php esc_html_e( 'Billing details', 'paccofacile-for-woocommerce' ); ?></legend>
						<ul class="wc-radios">
							<li>
								<label><input name="paccofacile_billing_detail" value="1" type="radio" class="select short" style="width:16px" required checked> <?php esc_html_e( 'Non-fiscal receipt with order summary', 'paccofacile-for-woocommerce' ); ?></label>
							</li>
							<li>
								<label><input name="paccofacile_billing_detail" value="2" type="radio" class="select short" style="width:16px" required> <?php esc_html_e( 'Invoice', 'paccofacile-for-woocommerce' ); ?></label>
							</li>
						</ul>
					</fieldset>

					<div class="paccofacile_billing_bill">
						<?php
						woocommerce_wp_radio(
							array(
								'id'            => 'paccofacile_billing_date',
								'label'         => __( 'Invoice type', 'paccofacile-for-woocommerce' ),
								'value'         => '1',
								'options'       => array(
									'1' => __( 'Monthly (unique invoice recap of all orders of the current month)', 'paccofacile-for-woocommerce' ),
									'2' => __( 'Singular (invoice of this order only)', 'paccofacile-for-woocommerce' ),
								),
								'style'         => 'width:16px', // required for checkboxes and radio buttons.
								'wrapper_class' => 'paccofacile_billing_date', // always add this class.
							)
						);

						woocommerce_wp_select(
							array(
								'id'            => 'paccofacile_billing_address',
								'label'         => __( 'Billing address', 'paccofacile-for-woocommerce' ),
								'value'         => $fav_address['address_id'],
								'options'       => $addresses_options,
								'wrapper_class' => 'paccofacile_billing_address',
							)
						);
						?>
					</div>
					
					<?php

					$customes_required = get_post_meta( $order->get_id(), 'customes_required', 1 );
					$customes          = get_post_meta( $order->get_id(), 'customes', 1 );

					if (
						$credito < $order->get_shipping_total() ||
						( 1 == $customes_required && ! $customes ) ||
						! $shipment_id
					) {
						$disabled = 'disabled';
					} else {
						$disabled = '';
					}

					if ( 1 == $customes_required && ! $customes ) :
						add_thickbox();
						?>

						<a name="<?php esc_attr_e( 'Enter customs information', 'paccofacile-for-woocommerce' ); ?>" href="#TB_inline?width=600&height=550&inlineId=modal_customes" class="button button-primary thickbox add_customs_modal_open"><?php esc_html_e( 'Enter customs information', 'paccofacile-for-woocommerce' ); ?></a>
						
					<?php endif; ?>
				
					<button type="button" name="paccofacile_pay_order" class="button button-primary" <?php echo esc_attr( $disabled ); ?>><?php esc_html_e( 'Pay the order with the remaining credit', 'paccofacile-for-woocommerce' ); ?></button>
				
				<?php endif; ?>

			<?php else : ?>
				<p class="success"><?php esc_html_e( 'The order on Paccofacile.it is paid', 'paccofacile-for-woocommerce' ); ?></p>
			<?php endif; ?>
		
		</div>

		<div id="modal_customes" style="display:none;">
			<div class="customs_wrapper">
				<!-- <form action="post.php" class="modal_customes_form" method="post"> -->
				<div class="modal_customes_form">

					<p>Order weight: <?php echo esc_html( $order_weight ); ?></p>
					<input type="number" required name="total_goods_value" placeholder="<?php esc_attr_e( 'Shipping goods total amount (€)', 'paccofacile-for-woocommerce' ); ?>">
					<select name="goods_type" required>
						<option value="none" disabled><?php esc_html_e( 'Goods type', 'paccofacile-for-woocommerce' ); ?></option>
						<option value="on_sale_goods"><?php esc_html_e( 'On sale goods', 'paccofacile-for-woocommerce' ); ?></option>
						<option value="document"><?php esc_html_e( 'Document', 'paccofacile-for-woocommerce' ); ?></option>
						<option value="commercial_sample"><?php esc_html_e( 'Commercial sample', 'paccofacile-for-woocommerce' ); ?></option>
						<option value="no_sale_goods"><?php esc_html_e( 'Goods not for sale', 'paccofacile-for-woocommerce' ); ?></option>
						<option value="other"><?php esc_html_e( 'Other', 'paccofacile-for-woocommerce' ); ?></option>
					</select>

					<h4><?php esc_html_e( 'Shipping articles details', 'paccofacile-for-woocommerce' ); ?></h4>
					<table class="widefat fixed lista_customs">
						<thead>
							<tr>
								<th width="80px"><?php esc_html_e( 'Quantity', 'paccofacile-for-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'Weight (Kg)', 'paccofacile-for-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'Amount (€)', 'paccofacile-for-woocommerce' ); ?></th>
								<th width="200px"><?php esc_html_e( 'Description', 'paccofacile-for-woocommerce' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td><input required type="number" name="customs_1_quantity"></td>
								<td><input required type="number" name="customs_1_weight"></td>
								<td><input required type="number" name="customs_1_amount"></td>
								<td><input required type="text" name="customs_1_description"></td>
							</tr>
							<tr>
								<td><input type="number" name="customs_2_quantity"></td>
								<td><input type="number" name="customs_2_weight"></td>
								<td><input type="number" name="customs_2_amount"></td>
								<td><input type="text" name="customs_2_description"></td>
							</tr>
							<tr>
								<td><input type="number" name="customs_3_quantity"></td>
								<td><input type="number" name="customs_3_weight"></td>
								<td><input type="number" name="customs_3_amount"></td>
								<td><input type="text" name="customs_3_description"></td>
							</tr>
							<tr>
								<td><input type="number" name="customs_4_quantity"></td>
								<td><input type="number" name="customs_4_weight"></td>
								<td><input type="number" name="customs_4_amount"></td>
								<td><input type="text" name="customs_4_description"></td>
							</tr>
						</tbody>
					</table>

					<input type="hidden" name="shipment_id" value="<?php echo esc_attr( $shipment_id ); ?>">
					<input type="hidden" name="woo_order_id" value="<?php echo esc_attr( $order->get_id() ); ?>">
					<input type="hidden" name="order_weight" value="<?php echo esc_attr( $order_weight ); ?>">

					<button type="button" name="customes_submit" class="button button-primary customes_submit_button"><?php esc_html_e( 'Save', 'paccofacile-for-woocommerce' ); ?></button>
				</div>
				<!-- </form> -->
			</div>
		</div>

		<?php
	}
}

/**
 * Paccofacile tracking metabox
 *
 * @return void
 */
function paccofacile_tracking_meta_box() {
	global $post;

	$order_tracking = get_post_meta( $post->ID, 'order_tracking', true ) ? json_decode( get_post_meta( $post->ID, 'order_tracking', true ), true ) : '';
	$checkpoints    = $order_tracking['elenco']['checkpoints'];

	if ( ! empty( $checkpoints ) ) :
		?>
		<ul>
			<?php
			$count_checkpoints = count( $checkpoints );
			for ( $i = 0; $i < $count_checkpoints; $i++ ) :
				?>
				<li><?php echo '- <b>' . esc_html( $checkpoints[ $i ]['checkpoint_time'] ) . '</b><br />- ' . esc_html( $checkpoints[ $i ]['message'] ) . ' [' . esc_html( $checkpoints[ $i ]['city'] ) . ']'; ?></li>
			<?php endfor; ?>
		</ul>
		<?php
	endif;
}

/**
 * Paccofacile parcels metabox
 *
 * @param [type] $post Order object.
 * @return void
 */
function paccofacile_parcels_meta_box( $post ) {
	$order_parcels = get_post_meta( $post->ID, 'order_parcels', true ) ? json_decode( get_post_meta( $post->ID, 'order_parcels', true ), true ) : '';

	if ( ! empty( $order_parcels ) ) :
		?>
		<table class="widefat fixed">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Parcels', 'paccofacile-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Dimensions', 'paccofacile-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Weight', 'paccofacile-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Products', 'paccofacile-for-woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$count_order_parcels = count( $order_parcels );
				for ( $i = 0; $i < $count_order_parcels; $i++ ) :
					?>
					<tr>
						<td><?php echo ( '' !== $order_parcels[ $i ]['box_name'] ) ? esc_html( $order_parcels[ $i ]['box_name'] ) : esc_html( $order_parcels[ $i ]['products'][0]['name'] ); ?></td>
						<td><?php echo esc_html( $order_parcels[ $i ]['box_width'] ) . 'x' . esc_html( $order_parcels[ $i ]['box_depth'] ) . 'x' . esc_html( $order_parcels[ $i ]['box_height'] ); ?></td>
						<td><?php echo ( array_key_exists( 'box_weight', $order_parcels[ $i ] ) ) ? esc_html( $order_parcels[ $i ]['box_weight'] ) : ''; ?></td>
						<td>
							<?php
							$products = $order_parcels[ $i ]['products'];
							if ( ! empty( $products ) ) :
								$count_products = count( $products );
								?>
								<ul>
									<?php for ( $c = 0; $c < $count_products; $c++ ) { ?>
										<li><?php echo esc_html( $products[ $c ]['id'] ) . ' ' . esc_html( $products[ $c ]['name'] ); ?></li>
									<?php } ?>
								</ul>
							<?php endif; ?>
						</td>
					</li>
				<?php endfor; ?>
			</tbody>
			
		</table>
		<?php
	endif;
}

add_action( 'woocommerce_order_details_after_order_table', 'paccofacile_order_tracking_info' );
/**
 * Order tracking info
 *
 * @param [type] $order Order object.
 * @return void
 */
function paccofacile_order_tracking_info( $order ) {

	$order_tracking   = get_post_meta( $order->get_id(), 'order_tracking', true ) ? json_decode( get_post_meta( $order->get_id(), 'order_tracking', true ), true ) : '';
	$options_tracking = get_option( 'paccofacile_settings' )['tracking_to_show'];

	if ( ! empty( $order_tracking ) ) {
		$checkpoints = $order_tracking['elenco']['checkpoints'];

		if ( ! empty( $checkpoints ) ) :
			?>

			<h2><?php echo esc_html( apply_filters( 'paccofacile_order_tracking_title', __( 'Order tracking', 'paccofacile-for-woocommerce' ) ) ); ?></h2>

			<table class="woocommerce-table shop_table paccofacile_order_tracking">
				<?php $count_checkpoints = count( $checkpoints ); ?>
				<?php for ( $i = 0; $i < $count_checkpoints; $i++ ) : ?>
					<?php if ( array_key_exists( $checkpoints[ $i ]['tag'], $options_tracking ) && 1 === $options_tracking[ $checkpoints[ $i ]['tag'] ] ) : ?>
						<tr><td><?php echo '<b>' . esc_html( $checkpoints[ $i ]['checkpoint_time'] ) . '</b><br />- ' . esc_html( $checkpoints[ $i ]['message'] ) . ' [' . esc_html( $checkpoints[ $i ]['city'] ) . ']'; ?></td></tr>
					<?php endif; ?>
				<?php endfor; ?>
			
			</table>

			<?php
		endif;
	}
}

/**
 * Validate shipping methods
 *
 * @param [type] $services_list Services list.
 * @return array
 */
function paccofacile_validate_shipping_methods( $services_list ) {

	// PRENDO SOLO I SERVIZI ABILITATI DALL'UTENTE.
	$args             = array( 'post_type' => 'carrier' );
	$enabled_services = new WP_Query( $args );
	$filtered_list    = array();

	if ( $enabled_services->have_posts() ) {
		while ( $enabled_services->have_posts() ) {
			$enabled_services->the_post();
			$service_id = get_post_meta( get_the_ID(), 'service_id', true );

			$array_serviceids = array_column( $services_list, 'service_id' );
			$method_k         = array_search( (int) $service_id, $array_serviceids, true );

			if ( false !== $method_k ) {
				$filtered_list[] = $services_list[ $method_k ];
			}
		}
		$enabled_services->reset_postdata();
	}

	return $filtered_list;
}

/**
 * Quote and Save
 *
 * @param [type] $order Order object.
 * @param [type] $action Action.
 * @return void
 */
function paccofacile_quote_and_save_by_woo_order( $order, $action = null ) {

	$paccofacile_api = Paccofacile_Api::get_instance();

	$items  = $order->get_items();
	$volume = 0;
	$length = 0;
	$width  = 0;
	$height = 0;
	$weight = 0;

	$store_address         = get_option( 'woocommerce_store_address' );
	$store_address_2       = get_option( 'woocommerce_store_address_2' );
	$store_city            = get_option( 'woocommerce_store_city' );
	$store_postcode        = get_option( 'woocommerce_store_postcode' );
	$store_name            = get_option( 'woocommerce_store_name' );
	$store_phone           = get_option( 'woocommerce_store_phone' );
	$store_email           = get_option( 'woocommerce_store_email' );
	$store_building_number = '' . get_option( 'woocommerce_store_building_number' );

	// The country/state.
	$store_raw_country = get_option( 'woocommerce_default_country' );

	// Split the country/state.
	$split_country = explode( ':', $store_raw_country );

	// Country and state separated.
	$store_country = $split_country[0];
	$store_state   = $split_country[1];

	$destination_country    = $order->get_shipping_country();
	$destination_postcode   = $order->get_shipping_postcode();
	$destination_city       = $order->get_shipping_city();
	$destination_first_name = $order->get_shipping_first_name();
	$destination_last_name  = $order->get_shipping_last_name();
	$destination_address    = $order->get_shipping_address_1();
	$destination_province   = $order->get_shipping_state();

	$destination_phone           = get_post_meta( $order->get_id(), '_shipping_phone', true ) ? get_post_meta( $order->get_id(), '_shipping_phone', true ) : $order->get_billing_phone();
	$destination_email           = get_post_meta( $order->get_id(), '_shipping_email', true ) ? get_post_meta( $order->get_id(), '_shipping_email', true ) : $order->get_billing_email();
	$destination_building_number = $order->get_meta( '_shipping_building_number' );
	$destination_intercom_code   = $order->get_meta( '_shipping_intercom_code' );

	$parcels = create_parcels_object( $order->get_items() );

	$calculate_tax_for = array(
		'country'  => $destination_country,
		'state'    => $destination_province, // Can be set (optional).
		'postcode' => $destination_postcode, // Can be set (optional).
		'city'     => $destination_city, // Can be set (optional).
	);

	$response_quote = $paccofacile_api->calculate_quote( $store_country, $store_state, $store_postcode, $store_city, $destination_country, $destination_province, $destination_postcode, $destination_city, $parcels, false );

	if ( array_key_exists( 'data', $response_quote ) && ! empty( $response_quote['data'] ) ) {
		$response_quote_data = $response_quote['data'];

		$filtered_response = paccofacile_validate_shipping_methods( $response_quote_data['services_available'] );

		$shipping_items = (array) $order->get_items( 'shipping' );
		if ( 0 === count( $shipping_items ) ) {
			$shipping_item  = new WC_Order_Item_Shipping();
			$shipping_items = array( $shipping_item );
			$new_item       = true;
		}

		if ( ! empty( $filtered_response ) ) {

			foreach ( $shipping_items as $shipping_item ) {

				$shipping_item->set_method_title( $filtered_response[0]['carrier'] . ' ' . $filtered_response[0]['name'] );
				$shipping_item->set_method_id( 'paccofacile_shipping_method' ); // set an existing Shipping method rate ID.
				$shipping_item->set_total( $filtered_response[0]['price_total']['amount'] );
				$shipping_item->calculate_taxes( $calculate_tax_for );

				$shipping_item_id = $shipping_item->get_id();
				$shipping_meta    = $shipping_item->get_meta_data();

				if ( ! empty( $shipping_meta ) ) {
					$shipping_item->delete_meta_data( 'service_id' );
				}

				$shipping_item->add_meta_data( 'service_id', $filtered_response[0]['service_id'] );

				if ( isset( $new_item ) && $new_item ) {
					$order->add_item( $shipping_item );
				} else {
					$shipping_item->save();
				}
			}
		}

		if ( array_key_exists( 'paccofacile_parcels_order', $_SESSION ) ) {
			update_post_meta( $order->get_id(), 'order_parcels', filter_var( wp_unslash( wp_json_encode( $_SESSION['paccofacile_parcels_order'] ) ), FILTER_SANITIZE_STRING ) );
		}

		$shipment_id = get_post_meta( $order->get_id(), 'shipment_id', true ) ? get_post_meta( $order->get_id(), 'shipment_id', true ) : '';

		// AGGIORNO LA SPEDIZIONE PACCOFACILE.

		$pickup_date  = $filtered_response[0]['pickup_date']['first_date'];
		$pickup_range = $filtered_response[0]['pickup_date']['first_date_range'];

		$customes_required = $filtered_response[0]['customes_required'];
		$weight            = 0;

		if ( 0 !== $customes_required ) {
			$amount  = number_format( (float) $order->get_total() - $order->get_total_tax() - $order->get_total_shipping() - $order->get_shipping_tax(), wc_get_price_decimals(), '.', '' );
			$customs = array(
				'amount'   => array(
					'value'    => $amount,
					'currency' => 'EUR',
				),
				'articles' => array(
					array(
						'amount'                        => array(
							'value'    => $amount,
							'currency' => 'EUR',
						),
						'quantity'                      => 1,
						'weight'                        => $weight,
						'description'                   => 'Merce',
						'iso_code_country_manufactured' => 'IT',
					),
				),
			);
			update_post_meta( $order->get_id(), 'customes_required', 1 );
		} else {
			$customs = array();
			update_post_meta( $order->get_id(), 'customes_required', 0 );
		}

		$payload_ordine = array(
			'external_order_id'     => $order->get_id(),
			'external_service_name' => 'woocommerce',
			'shipment_service'      => array(
				'shipment_type'        => 1,
				'pickup_date'          => $pickup_date,
				'pickup_range'         => $pickup_range,
				'parcels'              => $parcels,
				'package_content_type' => 'GOODS',
			),
			'pickup'                => array(
				'iso_code'            => $store_country,
				'postal_code'         => $store_postcode,
				'city'                => $store_city,
				'header_name'         => $store_name,
				'address'             => $store_address,
				'building_number'     => $store_building_number,
				'StateOrProvinceCode' => $store_state,
				'phone'               => $store_phone,
				'email'               => $store_email,
				'note'                => '',
			),
			'destination'           => array(
				'iso_code'            => $destination_country,
				'postal_code'         => $destination_postcode,
				'city'                => $destination_city,
				'header_name'         => $destination_first_name . ' ' . $destination_last_name,
				'address'             => $destination_address,
				'building_number'     => $destination_building_number,
				'StateOrProvinceCode' => $destination_province,
				'phone'               => $destination_phone,
				'email'               => $destination_email,
			),
			'customs'               => $customs,
		);

		if ( $shipment_id ) {
			$payload_ordine['shipment_id'] = $shipment_id;
		} else {
			$payload_ordine['shipment_draft_id'] = get_post_meta( $order->get_id(), 'shipment_draft_id', true );
		}

		$saved_service_id = '';

		if ( $shipment_id || array_key_exists( 'shipment_draft_id', $payload_ordine ) ) { // siamo nella modifica ordine in wp-admin.
			$paccofacile_order_payload = get_post_meta( $order->get_id(), 'paccofacile_order_payload', true );

			if ( $paccofacile_order_payload && is_array( $paccofacile_order_payload ) && array_key_exists( 'shipment_service', $paccofacile_order_payload ) && array_key_exists( 'service_id', $paccofacile_order_payload['shipment_service'] ) ) {
				$saved_service_id = $paccofacile_order_payload['shipment_service']['service_id'];
			}
		}

		if ( null !== $saved_service_id && $saved_service_id && '' !== $saved_service_id ) {
			$payload_ordine['shipment_service']['service_id'] = $saved_service_id;
		} elseif ( ! empty( $filtered_response ) ) {
			$payload_ordine['shipment_service']['service_id'] = $filtered_response[0]['service_id'];
		}

		$response_ordine      = $paccofacile_api->post( 'shipment/save', array(), $payload_ordine );
		$response_ordine_data = $response_ordine['data'];

		update_post_meta( $order->get_id(), 'paccofacile_order_payload', $payload_ordine );

		if ( 200 === $response_ordine['code'] ) {
			delete_post_meta( $order->get_id(), 'shipment_draft_id' );
			if ( ! $shipment_id ) {
				update_post_meta( $order->get_id(), 'shipment_id', $response_ordine_data['shipment']['shipment_id'] );
				if ( 1 === $response_ordine_data['shipment']['consolidation']['is_service_consolidation'] ) { // SERVIZIO CONSOLIDABILE.
					update_post_meta( $order_id, 'shipment_consolidabile', 1 );
				}
			}
		} elseif ( 400 === $response_ordine['code'] && array_key_exists( 'destination', $response_ordine['header']['notification']['messages']['errors'] ) ) {
			$shipment_draft_id = $response_ordine['header']['notification']['messages']['shipment_draft_id'];

			delete_post_meta( $order->get_id(), 'shipment_id' );
			update_post_meta( $order->get_id(), 'shipment_draft_id', $shipment_draft_id );
		}
	}
}


add_action( 'woocommerce_order_before_calculate_totals', 'paccofacile_calculate_shipping_costs', 10, 2 );
/**
 * Calculate shipping costs
 *
 * @param [type] $and_taxes And Taxes option.
 * @param [type] $order Order object.
 * @return void
 */
function paccofacile_calculate_shipping_costs( $and_taxes, $order ) {

	if ( did_action( 'woocommerce_order_before_calculate_totals' ) > 0 ) {

		$shipping_methods   = $order->get_items( 'shipping' );
		$shipping_method_id = false;

		foreach ( $shipping_methods as $shipping_method ) {
			$shipping_method_id = $shipping_method->get_method_id();
			break;
		}

		if ( $shipping_method_id && 'paccofacile_shipping_method' === $shipping_method_id ) {
			paccofacile_quote_and_save_by_woo_order( $order );
		}
	}
}


// Add custom fields to product shipping tab.
add_action( 'woocommerce_product_options_shipping', 'paccofacile_shipping_option_to_products' );
/**
 * No need pack option on WooCommerce products
 *
 * @return void
 */
function paccofacile_shipping_option_to_products() {
	global $post, $product;

	echo '</div><div class="options_group">'; // New option group.

	$no_pack_needed = get_post_meta( $post->ID, 'no_pack_needed', true );
	if ( ! $no_pack_needed ) {
		$no_pack_needed = 0;
	}

	woocommerce_wp_checkbox(
		array(
			'id'      => 'no_pack_needed',
			'value'   => $no_pack_needed,
			'label'   => __( 'No pack needed', 'paccofacile-for-woocommerce' ),
			'cbvalue' => 1,
		)
	);
}

// Save the custom fields values as meta data.
add_action( 'woocommerce_process_product_meta', 'paccofacile_save_shipping_option_to_products' );
/**
 * Save "need package option" setting
 *
 * @param int $post_id Product post id.
 * @return void
 */
function paccofacile_save_shipping_option_to_products( $post_id ) {
	// Check & Validate the woocommerce meta nonce.
	if ( ! ( isset( $_POST['woocommerce_meta_nonce'] ) || wp_verify_nonce( sanitize_key( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) ) {
		return;
	}

	if ( isset( $_POST['no_pack_needed'] ) ) {
		update_post_meta( $post_id, 'no_pack_needed', filter_var( wp_unslash( $_POST['no_pack_needed'] ) ) );
	} else {
		delete_post_meta( $post_id, 'no_pack_needed' );
	}
}



/*
Save the data of the Meta field!
add_action( 'save_post_shop_order', 'paccofacile_pay_order', 10, 1 );
if ( ! function_exists( 'paccofacile_pay_order' ) ) {
	function paccofacile_pay_order( $post_id ) {

		if ( is_admin() ) {
			$paccofacile_api = Paccofacile_Api::get_instance();

			// Only for shop order
			if ( array_key_exists( 'post_type', $_POST ) && $_POST[ 'post_type' ] != 'shop_order' )
				return $post_id;

			// Check if our nonce is set (and our cutom field)
			if ( ! isset( $_POST[ 'paccofacile_meta_field_nonce' ] ) && isset( $_POST['paccofacile_pay_order'] ) )
				return $post_id;

			$nonce = $_POST[ 'paccofacile_meta_field_nonce' ];

			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $nonce ) )
				return $post_id;

			// Checking that is not an autosave
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
				return $post_id;

			// Check the user’s permissions (for 'shop_manager' and 'administrator' user roles)
			if ( ! current_user_can( 'edit_shop_order', $post_id ) && ! current_user_can( 'edit_shop_orders', $post_id ) )
				return $post_id;

			// Action to make or (saving data)
			if ( isset( $_POST['paccofacile_pay_order'] ) ) {
				$data_fattura = ( $_POST['paccofacile_billing_detail'] == '1' ) ? "" : $_POST['paccofacile_billing_date'];
				$address_id_select = ( $_POST['paccofacile_billing_detail'] == '1' ) ? "" : $_POST['paccofacile_billing_address'];

				$payload = array(
					'shipments' => array(
						$_POST['shipment_id']
					),
					'fattura' => $_POST['paccofacile_billing_detail'],
					"data_fattura" => $data_fattura,
					"address_id_select" => $address_id_select,
					'payment_method' => 'CREDIT'
				);

				$response = $paccofacile_api->post('shipment/buy', array(), $payload);
				$response = $response['data'];
				//error_log(print_r($response, true));

				if (array_key_exists( 'order', $response ) && $response['order']['order_id']) {
					update_post_meta($post_id, 'paccofacile_order_id', $response['order']['order_id'] );
					update_post_meta($post_id, 'paccofacile_order_status', 'paid' );
				}

			}
		}
	}

}
*/

/**
 * Send documents to WooCommerce order
 *
 * @param [type] $data Data to sent.
 * @return array
 */
function paccofacile_send_documents_to_orders( $data ) {

	global $wp_filesystem;
	if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base' ) ) {
		include_once ABSPATH . 'wp-admin/includes/file.php';
		$creds = request_filesystem_credentials( site_url() );
		wp_filesystem( $creds );
	}

	$paccofacile_order_id = $data['order_id'];

	$orders = wc_get_orders( array( 'paccofacile_order_id' => $paccofacile_order_id ) );
	if ( ! empty( $orders ) ) {
		$order = $orders[0];
	} else {
		return null;
	}

	$order_data = $order->get_data(); // The Order data.
	$order_id   = $order_data['id'];

	$response['id_waybill'] = $data['id_waybill'];
	$response['waybill']    = $data['waybill'];

	if ( empty( $order ) ) {
		return null;
	}

	// SALVO IL FILE.
	$upload     = wp_upload_dir();
	$upload_dir = $upload['basedir'];

	if ( ! is_dir( $upload_dir ) ) {
		$response['status']  = 'FAILURE';
		$response['message'] = 'Cartella upload non trovata';

		$res = new WP_REST_Response( $response );
		$res->set_status( 200 );
	} else {

		$upload_dir = $upload_dir . '/paccofacile';
		if ( ! is_dir( $upload_dir ) ) {
			$folder_created = mkdir( $upload_dir, 0777 );
		}

		// Open the output file for writing.
		if ( $response['waybill'] ) {
			if ( $response['waybill'] ) {

				$bytes     = random_bytes( 10 );
				$hex_bytes = bin2hex( $bytes );

				$filename = $hex_bytes . '_' . $response['id_waybill'] . '.pdf';
				$ifp      = $wp_filesystem->get_contents( $upload_dir . '/' . $filename );
				$file     = explode( ',', $response['waybill'] );

				// We could add validation here with ensuring count( $data ) > 1.
				$fwrite_response = $wp_filesystem->put_contents( $ifp, base64_decode( $file[1] ) );

				if ( $fwrite_response ) {
					$response['message'] = 'file scritto';
				} else {
					$response['message'] = 'non è possibile scrivere il file ---> ' . $upload_dir . '/' . $filename;
				}

				// Clean up the file resource.
				update_post_meta( $order_id, 'waybill', $upload['baseurl'] . '/paccofacile/' . $filename );

				if ( $response['id_waybill'] ) {
					update_post_meta( $order_id, 'id_waybill', $response['id_waybill'] );
				}
			}
		}

		$response['status'] = 'SUCCESS';
		unset( $response['waybill'] );
		$res = new WP_REST_Response( $response );
		$res->set_status( 200 );
	}

	return array( 'req' => $res );
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'paccofacile/v1',
			'/order_documents/(?P<order_id>\d+)',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => 'paccofacile_send_documents_to_orders',
				'permission_callback' => '__return_true',
			)
		);
	}
);

/**
 * Paccofacile update WooCommerce order
 *
 * @param array $data Data to update.
 * @return array
 */
function paccofacile_update_order( $data ) {

	$paccofacile_order_id = $data['shipment']['order_id'];

	$servizio         = $data['shipment']['servizio'];
	$nome_corriere    = $data['shipment']['carrier_code'] . ' ' . $servizio['codice_servizio_corriere'];
	$service_id       = $servizio['servizio_id'];
	$consolidamento   = $servizio['consolidamento'];
	$is_consolidabile = $consolidamento['is_service_consolidation'];
	$is_consolidato   = $consolidamento['is_consolidato'];

	$destinatario             = $data['shipment']['recipient'];
	$destinatario_name        = $destinatario['contact']['person_name'];
	$destinatario_phone       = $destinatario['contact']['telephone'];
	$destinatario_email       = $destinatario['contact']['email'];
	$destinatario_address     = $destinatario['address']['address'];
	$destinatario_state       = $destinatario['address']['province_code'];
	$destinatario_city        = $destinatario['address']['city'];
	$destinatario_postal_code = $destinatario['address']['postal_code'];
	$destinatario_country     = $destinatario['address']['country_code'];

	$order_status = $data['shipment']['order']['order_status_id'];

	$price_detail   = $data['shipment']['price_detail'];
	$amount_total   = $price_detail['amount_total'];
	$label_iva      = $price_detail['label_iva'];
	$percentage_tax = $price_detail['percentage_tax'];

	$tracking = $data['shipment']['tracking'];

	$orders = wc_get_orders( array( 'paccofacile_order_id' => $paccofacile_order_id ) );
	if ( ! empty( $orders ) ) {
		$order = $orders[0];
	} else {
		return null;
	}

	/*
	AGGIORNO IL DESTINATARIO ORDINE (va fatto?).
	$shipping_address = array(
		'first_name' => $destinatario_name,
		'last_name' => '',
		'company' => '',
		'address_1' => $destinatario_address,
		'address_2' => '',
		'city' => $destinatario_city,
		'state' => $destinatario_state,
		'postcode' => $destinatario_postal_code,
		'country' => $destinatario_country
	);
	$order->set_address($shipping_address, 'shipping');
	*/

	// AGGIORNO LO STATO DELL'ORDINE.
	if ( 5 === $order_status ) { // PAGATO.
		update_post_meta( $order->get_id(), 'paccofacile_order_status', 'paid' );
	} else {
		delete_post_meta( $order->get_id(), 'paccofacile_order_status' );
	}

	// AGGIORNO PREZZO SPEDIZIONE.
	update_post_meta( $order->get_id(), 'paccofacile_shipping_cost', $amount_total );
	update_post_meta( $order->get_id(), 'paccofacile_shipping_cost_label_iva', $label_iva );

	// AGGIORNO IL TRACKING.
	$tracking_update = wp_json_encode( $tracking );
	update_post_meta( $order->get_id(), 'order_tracking', $tracking_update );

	// AGGIORNO IL CORRIERE SCELTO.
	$corriere = wp_json_encode(
		array(
			'service_id'   => $service_id,
			'carrier_name' => $nome_corriere,
		)
	);
	update_post_meta( $order->get_id(), 'paccofacile_shipping_service', $corriere );

	// AGGIORNO CONSOLIDAMENTO.
	if ( 1 === $is_consolidabile ) {
		update_post_meta( $order->get_id(), 'shipment_consolidabile', 1 );
	} else {
		update_post_meta( $order->get_id(), 'shipment_consolidabile', 0 );
	}

	if ( 1 === $is_consolidato ) {
		update_post_meta( $order->get_id(), 'shipment_consolidato', 1 );
	} else {
		update_post_meta( $order->get_id(), 'shipment_consolidato', 0 );
	}

	$response['data']   = $data;
	$response['status'] = 'SUCCESS';

	$res = new WP_REST_Response( $response );
	$res->set_status( 200 );

	do_action( 'paccofacile_order_tracking_info_sent', $order->get_id(), $tracking );

	return array( 'req' => $res );
}

add_action(
	'rest_api_init',
	function () {
		register_rest_route(
			'paccofacile/v1',
			'/order_update',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => 'paccofacile_update_order',
				'permission_callback' => '__return_true',
			)
		);
	}
);


add_filter( 'manage_edit-shop_order_columns', 'paccofacile_shop_order_column', 20 );
/**
 * Shop order column
 *
 * @param [type] $columns Columns.
 * @return array
 */
function paccofacile_shop_order_column( $columns ) {
	$reordered_columns = array();

	// Inserting columns to a specific location.
	foreach ( $columns as $key => $column ) {
		$reordered_columns[ $key ] = $column;
		if ( 'order_total' === $key ) {
			// Inserting after "Status" column.
			$reordered_columns['paccofacile_status'] = __( 'Shipping status', 'paccofacile-for-woocommerce' );
			$reordered_columns['paccofacile_label']  = __( 'Paccofacile.it documents', 'paccofacile-for-woocommerce' );
		}
	}
	return $reordered_columns;
}


add_action( 'manage_shop_order_posts_custom_column', 'paccofacile_orders_list_column_content', 20, 2 );
/**
 * Order listg content column
 *
 * @param [type] $column Column of Order Lists Admin.
 * @param [type] $post_id Post id.
 * @return void
 */
function paccofacile_orders_list_column_content( $column, $post_id ) {
	switch ( $column ) {
		case 'paccofacile_status':
			// Get custom post meta data.
			$order_tracking = get_post_meta( $post_id, 'order_tracking', true ) ? json_decode( get_post_meta( $post_id, 'order_tracking', true ), true ) : '';

			if ( $order_tracking ) {
				$checkpoints = $order_tracking['elenco']['checkpoints'];
				if ( ! empty( $checkpoints ) ) {
					$last_key        = count( $checkpoints ) - 1;
					$last_checkpoint = $checkpoints[ $last_key ];

					echo esc_html( $last_checkpoint['message'] ) . ' - ' . esc_html( $last_checkpoint['city'] );
				}
			} else {
				echo '<small>(<em>' . esc_html__( 'no value', 'paccofacile-for-woocommerce' ) . '</em>)</small>';
			}

			break;

		case 'paccofacile_label':
			// Get custom post meta data.
			$waybill = get_post_meta( $post_id, 'waybill', true ) ? get_post_meta( $post_id, 'waybill', true ) : '';
			if ( $waybill ) {
				echo '<a href="' . esc_attr( $waybill ) . '" target="_blank"><span class="dashicons dashicons-pdf"></span> Lettera di vettura</a>';
			} else {
				echo '<small>(<em>' . esc_html__( 'no value', 'paccofacile-for-woocommerce' ) . '</em>)</small>';
			}

			break;
	}
}


/**
 *  Add a custom email to the list of emails WooCommerce should load
 *
 * @since 1.0
 * @param array $email_classes available Email classes.
 * @return array filtered available email classes
 */
function paccofacile_add_tracking_info_woocommerce_email( $email_classes ) {

	// include our custom email class.
	require 'class-wc-tracking-info-order-email.php';

	// add the email class to the list of email classes that WooCommerce loads.
	$email_classes['PFWC_Tracking_Info_Order_Email'] = new PFWC_Tracking_Info_Order_Email();

	return $email_classes;
}
add_filter( 'woocommerce_email_classes', 'paccofacile_add_tracking_info_woocommerce_email' );


/*
COMMENTED.
add_action( 'woocommerce_proceed_to_checkout', 'print_shipping_info' );
function print_shipping_info() {
	$shipping_tax_rates = WC_Tax::get_shipping_tax_rates();

	echo '<pre>';
	print_r($shipping_tax_rates);
	echo '</pre>';
}
*/

/**
 * Search locality
 *
 * @param [type] $iso_code Iso code.
 * @param [type] $city City.
 * @return array
 */
function paccofacile_search_locality( $iso_code, $city ) {
	$paccofacile_api = Paccofacile_Api::get_instance();

	$payload = array(
		'iso_code' => $iso_code,
		'search'   => $city,
	);

	$response = $paccofacile_api->post( 'locality/validation', array(), $payload );

	if ( 200 === $response['code'] ) {
		if ( array_key_exists( 'data', $response ) && array_key_exists( 'items', $response['data'] ) && ! empty( $response['data']['items'] ) ) {
			$array_locality       = array( '' => __( 'Select a city/locality', 'paccofacile-for-woocommerce' ) );
			$count_response_items = count( $response['data']['items'] );
			for ( $i = 0; $i < $count_response_items; $i++ ) {
				$array_locality[ $response['data']['items'][ $i ]['locality'] ] = $response['data']['items'][ $i ]['locality'];
			}
			return $array_locality;
		}
	} else {
		return array();
	}
}

/**
 * Add store locker
 *
 * @param [type] $carrier_id Id Corriere.
 * @param [type] $locker_id Id locker.
 * @return mixed
 */
function paccofacile_add_store_locker( $carrier_id, $locker_id ) {

	$result = update_option( 'paccofacile_pickup_locker_' . $carrier_id, $locker_id );

	if ( $result ) {
		return $locker_id;
	} else {
		return false;
	}
}

/**
 * Get pickup locker
 *
 * @param [type] $carrier_id Id Corriere.
 * @return mixed|bool
 */
function paccofacile_get_pickup_locker( $carrier_id ) {

	$locker_id = get_option( 'paccofacile_pickup_locker_' . $carrier_id );

	if ( $locker_id ) {
		return $locker_id;
	} else {
		return false;
	}
}

/**
 * Get lockers
 *
 * @param [type] $postcode Postal code.
 * @param [type] $city City.
 * @return array
 */
function paccofacile_get_lockers( $postcode, $city ) {
	$paccofacile_api = Paccofacile_Api::get_instance();

	// prendo le coordinate del comune indicato.
	$locality = paccofacile_get_location_info( $postcode, $city );

	if ( $locality ) {
		$payload  = array(
			'latitude'    => $locality['latitude'],
			'longitude'   => $locality['longitude'],
			'corriere_id' => 10,
		);
		$response = $paccofacile_api->get( 'lockers', array(), $payload );

		if ( 200 === $response['code'] ) {
			if ( array_key_exists( 'data', $response ) ) {
				return $response['data'];
			}
		} else {
			return array();
		}
	} else {
		return array();
	}
}

/**
 * Get location info
 *
 * @param string $postcode Postal code.
 * @param string $city City.
 * @return array
 */
function paccofacile_get_location_info( $postcode, $city ) {
	$paccofacile_api = Paccofacile_Api::get_instance();

	$payload = array(
		'postcode' => $postcode,
		'city'     => $city,
	);

	$response = $paccofacile_api->post( 'locality/search', array(), $payload );

	if ( 200 === $response['code'] ) {
		if ( array_key_exists( 'data', $response ) ) {
			return $response['data'];
		}
	} else {
		return array();
	}
}

/**
 * Search locality
 *
 * @param array $fields Admin shipping fields.
 * @return array
 */
function paccofacile_cerca_localita( $fields ) {
	global $post;

	$shipment_draft_id = get_post_meta( $post->ID, 'shipment_draft_id', true );

	$iso_code = get_post_meta( $post->ID, '_shipping_country', true );
	$city     = get_post_meta( $post->ID, '_shipping_city', true );

	if ( $shipment_draft_id ) {
		$fields['city'] = array(
			'label'   => __( 'City / Locality', 'paccofacile-for-woocommerce' ),
			'show'    => false,
			'type'    => 'select',
			'class'   => 'js_field-city select short',
			'options' => paccofacile_search_locality( $iso_code, $city ),
		);
	}
	return $fields;
}
add_filter( 'woocommerce_admin_shipping_fields', 'paccofacile_cerca_localita' );

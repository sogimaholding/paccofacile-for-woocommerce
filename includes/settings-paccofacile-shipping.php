<?php
/**
 * Paccofacile settings shipping
 *
 * HOWEVER, on occasion Paccofacile will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.

 * @package Paccofacile
 * @subpackage Paccofacile/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$cost_desc = __( 'Enter a cost (excl. tax) or sum, e.g. <code>10.00 * [qty]</code>.', 'paccofacile-for-woocommerce' ) . '<br/><br/>' . __( 'Use <code>[qty]</code> for the number of items, <br/><code>[cost]</code> for the total cost of items, and <code>[fee percent="10" min_fee="20" max_fee=""]</code> for percentage based fees.', 'paccofacile-for-woocommerce' );
// translators: WooCommerce shipping classes URL.
$desc  = sprintf( __( 'These costs can optionally be added based on the <a href="%s">product shipping class</a>.', 'paccofacile-for-woocommerce' ), admin_url( 'admin.php?page=wc-settings&tab=shipping&section=classes' ) );
$desc .= ' ' . __( 'If no costs are set for shipping classes, costs defined in Paccofacile configuration will be used.', 'paccofacile-for-woocommerce' );

$array_carriers = array( 'none' => __( 'All chosen couriers', 'paccofacile-for-woocommerce' ) );
$carriers       = pfwc_get_available_shipping_methods();

if ( $carriers->have_posts() ) {
	foreach ( $carriers->posts as $corriere ) {
		$service_id = get_post_meta( $corriere->ID, 'service_id', true );
		$array_carriers[ get_post_field( 'post_name', $corriere->ID ) . '_' . $service_id ] = $corriere->post_title;
	}
}

/**
 * Settings for Paccofacile shipping.
 */
$settings = array(
	'title'                      => array(
		'title'       => __( 'Method title', 'paccofacile-for-woocommerce' ),
		'type'        => 'text',
		'description' => __( 'This controls the title which the user sees during checkout.', 'paccofacile-for-woocommerce' ),
		'default'     => isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'Paccofacile.it Shipping', 'paccofacile-for-woocommerce' ),
		'desc_tip'    => true,
	),
	'carrier'                    => array(
		'title'       => __( 'Carrier', 'paccofacile-for-woocommerce' ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'description' => __( 'Choose a carrier to activate it in checkout.', 'paccofacile-for-woocommerce' ),
		'default'     => isset( $this->settings['carrier'] ) ? $this->settings['carrier'] : 'none',
		'desc_tip'    => true,
		'options'     => $array_carriers,
	),
	'activation_condition'       => array(
		'title'       => __( 'Activation condition', 'paccofacile-for-woocommerce' ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'description' => __( 'Condition to activate this shipping method.', 'paccofacile-for-woocommerce' ),
		'default'     => isset( $this->settings['activation_condition'] ) ? $this->settings['activation_condition'] : 'none',
		'desc_tip'    => true,
		'options'     => array(
			'none'      => __( 'Always active', 'paccofacile-for-woocommerce' ),
			'by_weight' => __( 'By weight range', 'paccofacile-for-woocommerce' ),
			'by_price'  => __( 'By cart total price range', 'paccofacile-for-woocommerce' ),
		),
	),
	'min_weight'                 => array(
		'title'       => __( 'Minimum weight (Kg)', 'paccofacile-for-woocommerce' ),
		'type'        => 'text',
		'default'     => isset( $this->settings['min_weight'] ) ? $this->settings['min_weight'] : '',
		'placeholder' => __( 'From Kg', 'paccofacile-for-woocommerce' ),
	),
	'max_weight'                 => array(
		'title'       => __( 'Maximum weight (Kg)', 'paccofacile-for-woocommerce' ),
		'type'        => 'text',
		'default'     => isset( $this->settings['max_weight'] ) ? $this->settings['max_weight'] : '',
		'placeholder' => __( 'To Kg', 'paccofacile-for-woocommerce' ),
	),
	'min_price'                  => array(
		'title'       => __( 'Minimum price (€)', 'paccofacile-for-woocommerce' ),
		'type'        => 'price',
		'default'     => isset( $this->settings['min_price'] ) ? $this->settings['min_price'] : '',
		'placeholder' => __( 'From €', 'paccofacile-for-woocommerce' ),
	),
	'max_price'                  => array(
		'title'       => __( 'Maximum price (€)', 'paccofacile-for-woocommerce' ),
		'type'        => 'price',
		'default'     => isset( $this->settings['max_price'] ) ? $this->settings['max_price'] : '',
		'placeholder' => __( 'To €', 'paccofacile-for-woocommerce' ),
	),
	'price_variation'            => array(
		'title'       => __( 'Price variation', 'paccofacile-for-woocommerce' ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'description' => __( 'Choose if you want to increase or decrease the shipping price.', 'paccofacile-for-woocommerce' ),
		'default'     => isset( $this->settings['price_variation'] ) ? $this->settings['price_variation'] : 'none',
		'desc_tip'    => true,
		'options'     => array(
			'none'     => __( 'None', 'paccofacile-for-woocommerce' ),
			'increase' => __( 'Increase shipping price', 'paccofacile-for-woocommerce' ),
			'decrease' => __( 'Decrease shipping price', 'paccofacile-for-woocommerce' ),
		),
	),
	'price_variation_type'       => array(
		'title'       => __( 'Price variation type', 'paccofacile-for-woocommerce' ),
		'type'        => 'select',
		'class'       => 'wc-enhanced-select',
		'description' => __( 'Choose the type of variation of the shipping price.', 'paccofacile-for-woocommerce' ),
		'default'     => isset( $this->settings['price_variation_type'] ) ? $this->settings['price_variation_type'] : 'fixed',
		'desc_tip'    => true,
		'options'     => array(
			'fixed'      => __( 'Fixed amount €', 'paccofacile-for-woocommerce' ),
			'percentage' => __( 'Percentage %', 'paccofacile-for-woocommerce' ),
		),
	),
	'price_variation_amount'     => array(
		'title'   => __( 'Price variation amount €', 'paccofacile-for-woocommerce' ),
		'type'    => 'price',
		'default' => isset( $this->settings['price_variation_amount'] ) ? $this->settings['price_variation_amount'] : 0,
	),
	'price_variation_percentage' => array(
		'title'   => __( 'Price variation percentage %', 'paccofacile-for-woocommerce' ),
		'type'    => 'number',
		'default' => isset( $this->settings['price_variation_percentage'] ) ? $this->settings['price_variation_percentage'] : 0,
	),

);

$shipping_classes = WC()->shipping->get_shipping_classes();

if ( ! empty( $shipping_classes ) ) {
	$settings['class_costs'] = array(
		'title'       => __( 'Shipping class costs', 'paccofacile-for-woocommerce' ),
		'type'        => 'title',
		'default'     => '',
		'description' => $desc,
	);

	foreach ( $shipping_classes as $shipping_class ) {
		if ( ! isset( $shipping_class->term_id ) ) {
			continue;
		}

		$settings[ 'class_cost_' . $shipping_class->term_id ] = array(
			/* translators: %s: shipping class name */
			'title'       => sprintf( __( '"%s" shipping class cost', 'paccofacile-for-woocommerce' ), esc_html( $shipping_class->name ) ),
			'type'        => 'text',
			'placeholder' => __( 'N/A', 'paccofacile-for-woocommerce' ),
			'description' => $cost_desc,
			'default'     => $this->get_option( 'class_cost_' . $shipping_class->slug ),
			'desc_tip'    => true,
		);
	}

	$settings['class_cost_calculation_type'] = array(
		'title'   => __( 'Calculation type', 'paccofacile-for-woocommerce' ),
		'type'    => 'select',
		'class'   => 'wc-enhanced-select',
		'default' => 'class',
		'options' => array(
			'class' => __( 'Per class: Charge shipping for each shipping class individually', 'paccofacile-for-woocommerce' ),
			'order' => __( 'Per order: Charge shipping for the most expensive shipping class', 'paccofacile-for-woocommerce' ),
		),
	);
}

return $settings;

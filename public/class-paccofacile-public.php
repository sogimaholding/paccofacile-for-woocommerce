<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Paccofacile
 * @subpackage Paccofacile/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Paccofacile
 * @subpackage Paccofacile/public
 * @author     Francesco Barberini <supporto.tecnico@paccofacile.it>
 */
class Paccofacile_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		add_action( 'woocommerce_after_shipping_calculator', array($this, 'paccofacile_locker_map'));

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Paccofacile_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Paccofacile_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( 'open-layers', 'https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/css/ol.css', array(), '6.14.1', 'all' );

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/paccofacile-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Paccofacile_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Paccofacile_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'open-layers', 'https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/build/ol.js', array( 'jquery' ), '6.15.1', false );
		wp_enqueue_script( 'locker-map', plugin_dir_url( __FILE__ ) . 'js/draw-map.js', array( 'jquery' ), $this->version, true );
		

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/paccofacile-public.js', array( 'jquery' ), $this->version, false );

		wp_localize_script( 'locker-map', 'paccofacile_ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		$paccofacile_help_var = array( 'site_url' => get_site_url(), 'pluginUrl' => plugins_url('', __FILE__) );
		wp_localize_script( $this->plugin_name, 'paccofacile_help_var', $paccofacile_help_var );

	}


	public function paccofacile_locker_map() {

		//write_log('entrato paccofacile_locker_map');

		global $woocommerce;
		$postcode = $woocommerce->customer->get_shipping_postcode();
		$city = $woocommerce->customer->get_shipping_city();

		$current_shipping_method = WC()->session->get( 'chosen_shipping_methods' );
		

		// $current_shipping_method[0] == 'paccofacile_shipping_107'

		$current_method_strarray = explode( '_', $current_shipping_method[0] ) ;

		$service_id = end($current_method_strarray);

		/* var_dump($service_id);
		var_dump($current_shipping_method[0]); */

		$args_carriers = array(
			'post_type' => 'carrier',
			'meta_key' => 'service_id',
			'meta_value' => $service_id
		);
		
		$carriers = new WP_Query($args_carriers);

		$pickup_type = 1;
		$carrier_id = '';
		if ( $carriers->have_posts() ) :
			while( $carriers->have_posts() ) :
				$carriers->the_post();
				$pickup_type = get_post_meta( get_the_ID(), 'pickup_type', true );
				$carrier_id = get_post_meta( get_the_ID(), 'carrier_id', true );
			endwhile;
			$carriers->wp_reset_postdata();
		endif;

		/* var_dump($pickup_type); */

		/* @todo: controllare se il metodo di spedizione scelto è compatibile con locker (meta data?) */
		if ($pickup_type == 4 || $pickup_type == 5) :
		
			?>
			<div id="paccofacile-map" class="paccofacile-map" data-postcode="<?php echo $postcode; ?>" data-city="<?php echo $city; ?>" data-carrier-id="<?php echo $carrier_id; ?>" data-store-nonce="<?php echo wp_create_nonce( 'get_store_locker_nonce' ); ?>">
				<div id="popup" class="ol-popup">
					<a href="#" id="popup-closer" class="ol-popup-closer"></a>
					<div id="popup-content"></div>
				</div>
			</div>
			<div id="paccofacile-lockers-list"></div>
			<?php
		
		endif;

	}


	
	public function paccofacile_body_classes( $classes ) {

		if ( is_cart() ) {
			$classes[] = 'paccofacile-active';
		}
		
		return $classes;
		
	}

	
	public function get_lockers_ajax_handler() {
		// maybe check some permissions here, depending on your app
		//if ( ! current_user_can( 'edit_posts' ) ) exit;

		if ( ! ( isset( $_POST['_wpnonce'] ) || wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'get_store_locker_nonce' ) ) ) {
			return;
		}

		$postcode = ( isset( $_POST['postcode'] ) ) ? $_POST['postcode'] : '';
		$city = ( isset( $_POST['city'] ) ) ? $_POST['city'] : '';

		//error_log( 'ajax handler' );

		$return = paccofacile_get_lockers( $postcode, $city );

		//error_log( print_r( $return, true ) );

		// send some information back to the javascipt handler
		if ( $return ) {
			$response = array(
				'status' => '200',
				'message' => 'OK',
				'data' => $return
			);
		} else {
			$response = array(
				'status' => '400',
				'message' => 'error'
			);
		}

		// normally, the script expects a json respone
		header( 'Content-Type: application/json; charset=utf-8' );
		echo wp_json_encode( $response );

		exit; // important!
	}


	public function getCityCoordinates_ajax_handler() {
		// maybe check some permissions here, depending on your app
		//if ( ! current_user_can( 'edit_posts' ) ) exit;

		if ( ! ( isset( $_POST['_wpnonce'] ) || wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'get_store_locker_nonce' ) ) ) {
			return;
		}

		$postcode = ( isset( $_POST['postcode'] ) ) ? $_POST['postcode'] : '';
		$city = ( isset( $_POST['city'] ) ) ? $_POST['city'] : '';

		//error_log( 'ajax handler' );

		$return = paccofacile_get_location_info( $postcode, $city );

		//error_log( print_r( $return, true ) );

		// send some information back to the javascipt handler
		if ( $return ) {
			$response = array(
				'status' => '200',
				'message' => 'OK',
				'data' => $return
			);
		} else {
			$response = array(
				'status' => '400',
				'message' => 'error'
			);
		}

		// normally, the script expects a json respone
		header( 'Content-Type: application/json; charset=utf-8' );
		echo wp_json_encode( $response );

		exit; // important!
	}


	public function locker_id_session_ajax_handler() {

		if ( ! ( isset( $_POST['_wpnonce'] ) || wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'woocommerce-cart-nonce' ) ) ) {
			return;
		} else {
			error_log( 'woocommerce-cart-nonce settato' );
		}

		$locker_id = (array_key_exists('locker_id', $_POST)) ? $_POST['locker_id'] : '';

		WC()->session->set( 'locker_id', $locker_id );

		// normally, the script expects a json response.
		header( 'Content-Type: application/json; charset=utf-8' );
		echo wp_json_encode( array( 'header' => array('success'=>true), 'data' => true ) );

		exit; // important!
	}


}

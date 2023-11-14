<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Paccofacile
 * @subpackage Paccofacile/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Paccofacile
 * @subpackage Paccofacile/admin
 * @author     Francesco Barberini <supporto.tecnico@paccofacile.it>
 */
class Paccofacile_Admin {

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

	public $settings;

	public $default_tracking_to_show;
	public $tracking_status_label;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->default_tracking_to_show = [
			'delivered' => 1,
			'exception' => 1,
			'expired' => 1,
			'inforeceived' => 1,
			'outfordelivery' => 1,
			'attemptfail' => 1,
			'pending' => 1,
			'intransit' => 1
		];
		$this->settings = array('api_key' => '', 'token' => '', 'account_number' => '', 'tracking_to_show' => $this->default_tracking_to_show);

		$this->paccofacile_tracking_status_key_to_label();

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( 'fontawesome-core', plugin_dir_url( __FILE__ ) . 'fontawesome/css/fontawesome.css', array(), '6.1.1', 'all' );
		wp_enqueue_style( 'fontawesome-solid', plugin_dir_url( __FILE__ ) . 'fontawesome/css/solid.css', array(), '6.1.1', 'all' );

		wp_enqueue_style( 'open-layers', 'https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/css/ol.css', array(), '6.15.1', 'all' );

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/paccofacile-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		//wp_enqueue_script( 'open-layers', 'https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.14.1/build/ol.js', array( 'jquery' ), '6.14.1', false );
		wp_enqueue_script( 'open-layers', 'https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.15.1/build/ol.js', array( 'jquery' ), '6.15.1', false );
		wp_enqueue_script( 'locker-map', plugin_dir_url( __FILE__ ) . 'js/draw-map.js', array( 'jquery' ), $this->version, true );

		//Add the Select2 CSS file
		wp_enqueue_style( 'select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css', array(), '4.1.0-rc.0');

		//Add the Select2 JavaScript file
		wp_enqueue_script( 'select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', 'jquery', '4.1.0-rc.0');

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/paccofacile-admin.js', array( 'jquery' ), $this->version, false );

		$paccofacile_help_var = array( 'site_url' => get_site_url(), 'pluginUrl' => plugins_url('', __FILE__) );
    	wp_localize_script( $this->plugin_name, 'paccofacile_help_var', $paccofacile_help_var );

	}

	/**
	 * Adds Paccofacile item to backend administrator menu.
	 */
	public function load_admin_menu() {
		if ( is_admin() && ! is_network_admin() ) {
			add_action( 'admin_menu', array( $this, 'create_admin_submenu' ) );
		}
	}

	/**
	 * Creates Paccofacile item in administrator menu.
	 */
	public function create_admin_submenu() {
		add_submenu_page(
			'woocommerce',
			'Paccofacile',
			'Paccofacile',
			'manage_options',
			'paccofacile',
			array( $this, 'render_plugin_settings_page' )
		);
	}


	public function render_plugin_settings_page() {
		
		include dirname( __FILE__ ) . '/partials/paccofacile-admin-display.php';
		
	}
	
	
	public function paccofacile_register_settings() {
		// SET TO FALSE THE API KEY VALIDATION ON START
		add_option( 'paccofacile_api_valid', '0');

		register_setting( 'paccofacile_settings', 'paccofacile_settings', array( 'sanitize_callback' => array($this, 'paccofacile_settings_validate') ) );
		
		add_settings_section( 'api_settings', __('API Settings', 'paccofacile'), array($this, 'paccofacile_section_api_text'), 'paccofacile' );
		add_settings_field( 'paccofacile_setting_api_key', __('API Key', 'paccofacile'), array($this, 'paccofacile_setting_api_key'), 'paccofacile', 'api_settings' );
		add_settings_field( 'paccofacile_setting_token', __('Token', 'paccofacile'), array($this, 'paccofacile_setting_token'), 'paccofacile', 'api_settings' );
		add_settings_field( 'paccofacile_setting_account_number', __('Account Number', 'paccofacile'), array($this, 'paccofacile_setting_account_number'), 'paccofacile', 'api_settings' );
		
		add_settings_section( 'tracking_settings', __('Tracking', 'paccofacile'), array($this, 'paccofacile_section_tracking_text'), 'paccofacile_tracking' );
		add_settings_field( 'paccofacile_tracking_to_show', __('Tracking info to show', 'paccofacile'), array($this, 'paccofacile_tracking_to_show'), 'paccofacile_tracking', 'tracking_settings' );
		
		
		/* register_setting( 'paccofacile_settings_refund', 'paccofacile_settings_refund', array( 'sanitize_callback' => array($this, 'paccofacile_settings_refund_options') ) );
		
		add_settings_section( 'refund_method', __('Refund methods', 'paccofacile'), array($this, 'paccofacile_section_refund_methods'), 'paccofacile_refund' );
		
		add_settings_section( 'refund_method_paypal', __('PayPal refund', 'paccofacile'), array($this, 'paccofacile_section_refund_methods'), 'paccofacile_refund_paypal' );
		add_settings_field( 'paccofacile_refund_paypal_email', __('Paypal email', 'paccofacile'), array($this, 'paccofacile_refund_paypal_email'), 'paccofacile_refund_paypal', 'refund_method_paypal' );
		
		add_settings_section( 'refund_method_wire_transfer', __('Wire Transfer refund', 'paccofacile'), array($this, 'paccofacile_section_refund_methods'), 'paccofacile_refund_wire_transfer' );
		add_settings_field( 'paccofacile_refund_wire_transfer_header', __('Bank header', 'paccofacile'), array($this, 'paccofacile_refund_wire_transfer_header'), 'paccofacile_refund_wire_transfer', 'refund_method_wire_transfer' );
		add_settings_field( 'paccofacile_refund_wire_transfer_bank', __('Bank', 'paccofacile'), array($this, 'paccofacile_refund_wire_transfer_bank'), 'paccofacile_refund_wire_transfer', 'refund_method_wire_transfer' );
		add_settings_field( 'paccofacile_refund_wire_transfer_iban', __('IBAN', 'paccofacile'), array($this, 'paccofacile_refund_wire_transfer_iban'), 'paccofacile_refund_wire_transfer', 'refund_method_wire_transfer' );
		add_settings_field( 'paccofacile_refund_wire_transfer_bic', __('BIC', 'paccofacile'), array($this, 'paccofacile_refund_wire_transfer_bic'), 'paccofacile_refund_wire_transfer', 'refund_method_wire_transfer' ); */
		

	}

	public function paccofacile_check_api_auth( $api_key, $token, $account_number ) {
		$paccofacile_api = Paccofacile_Api::getInstance();

		$headers = array(
			'Account-Number' => $account_number,
            'api-key' => $api_key,
            'Authorization' => 'Bearer '.$token
		);

		$payload = array(
			'domain' => get_site_url(),
			'api_version' => 'v1',
			'endpoint_push_tracking' => get_rest_url( null, 'paccofacile/v1/order_tracking' ),
			'endpoint_push_documents' => get_rest_url( null, 'paccofacile/v1/order_documents/{order_id}' )
		);

		$response = $paccofacile_api->post( 'key_validation', $headers, $payload );

		if( $response['code'] === 401 ) return false;

		return true;

	}

	public function paccofacile_settings_validate( $input ) {
		$newinput['api_key'] = trim( $input['api_key'] );
		$newinput['account_number'] = trim( $input['account_number'] );
		$newinput['token'] = trim( $input['token'] );
		$newinput['tracking_to_show'] = $input['tracking_to_show'];
		$valid = true;
		
		if ( empty( $newinput['api_key'] ) ) {
			$valid = false;
			add_settings_error( 'api_key', 'invalid_api_settings', __('API Key is incorrect.', 'paccofacile') );
			$newinput['api_key'] = '';
		}

		if ( empty( $newinput['account_number'] ) ) {
			$valid = false;
			add_settings_error( 'account_number', 'invalid_account_number', __('Account number is incorrect.', 'paccofacile') );
			
		}

		if( $valid === true ) {
			$valid = $this->paccofacile_check_api_auth( $newinput['api_key'], $newinput['token'], $newinput['account_number'] );
			if( $valid === false ) {
				update_option( 'paccofacile_api_valid', '0' );
				add_settings_error( 'api_auth', 'invalid_api_auth', __('API credentials are not valid.', 'paccofacile') );
			} else {
				update_option( 'paccofacile_api_valid', '1' );
			}
		}

		// Ignore the user's changes and use the old database value.
		if ( ! $valid ) {
			$newinput = get_option( 'paccofacile_settings' );
		}
	
		return $newinput;
	}

	/* public function paccofacile_settings_refund_options( $input ) {
		$newinput['paypal_email'] = trim( $input['paypal_email'] );
		$newinput['wire_transfer_header'] = trim( $input['wire_transfer_header'] );
		$newinput['wire_transfer_bank'] = trim( $input['wire_transfer_bank'] );
		$newinput['wire_transfer_iban'] = trim( $input['wire_transfer_iban'] );
		$newinput['wire_transfer_bic'] = trim( $input['wire_transfer_bic'] );
		$valid = true;

		$wire_transfer_data = array(
			'header' => $newinput['wire_transfer_header'],
			'bank' => $newinput['wire_transfer_bank'],
			'iban' => $newinput['wire_transfer_iban'],
			'bic' => $newinput['wire_transfer_bic']
		);

		if( $valid === true ) {
			
			update_option( 'paccofacile_refund_method_paypal_email', $newinput['paypal_email'] );
			
			update_option( 'paccofacile_refund_method_wire_transfer_data', $wire_transfer_data );
		}

		if ( ! $valid ) {
			$newinput = get_option( 'paccofacile_settings_refund' );
		}
	
		return $newinput;
	} */

	public function paccofacile_section_api_text() {
		echo '<p>'.__('You will find the API Keys to activate Paccofacile.it PRO plugin in your Paccofacile.it account follow this path: Paccofacile PRO Dashboard -> Integrations -> WooCommerce -> Generate Keys.', 'paccofacile').'</p>';
	}
	
	public function paccofacile_section_tracking_text() {
		echo '<p>'.__('Check which notification you want to send to your customers to keep them updated on the tracking of their shipments.', 'paccofacile').'</p>';
	}
	
	public function paccofacile_setting_api_key() {
		$options = get_option( 'paccofacile_settings' );
		if(!is_array($options)) {
			$options = $this->settings;
		}
		echo "<input id='paccofacile_setting_api_key' name='paccofacile_settings[api_key]' type='text' value='" . esc_attr( $options['api_key'] ) . "' />";
	}

	/* public function paccofacile_section_refund_methods() {

	}

	public function paccofacile_refund_paypal_email() {
		$options = get_option( 'paccofacile_settings_refund' );
		if(!is_array($options)) {
			$options = $this->settings;
		}
		echo "<input id='paccofacile_refund_paypal_email' name='paccofacile_settings_refund[paypal_email]' type='email' value='" . esc_attr( $options['paypal_email'] ) . "' />";
	}

	public function paccofacile_refund_wire_transfer_header() {
		$options = get_option( 'paccofacile_settings_refund' );
		if(!is_array($options)) {
			$options = $this->settings;
		}
		echo "<input id='paccofacile_refund_wire_transfer_header' name='paccofacile_settings_refund[wire_transfer_header]' type='text' value='" . esc_attr( $options['wire_transfer_header'] ) . "' />";
	}
	public function paccofacile_refund_wire_transfer_bank() {
		$options = get_option( 'paccofacile_settings_refund' );
		if(!is_array($options)) {
			$options = $this->settings;
		}
		echo "<input id='paccofacile_refund_wire_transfer_bank' name='paccofacile_settings_refund[wire_transfer_bank]' type='text' value='" . esc_attr( $options['wire_transfer_bank'] ) . "' />";
	}
	public function paccofacile_refund_wire_transfer_iban() {
		$options = get_option( 'paccofacile_settings_refund' );
		if(!is_array($options)) {
			$options = $this->settings;
		}
		echo "<input id='paccofacile_refund_wire_transfer_iban' name='paccofacile_settings_refund[wire_transfer_iban]' type='text' value='" . esc_attr( $options['wire_transfer_iban'] ) . "' />";
	}
	public function paccofacile_refund_wire_transfer_bic() {
		$options = get_option( 'paccofacile_settings_refund' );
		if(!is_array($options)) {
			$options = $this->settings;
		}
		echo "<input id='paccofacile_refund_wire_transfer_bic' name='paccofacile_settings_refund[wire_transfer_bic]' type='text' value='" . esc_attr( $options['wire_transfer_bic'] ) . "' />";
	} */
	
	public function paccofacile_setting_token() {
		$options = get_option( 'paccofacile_settings' );
		if(!is_array($options)) {
			$options = $this->settings;
		}
		echo "<input id='paccofacile_setting_token' name='paccofacile_settings[token]' type='text' value='" . esc_attr( $options['token'] ) . "' />";
	}
	
	public function paccofacile_setting_account_number() {
		$options = get_option( 'paccofacile_settings' );
		if(!is_array($options)) {
			$options = $this->settings;
		}
		echo "<input id='paccofacile_setting_account_number' name='paccofacile_settings[account_number]' type='text' value='" . esc_attr( $options['account_number'] ) . "' />";
	}

	public function paccofacile_tracking_status_key_to_label() {
		$this->tracking_status_label = array();
		foreach($this->default_tracking_to_show as $key => $value) {
			switch($key) {
				case 'delivered':
					$this->tracking_status_label['delivered'] = __('Delivered', 'paccofacile');
					break;
				case 'exception':
					$this->tracking_status_label['exception'] = __('Exception', 'paccofacile');
					break;
				case 'expired':
					$this->tracking_status_label['expired'] = __('Expired', 'paccofacile');
					break;
				case 'inforeceived':
					$this->tracking_status_label['inforeceived'] = __('Info received', 'paccofacile');
					break;
				case 'outfordelivery':
					$this->tracking_status_label['outfordelivery'] = __('Out for delivery', 'paccofacile');
					break;
				case 'attemptfail':
					$this->tracking_status_label['attemptfail'] = __('Attempt failed', 'paccofacile');
					break;
				case 'pending':
					$this->tracking_status_label['pending'] = __('Pending', 'paccofacile');
					break;
				case 'intransit':
					$this->tracking_status_label['intransit'] = __('In transit', 'paccofacile');
					break;
			}
		}
	}
	
	public function paccofacile_tracking_to_show() {
		$options = get_option( 'paccofacile_settings' );
		if(!is_array($options)) {
			$options = $this->settings;
		}
		echo '<div id="paccofacile_setting_tracking_to_show">';
		foreach($this->default_tracking_to_show as $key => $value) {
			if( array_key_exists('tracking_to_show', $options) && array_key_exists($key, $options['tracking_to_show']) && $options['tracking_to_show'][$key] == 1 ) {
				$checked = 'checked="checked"';
			} else {
				$checked = '';
			}
			
			echo "<div><label><input id='paccofacile_setting_tracking_to_show' name='paccofacile_settings[tracking_to_show][".$key."]' type='checkbox' value='1' ".$checked." /> ".$this->tracking_status_label[$key]."</label></div>";
		}
		echo '</div>';
	}

	public function paccofacile_pay_order_ajax_handler() {

		// Check if our nonce is set (and our cutom field)
		if ( ! isset( $_POST[ 'paccofacile_meta_field_nonce' ] ) && isset( $_POST['paccofacile_pay_order'] ) )
			return;

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST[ 'paccofacile_meta_field_nonce' ] ) ) return;

		$post_id = $_POST['order_id'];

		
		// maybe check some permissions here, depending on your app
		if ( ! current_user_can( 'edit_shop_order', $post_id ) && ! current_user_can( 'edit_shop_orders', $post_id ) ) exit;
		

		$paccofacile_api = Paccofacile_Api::getInstance();

		$data_fattura = ( !array_key_exists('paccofacile_billing_detail', $_POST) || $_POST['paccofacile_billing_detail'] == '1' ) ? "" : $_POST['paccofacile_billing_date'];
		$address_id_select = ( !array_key_exists('paccofacile_billing_detail', $_POST) || $_POST['paccofacile_billing_detail'] == '1' ) ? "" : $_POST['paccofacile_billing_address'];

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

		if(array_key_exists( 'order', $response ) && $response['order']['order_id']) {
			update_post_meta($post_id, 'paccofacile_order_id', $response['order']['order_id'] );
			update_post_meta($post_id, 'paccofacile_order_status', 'paid' );

		}

		header( 'Content-Type: application/json; charset=utf-8' );
		echo wp_json_encode( $response );

		exit; // important

		//handle your form data here by accessing $_POST
		//error_log( wp_json_encode($_POST, true) );
	}
	
	
	public function paccofacile_ship_with_ajax_handler() {

		

		// Check if our nonce is set (and our cutom field)
		if ( ! isset( $_POST[ 'paccofacile_meta_field_nonce' ] ) && isset( $_POST['paccofacile_ship_with'] ) )
			return;

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST[ 'paccofacile_meta_field_nonce' ] ) ) return;

		$post_id = $_POST['order_id'];
		$order_obj = wc_get_order( $post_id );
		
		// maybe check some permissions here, depending on your app
		if ( ! current_user_can( 'edit_shop_order', $post_id ) && ! current_user_can( 'edit_shop_orders', $post_id ) ) exit;

		paccofacile_quote_and_save_by_woo_order($order_obj, 'after_order');

		exit; // important

		//handle your form data here by accessing $_POST
		//error_log( wp_json_encode($_POST, true) );
	}

	public function add_carrier_ajax_handler() {
		// maybe check some permissions here, depending on your app
		//if ( ! current_user_can( 'edit_posts' ) ) exit;

		/* action -> add_carrier_nonce
		name -> _wpnonce */

		if ( ! ( isset( $_POST['_wpnonce'] ) || wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'add_carrier_nonce' ) ) ) {
			return;
		}

		$data = array(
			'post_title' => $_POST['carrier_name'].' '.$_POST['service_name'].' | '.$_POST['box_type'],
			'post_type' => 'carrier',
			'post_status' => 'publish'
		);
		//handle your form data here by accessing $_POST
		//error_log( wp_json_encode($_POST, true) );

		$new_post_ID = wp_insert_post( $data );

		// send some information back to the javascipt handler
		if( !is_wp_error( $new_post_ID ) ) {
			add_post_meta( $new_post_ID, 'service_id', $_POST['service_id'] );
			add_post_meta( $new_post_ID, 'pickup_type', $_POST['pickup_type'] );
			add_post_meta( $new_post_ID, 'carrier_id', $_POST['carrier_id'] );
			add_post_meta( $new_post_ID, 'image_url', $_POST['image_url'] );
			add_post_meta( $new_post_ID, 'carrier_ship_time', $_POST['carrier_ship_time'] );
			$response = array(
				'status' => '200',
				'message' => 'OK',
				'new_post_ID' => $new_post_ID,
				'nonce' => wp_create_nonce( 'form-nonce' ),
				'delete_button_label' => __('Delete', 'paccofacile')
			);
			foreach($_POST as $key => $value) {
				$response[$key] = $value;
			}
		} else {
			$response = array(
				'status' => '400',
				'message' => 'WP Error'
			);
		}

		// normally, the script expects a json respone
		header( 'Content-Type: application/json; charset=utf-8' );
		echo wp_json_encode( $response );

		exit; // important
	}
	
	public function add_box_ajax_handler() {

		// maybe check some permissions here, depending on your app
		//if ( ! current_user_can( 'edit_posts' ) ) exit;

		//die();

		if ( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'add_box_nonce' ) ) {
			return;
		}

		$plugin = new Paccofacile();

		$action = $_POST['action'];
		unset($_POST['action']);

		$box_type = $_POST['tipo'];

		//$post_data = $_POST;

		if( $action == 'add_box' ) {

			$imballo = $plugin->create_package( $_POST );
		} elseif( $action == 'edit_box' ) {
			$imballo_id = $_POST['imballo_id'];
			unset($_POST['imballo_id']);
			
			$imballo = $plugin->update_package( $imballo_id, $_POST );
		}
		/* error_log(wp_json_encode($imballo)); */

		//handle your form data here by accessing $_POST
		//error_log( wp_json_encode($_POST, true) )

		if( $box_type == 1 ) { // PACCO
			
			$icon = '<svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M447.9 176c0-10.6-2.6-21-7.6-30.3l-49.1-91.9c-4.3-13-16.5-21.8-30.3-21.8H87.1c-13.8 0-26 8.8-30.4 21.9L7.6 145.8c-5 9.3-7.6 19.7-7.6 30.3C.1 236.6 0 448 0 448c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32 0 0-.1-211.4-.1-272zm-87-112l50.8 96H286.1l-12-96h86.8zM192 192h64v64h-64v-64zm49.9-128l12 96h-59.8l12-96h35.8zM87.1 64h86.8l-12 96H36.3l50.8-96zM32 448s.1-181.1.1-256H160v64c0 17.7 14.3 32 32 32h64c17.7 0 32-14.3 32-32v-64h127.9c0 74.9.1 256 .1 256H32z" class=""></path></svg>';

		} elseif( $box_type == 3 ) { // PALLET
			
			$icon = '<svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="currentColor" d="M144 288h352c8.8 0 16-7.2 16-16V16c0-8.8-7.2-16-16-16H144c-8.8 0-16 7.2-16 16v256c0 8.8 7.2 16 16 16zM288 32h64v76.2l-32-16-32 16V32zm-128 0h96v128l64-32 64 32V32h96v224H160V32zm472 320c4.4 0 8-3.6 8-8v-16c0-4.4-3.6-8-8-8H8c-4.4 0-8 3.6-8 8v16c0 4.4 3.6 8 8 8h56v128H8c-4.4 0-8 3.6-8 8v16c0 4.4 3.6 8 8 8h624c4.4 0 8-3.6 8-8v-16c0-4.4-3.6-8-8-8h-56V352h56zM160 480H96V352h64v128zm288 0H192V352h256v128zm96 0h-64V352h64v128z" class=""></path></svg>';

		} elseif( $box_type == 2 ) { // BUSTA
			
			$icon = '<svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M464 64H48C21.5 64 0 85.5 0 112v288c0 26.5 21.5 48 48 48h416c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48zM48 96h416c8.8 0 16 7.2 16 16v41.4c-21.9 18.5-53.2 44-150.6 121.3-16.9 13.4-50.2 45.7-73.4 45.3-23.2.4-56.6-31.9-73.4-45.3C85.2 197.4 53.9 171.9 32 153.4V112c0-8.8 7.2-16 16-16zm416 320H48c-8.8 0-16-7.2-16-16V195c22.8 18.7 58.8 47.6 130.7 104.7 20.5 16.4 56.7 52.5 93.3 52.3 36.4.3 72.3-35.5 93.3-52.3 71.9-57.1 107.9-86 130.7-104.7v205c0 8.8-7.2 16-16 16z" class=""></path></svg>';

		}

		$response = array(
			'status' => '200',
			'message' => 'OK',
			'new_post_ID' => $imballo['imballo_id'],
			'nonce' => wp_create_nonce( 'form-nonce' ),
			'edit_button_label' => __('Edit', 'paccofacile'),
			'delete_button_label' => __('Delete', 'paccofacile'),
			'icon' => $icon,
			'box_name' => $imballo['nome'],
			'box_type' => $imballo['tipo'],
			'dim1' => $imballo['dim1'],
			'dim2' => $imballo['dim2'],
			'dim3' => $imballo['dim3'],
			'volume' => $imballo['volume'],
			'action' => $action,
			'confirm_edit_button_label' => __('Edit package', 'paccofacile')
		);

		$response['max_weight'] = ( isset($_POST['peso_max']) ) ? $_POST['peso_max'] : 0;
		$response['max_height'] = ( isset($_POST['altezza_max']) ) ? $_POST['altezza_max'] : null;

		// send some information back to the javascipt handler
		if( !$imballo ) {
			$response = array(
				'status' => '400',
				'message' => 'WP Error'
				//'new_post_ID' => $new_post_ID
			);
		}

		// normally, the script expects a json respone
		header( 'Content-Type: application/json; charset=utf-8' );
		echo wp_json_encode( $response );

		exit; // important
	}
	
	public function delete_carrier_ajax_handler() {
		// maybe check some permissions here, depending on your app
		//if ( ! current_user_can( 'edit_posts' ) ) exit;

		if ( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'delete_carrier_nonce' ) ) {
			return;
		}

		$post_id = $_POST['post_id'];

		$return = wp_delete_post( $post_id, true );

		// send some information back to the javascipt handler
		if( $return ) {
			$response = array(
				'status' => '200',
				'message' => 'OK'
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

		exit; // important
	}
	
	public function delete_box_ajax_handler() {
		// maybe check some permissions here, depending on your app
		//if ( ! current_user_can( 'edit_posts' ) ) exit;

		if ( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'delete_box_nonce' ) ) {
			return;
		}

		$plugin = new Paccofacile();

		$imballo_id = $_POST['imballo_id'];

		$return = $plugin->delete_package($imballo_id);

		// send some information back to the javascipt handler
		if( $return ) {
			$response = array(
				'status' => '200',
				'message' => 'OK'
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

		exit; // important
	}
	
	public function add_shipping_customes_ajax_handler() {

		$paccofacile_api = Paccofacile_Api::getInstance();
		// maybe check some permissions here, depending on your app
		//if ( ! current_user_can( 'edit_posts' ) ) exit;

		if ( !isset( $_POST['_wpnonce'] ) || !wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'add_shipping_customes' ) ) {
			return;
		}

		
		if( !isset( $_POST['total_goods_value'] ) ) $_POST['total_goods_value'] = 0;

		$items_amount_sum = $items_weight_sum = 0;

		for($i=1;$i<5;$i++) {
			if( !isset( $_POST['customs_'.$i.'_quantity'] ) ) $_POST['customs'.$i.'_quantity'] = 0;
			if( !isset( $_POST['customs_'.$i.'_weight'] ) ) $_POST['customs'.$i.'_weight'] = 0;
			if( !isset( $_POST['customs_'.$i.'_amount'] ) ) $_POST['customs'.$i.'_amount'] = 0;

			$items_weight_sum += $_POST['customs_'.$i.'_weight'];
			$items_amount_sum += $_POST['customs_'.$i.'_amount'];
		}


		// VALIDAZIONE
		/* if( wp_verify_nonce($_POST['nonce']) !== false ) { */
			if( $_POST['total_goods_value'] != $items_amount_sum || $_POST['order_weight'] != $items_weight_sum ) {
				$response = array(
					'status' => '400'
				);
				if( $_POST['total_goods_value'] != $items_amount_sum ) {
					$response['message'][] = __('The total goods amount must match the amounts of the articles to ship.', 'paccofacile');
				}
				if( $_POST['order_weight'] != $items_weight_sum ) {
					$response['message'][] = __('The sum of the articles weight must match the weight of the order. (%s Kg)', 'paccofacile');
				}
			} else {
				// SALVO LE INFO DOGANALI NELL'ORDINE

				$payload_ordine = get_post_meta( $_POST['woo_order_id'], 'paccofacile_order_payload', true );

				$articles = array();

				for($i=1;$i<5;$i++) {
					if( $_POST['customs_'.$i.'_quantity'] != 0 && $_POST['customs_'.$i.'_weight'] != 0 && $_POST['customs_'.$i.'_amount'] != 0 ) {
						$article = array(
							"amount" => array(
								"value" => $_POST['customs_'.$i.'_amount'],
								"currency" => "EUR"
							),
							"quantity" => $_POST['customs_'.$i.'_quantity'],
							"weight" => $_POST['customs_'.$i.'_weight'],
							"description" => $_POST['customs_'.$i.'_description'],
							"iso_code_country_manufactured" => "IT"
						);

						$articles[] = $article;
					}
				}

				$customs = array(
					"amount" => array(
						"value" => $_POST['total_goods_value'],
						"currency" => "EUR"
					),
					"articles" => $articles
				);

				$payload_ordine['customs'] = $customs;
				
				if($_POST['shipment_id']) {
					/* if( array_key_exists('shipment_id', $payload_ordine) ) {
						$payload_ordine['shipment_id'] = $_POST['shipment_id'];
					} */

					$response_ordine = $paccofacile_api->post('shipment/save', array(), $payload_ordine);
					update_post_meta( $_POST['woo_order_id'], 'paccofacile_order_payload', $payload_ordine );
				} else {
					$shipment_draft_id = get_post_meta( $_POST['woo_order_id'], 'shipment_draft_id', true );

					$payload_ordine['shipment_draft_id'] = $shipment_draft_id;

					$response_ordine = $paccofacile_api->post('shipment/save', array(), $payload_ordine);
				}


				if( $response_ordine['code'] == 200 ) {
					update_post_meta( $_POST['woo_order_id'], 'customes', $customs );
					
					delete_post_meta( $_POST['woo_order_id'], 'shipment_draft_id' );

					$response = array(
						'status' => '200',
						'message' => 'OK'
					);
				} else {
					
					$response = array(
						'status' => '400',
						'message' => array( __('Error while saving customs info. Please check the fields and retry.', 'paccofacile') )
					);

				}


			}
		/* } else {
			$response = array(
				'status' => '401',
				'message' => 'unauthorized'
			);
		} */

		// normally, the script expects a json response
		header( 'Content-Type: application/json; charset=utf-8' );
		$response['order_weight'] = $_POST['order_weight'];
		echo wp_json_encode( $response );

		exit; // important
	}

	public function search_locality_ajax_handler() {
		// maybe check some permissions here, depending on your app
		//if ( ! current_user_can( 'edit_posts' ) ) exit;

		if ( ! ( isset( $_POST['woocommerce_meta_nonce'] ) || wp_verify_nonce( sanitize_key( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) ) {
			return;
		}

		$iso_code = $_POST['iso_code'];
		$city = $_POST['city'];

		$return = paccofacile_search_locality( $iso_code, $city );

		//error_log( print_r( $return, true ) );

		// send some information back to the javascipt handler
		if( $return ) {
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

		exit; // important
	}
	
	
	public function add_store_locker_ajax_handler() {
		// maybe check some permissions here, depending on your app
		//if ( ! current_user_can( 'edit_posts' ) ) exit;

		if ( ! ( isset( $_POST['_wpnonce'] ) || wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'add_store_locker_nonce' ) ) ) {
			return;
		}

		$carrier_id = $_POST['carrier_id'];
		$locker_id = $_POST['shipping_locker'];

		$return = paccofacile_add_store_locker( $carrier_id, $locker_id );

		//error_log( print_r( $return, true ) );

		// send some information back to the javascipt handler
		if( $return ) {
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

		exit; // important
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
		if( $return ) {
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

		exit; // important
	}
	

}
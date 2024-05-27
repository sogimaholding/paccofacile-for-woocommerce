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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
	// Exit if accessed directly.
}

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
class PFWC_Paccofacile_Admin {

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
	 * The settings of this plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $settings
	 */
	public $settings;

	/**
	 * The default tracking settings values.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $default_tracking_to_show
	 */
	public $default_tracking_to_show;

	/**
	 * The tracking status labels.
	 *
	 * @since    1.0.0
	 * @access   public
	 * @var      string    $tracking_status_label
	 */
	public $tracking_status_label;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name              = $plugin_name;
		$this->version                  = $version;
		$this->default_tracking_to_show = array(
			'delivered'      => 1,
			'exception'      => 1,
			'expired'        => 1,
			'inforeceived'   => 1,
			'outfordelivery' => 1,
			'attemptfail'    => 1,
			'pending'        => 1,
			'intransit'      => 1,
		);
		$this->settings                 = array(
			'api_key'          => '',
			'token'            => '',
			'account_number'   => '',
			'tracking_to_show' => $this->default_tracking_to_show,
		);

		$this->pfwc_tracking_status_key_to_label();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function pfwc_enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in PFWC_Paccofacile_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The PFWC_Paccofacile_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( 'fontawesome-core', plugin_dir_url( __FILE__ ) . 'fontawesome/css/fontawesome.css', array(), '6.1.1', 'all' );
		wp_enqueue_style( 'fontawesome-solid', plugin_dir_url( __FILE__ ) . 'fontawesome/css/solid.css', array(), '6.1.1', 'all' );

		wp_enqueue_style( 'open-layers', PFWC_PACCOFACILE_PLUGIN_URL . '/openlayers/ol.css', array(), '6.15.1', 'all' );

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/paccofacile-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function pfwc_enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in PFWC_Paccofacile_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The PFWC_Paccofacile_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( 'open-layers', PFWC_PACCOFACILE_PLUGIN_URL . '/openlayers/ol.js', array( 'jquery' ), '6.15.1', false );
		wp_enqueue_script( 'locker-map', plugin_dir_url( __FILE__ ) . 'js/draw-map.js', array( 'jquery' ), $this->version, true );

		// Add the Select2 CSS file!
		wp_enqueue_style( 'select2-css', plugin_dir_url( __FILE__ ) . 'select2/select2.min.css', array(), '4.1.0-rc.0', false );

		// Add the Select2 JavaScript file!
		wp_enqueue_script( 'select2-js', plugin_dir_url( __FILE__ ) . 'select2/select2.min.js', 'jquery', '4.1.0-rc.0', true );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/paccofacile-admin.js', array( 'jquery' ), $this->version, false );

		$paccofacile_help_var = array(
			'site_url'  => get_site_url(),
			'pluginUrl' => plugins_url( '', __FILE__ ),
		);
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
	 *
	 * @return void
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

	/**
	 * Display plugin settings page.
	 *
	 * @return void
	 */
	public function render_plugin_settings_page() {
		include __DIR__ . '/partials/paccofacile-admin-display.php';
	}

	/**
	 * Register plugin settings.
	 *
	 * @return void
	 */
	public function pfwc_register_settings() {
		// SET TO FALSE THE API KEY VALIDATION ON START.
		add_option( 'paccofacile_api_valid', '0' );

		register_setting( 'paccofacile_settings', 'paccofacile_settings', array( 'sanitize_callback' => array( $this, 'pfwc_settings_validate' ) ) );

		add_settings_section( 'api_settings', esc_attr__( 'API Settings', 'paccofacile-for-woocommerce' ), array( $this, 'pfwc_section_api_text' ), 'paccofacile' );
		add_settings_field( 'paccofacile_setting_api_key', esc_attr__( 'API Key', 'paccofacile-for-woocommerce' ), array( $this, 'pfwc_setting_api_key' ), 'paccofacile', 'api_settings' );
		add_settings_field( 'paccofacile_setting_token', esc_attr__( 'Token', 'paccofacile-for-woocommerce' ), array( $this, 'pfwc_setting_token' ), 'paccofacile', 'api_settings' );
		add_settings_field( 'paccofacile_setting_account_number', esc_attr__( 'Account Number', 'paccofacile-for-woocommerce' ), array( $this, 'pfwc_setting_account_number' ), 'paccofacile', 'api_settings' );

		add_settings_section( 'tracking_settings', esc_attr__( 'Tracking', 'paccofacile-for-woocommerce' ), array( $this, 'pfwc_section_tracking_text' ), 'paccofacile_tracking' );
		add_settings_field( 'paccofacile_tracking_to_show', esc_attr__( 'Tracking info to show', 'paccofacile-for-woocommerce' ), array( $this, 'pfwc_tracking_to_show' ), 'paccofacile_tracking', 'tracking_settings' );

		/* phpcs:ignore
		Refund settings.
		register_setting( 'paccofacile_settings_refund', 'paccofacile_settings_refund', array( 'sanitize_callback' => array($this, 'pfwc_settings_refund_options') ) );

		add_settings_section( 'refund_method', esc_attr__('Refund methods', 'paccofacile-for-woocommerce'), array($this, 'pfwc_section_refund_methods'), 'paccofacile_refund' );

		add_settings_section( 'refund_method_paypal', esc_attr__('PayPal refund', 'paccofacile-for-woocommerce'), array($this, 'pfwc_section_refund_methods'), 'paccofacile_refund_paypal' );
		add_settings_field( 'paccofacile_refund_paypal_email', esc_attr__('Paypal email', 'paccofacile-for-woocommerce'), array($this, 'pfwc_refund_paypal_email'), 'paccofacile_refund_paypal', 'refund_method_paypal' );

		add_settings_section( 'refund_method_wire_transfer', esc_attr__('Wire Transfer refund', 'paccofacile-for-woocommerce'), array($this, 'pfwc_section_refund_methods'), 'paccofacile_refund_wire_transfer' );
		add_settings_field( 'paccofacile_refund_wire_transfer_header', esc_attr__('Bank header', 'paccofacile-for-woocommerce'), array($this, 'pfwc_refund_wire_transfer_header'), 'paccofacile_refund_wire_transfer', 'refund_method_wire_transfer' );
		add_settings_field( 'paccofacile_refund_wire_transfer_bank', esc_attr__('Bank', 'paccofacile-for-woocommerce'), array($this, 'pfwc_refund_wire_transfer_bank'), 'paccofacile_refund_wire_transfer', 'refund_method_wire_transfer' );
		add_settings_field( 'paccofacile_refund_wire_transfer_iban', esc_attr__('IBAN', 'paccofacile-for-woocommerce'), array($this, 'pfwc_refund_wire_transfer_iban'), 'paccofacile_refund_wire_transfer', 'refund_method_wire_transfer' );
		add_settings_field( 'paccofacile_refund_wire_transfer_bic', esc_attr__('BIC', 'paccofacile-for-woocommerce'), array($this, 'pfwc_refund_wire_transfer_bic'), 'paccofacile_refund_wire_transfer', 'refund_method_wire_transfer' );
		*/
	}

	/**
	 * Validate API Key
	 *
	 * @param string $api_key    Paccofacile Api Key.
	 * @param string $token    Paccofacile Token.
	 * @param int    $account_number    PaccoafcileAccount Number.
	 * @return bool
	 */
	public function pfwc_check_api_auth( $api_key, $token, $account_number ) {
		$paccofacile_api = PFWC_Paccofacile_Api::get_instance();

		$headers = array(
			'Account-Number' => $account_number,
			'api-key'        => $api_key,
			'Authorization'  => 'Bearer ' . $token,
		);

		$payload = array(
			'domain'                  => get_site_url(),
			'api_version'             => 'v1',
			'endpoint_push_tracking'  => get_rest_url( null, 'paccofacile/v1/order_tracking' ),
			'endpoint_push_documents' => get_rest_url( null, 'paccofacile/v1/order_documents/{order_id}' ),
		);

		$response = $paccofacile_api->post( 'key_validation', $headers, $payload );

		if ( 401 === $response['code'] ) {
			return false;
		}

		return true;
	}

	/**
	 * Setting validations
	 *
	 * @param array $input    array of input values.
	 * @return mixed array
	 */
	public function pfwc_settings_validate( $input ) {
		$newinput['api_key']          = ( array_key_exists( 'api_key', $input ) ) ? trim( $input['api_key'] ) : '';
		$newinput['account_number']   = ( array_key_exists( 'account_number', $input ) ) ? trim( $input['account_number'] ) : '';
		$newinput['token']            = ( array_key_exists( 'token', $input ) ) ? trim( $input['token'] ) : '';
		$newinput['tracking_to_show'] = ( array_key_exists( 'tracking_to_show', $input ) ) ? $input['tracking_to_show'] : '';
		$valid                        = true;

		if ( empty( $newinput['api_key'] ) ) {
			$valid = false;
			add_settings_error( 'api_key', 'invalid_api_settings', esc_attr__( 'API Key is incorrect.', 'paccofacile-for-woocommerce' ) );
			$newinput['api_key'] = '';
		}

		if ( empty( $newinput['account_number'] ) ) {
			$valid = false;
			add_settings_error( 'account_number', 'invalid_account_number', esc_attr__( 'Account number is incorrect.', 'paccofacile-for-woocommerce' ) );
		}

		if ( empty( $newinput['token'] ) ) {
			$valid = false;
			add_settings_error( 'token', 'invalid_token', esc_attr__( 'Token is incorrect.', 'paccofacile-for-woocommerce' ) );
		}

		if ( true === $valid ) {
			$valid = $this->pfwc_check_api_auth( $newinput['api_key'], $newinput['token'], $newinput['account_number'] );
			if ( false === $valid ) {
				update_option( 'paccofacile_api_valid', '0' );
				add_settings_error( 'api_auth', 'invalid_api_auth', esc_attr__( 'API credentials are not valid.', 'paccofacile-for-woocommerce' ) );
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

	/* phpcs:ignore
	Refund settings.
	public function pfwc_settings_refund_options( $input ) {
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

		if ( $valid === true ) {

			update_option( 'paccofacile_refund_method_paypal_email', $newinput['paypal_email'] );
			update_option( 'paccofacile_refund_method_wire_transfer_data', $wire_transfer_data );
		}

		if ( ! $valid ) {
			$newinput = get_option( 'paccofacile_settings_refund' );
		}

		return $newinput;
	}
	*/

	/**
	 * Display Api field label
	 *
	 * @return void
	 */
	public function pfwc_section_api_text() {
		echo '<p>' . esc_attr__( 'You will find the API Keys to activate Paccofacile.it PRO plugin in your Paccofacile.it account follow this path: Paccofacile PRO Dashboard -> Integrations -> WooCommerce -> Generate Keys.', 'paccofacile-for-woocommerce' ) . '</p>';
	}

	/**
	 * Display tracking field label
	 *
	 * @return void
	 */
	public function pfwc_section_tracking_text() {
		echo '<p>' . esc_attr__( 'Check which notification you want to send to your customers to keep them updated on the tracking of their shipments.', 'paccofacile-for-woocommerce' ) . '</p>';
	}

	/**
	 * Display API field
	 *
	 * @return void
	 */
	public function pfwc_setting_api_key() {
		$options = get_option( 'paccofacile_settings' );
		if ( ! is_array( $options ) ) {
			$options = $this->settings;
		}
		echo "<input id='paccofacile_setting_api_key' name='paccofacile_settings[api_key]' type='text' value='" . esc_attr( $options['api_key'] ) . "' />";
	}

	/* phpcs:ignore
	Refund settings.
	public function pfwc_section_refund_methods() {

	}

	public function pfwc_refund_paypal_email() {
		$options = get_option( 'paccofacile_settings_refund' );
		if (!is_array($options)) {
			$options = $this->settings;
		}
		echo "<input id='paccofacile_refund_paypal_email' name='paccofacile_settings_refund[paypal_email]' type='email' value='" . esc_attr( $options['paypal_email'] ) . "' />";
	}

	public function pfwc_refund_wire_transfer_header() {
		$options = get_option( 'paccofacile_settings_refund' );
		if (!is_array($options)) {
			$options = $this->settings;
		}
		echo "<input id='paccofacile_refund_wire_transfer_header' name='paccofacile_settings_refund[wire_transfer_header]' type='text' value='" . esc_attr( $options['wire_transfer_header'] ) . "' />";
	}
	public function pfwc_refund_wire_transfer_bank() {
		$options = get_option( 'paccofacile_settings_refund' );
		if (!is_array($options)) {
			$options = $this->settings;
		}
		echo "<input id='paccofacile_refund_wire_transfer_bank' name='paccofacile_settings_refund[wire_transfer_bank]' type='text' value='" . esc_attr( $options['wire_transfer_bank'] ) . "' />";
	}
	public function pfwc_refund_wire_transfer_iban() {
		$options = get_option( 'paccofacile_settings_refund' );
		if (!is_array($options)) {
			$options = $this->settings;
		}
		echo "<input id='paccofacile_refund_wire_transfer_iban' name='paccofacile_settings_refund[wire_transfer_iban]' type='text' value='" . esc_attr( $options['wire_transfer_iban'] ) . "' />";
	}
	public function pfwc_refund_wire_transfer_bic() {
		$options = get_option( 'paccofacile_settings_refund' );
		if (!is_array($options)) {
			$options = $this->settings;
		}
		echo "<input id='paccofacile_refund_wire_transfer_bic' name='paccofacile_settings_refund[wire_transfer_bic]' type='text' value='" . esc_attr( $options['wire_transfer_bic'] ) . "' />";
	}
	*/

	/**
	 * Token field
	 *
	 * @return void
	 */
	public function pfwc_setting_token() {
		$options = get_option( 'paccofacile_settings' );
		if ( ! is_array( $options ) ) {
			$options = $this->settings;
		}
		echo "<input id='paccofacile_setting_token' name='paccofacile_settings[token]' type='text' value='" . esc_attr( $options['token'] ) . "' />";
	}

	/**
	 * Account number field
	 *
	 * @return void
	 */
	public function pfwc_setting_account_number() {
		$options = get_option( 'paccofacile_settings' );
		if ( ! is_array( $options ) ) {
			$options = $this->settings;
		}
		echo "<input id='paccofacile_setting_account_number' name='paccofacile_settings[account_number]' type='text' value='" . esc_attr( $options['account_number'] ) . "' />";
	}

	/**
	 * Tracking status labels
	 *
	 * @return void
	 */
	public function pfwc_tracking_status_key_to_label() {
		$this->tracking_status_label = array();
		foreach ( $this->default_tracking_to_show as $key => $value ) {
			switch ( $key ) {
				case 'delivered':
					$this->tracking_status_label['delivered'] = esc_attr__( 'Delivered', 'paccofacile-for-woocommerce' );
					break;
				case 'exception':
					$this->tracking_status_label['exception'] = esc_attr__( 'Exception', 'paccofacile-for-woocommerce' );
					break;
				case 'expired':
					$this->tracking_status_label['expired'] = esc_attr__( 'Expired', 'paccofacile-for-woocommerce' );
					break;
				case 'inforeceived':
					$this->tracking_status_label['inforeceived'] = esc_attr__( 'Info received', 'paccofacile-for-woocommerce' );
					break;
				case 'outfordelivery':
					$this->tracking_status_label['outfordelivery'] = esc_attr__( 'Out for delivery', 'paccofacile-for-woocommerce' );
					break;
				case 'attemptfail':
					$this->tracking_status_label['attemptfail'] = esc_attr__( 'Attempt failed', 'paccofacile-for-woocommerce' );
					break;
				case 'pending':
					$this->tracking_status_label['pending'] = esc_attr__( 'Pending', 'paccofacile-for-woocommerce' );
					break;
				case 'intransit':
					$this->tracking_status_label['intransit'] = esc_attr__( 'In transit', 'paccofacile-for-woocommerce' );
					break;
			}
		}
	}

	/**
	 * Tracking settings fields
	 *
	 * @return void
	 */
	public function pfwc_tracking_to_show() {
		$options = get_option( 'paccofacile_settings' );
		if ( ! is_array( $options ) ) {
			$options = $this->settings;
		}
		echo '<div id="paccofacile_setting_tracking_to_show">';
		foreach ( $this->default_tracking_to_show as $key => $value ) {
			if ( array_key_exists( 'tracking_to_show', $options ) && $options['tracking_to_show'] && array_key_exists( $key, $options['tracking_to_show'] ) && '1' === $options['tracking_to_show'][ $key ] ) {
				$checked = 'checked="checked"';
			} else {
				$checked = '';
			}

			echo '<div><label><input id="paccofacile_setting_tracking_to_show" name="paccofacile_settings[tracking_to_show][' . esc_attr( $key ) . ']" type="checkbox" value="1" ' . esc_html( $checked ) . ' /> ' . esc_html( $this->tracking_status_label[ $key ] ) . '</label></div>';
		}
		echo '</div>';
	}

	/**
	 * Pay Order ajax handler.
	 *
	 * @return void
	 */
	public function pfwc_pay_order_ajax_handler() {

		// Check if our nonce is set (and our cutom field)!
		if ( ! isset( $_POST['paccofacile_meta_field_nonce'] ) && isset( $_POST['paccofacile_pay_order'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( filter_var( wp_unslash( $_POST['paccofacile_meta_field_nonce'] ), FILTER_SANITIZE_STRING ) ) ) {
			return;
		}

		$post_id = ( array_key_exists( 'order_id', $_POST ) && isset( $_POST['order_id'] ) ) ? absint( $_POST['order_id'] ) : 0;

		// maybe check some permissions here, depending on your app!
		if ( ! current_user_can( 'edit_shop_order', $post_id ) && ! current_user_can( 'edit_shop_orders', $post_id ) ) {
			exit;
		}

		$paccofacile_api = PFWC_Paccofacile_Api::get_instance();

		$data_fattura      = '';
		$address_id_select = '';

		if ( array_key_exists( 'paccofacile_billing_date', $_POST ) && array_key_exists( 'paccofacile_billing_address', $_POST ) ) {
			$data_fattura      = ( ! array_key_exists( 'paccofacile_billing_detail', $_POST ) || '1' === $_POST['paccofacile_billing_detail'] ) ? '' : filter_var( wp_unslash( $_POST['paccofacile_billing_date'] ), FILTER_SANITIZE_STRING );
			$address_id_select = ( ! array_key_exists( 'paccofacile_billing_detail', $_POST ) || '1' === $_POST['paccofacile_billing_detail'] ) ? '' : filter_var( wp_unslash( $_POST['paccofacile_billing_address'] ), FILTER_SANITIZE_STRING );
		}

		$response = array();

		if ( array_key_exists( 'shipment_id', $_POST ) && array_key_exists( 'paccofacile_billing_detail', $_POST ) ) {
			$payload = array(
				'shipments'         => array( filter_var( wp_unslash( $_POST['shipment_id'] ), FILTER_SANITIZE_NUMBER_INT ) ),
				'fattura'           => filter_var( wp_unslash( $_POST['paccofacile_billing_detail'] ), FILTER_SANITIZE_STRING ),
				'data_fattura'      => $data_fattura,
				'address_id_select' => $address_id_select,
				'payment_method'    => 'CREDIT',
			);

			$response = $paccofacile_api->post( 'shipment/buy', array(), $payload );
			$response = $response['data'];
		}

		if ( array_key_exists( 'order', $response ) && $response['order']['order_id'] ) {
			update_post_meta( $post_id, 'paccofacile_order_id', $response['order']['order_id'] );
			update_post_meta( $post_id, 'paccofacile_order_status', 'paid' );
		}

		header( 'Content-Type: application/json; charset=utf-8' );
		echo wp_json_encode( $response );

		exit; // important!
	}

	/**
	 * Ship With Ajax Handler
	 *
	 * @return void
	 */
	public function pfwc_ship_with_ajax_handler() {

		// Check if our nonce is set (and our cutom field)!
		if ( ! isset( $_POST['paccofacile_meta_field_nonce'] ) && isset( $_POST['paccofacile_ship_with'] ) ) {
			return;
		}

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( filter_var( wp_unslash( $_POST['paccofacile_meta_field_nonce'] ), FILTER_SANITIZE_STRING ) ) ) {
			return;
		}

		$post_id   = ( array_key_exists( 'order_id', $_POST ) && isset( $_POST['order_id'] ) ) ? absint( $_POST['order_id'] ) : 0;
		$order_obj = wc_get_order( $post_id );

		// maybe check some permissions here, depending on your app.
		if ( ! current_user_can( 'edit_shop_order', $post_id ) && ! current_user_can( 'edit_shop_orders', $post_id ) ) {
			exit;
		}

		pfwc_quote_and_save_by_woo_order( $order_obj, 'after_order' );

		exit; // important!
	}

	/**
	 * Add carrier Ajax Handler
	 *
	 * @return void
	 */
	public function pfwc_add_carrier_ajax_handler() {
		// maybe check some permissions here, depending on your app.

		if ( ! ( isset( $_POST['_wpnonce'] ) || wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'add_carrier_nonce' ) ) ) {
			return;
		}

		$carrier_name = ( array_key_exists( 'carrier_name', $_POST ) ) ? filter_var( wp_unslash( $_POST['carrier_name'] ), FILTER_SANITIZE_STRING ) : '';
		$service_name = ( array_key_exists( 'service_name', $_POST ) ) ? filter_var( wp_unslash( $_POST['service_name'] ), FILTER_SANITIZE_STRING ) : '';
		$box_type     = ( array_key_exists( 'box_type', $_POST ) ) ? filter_var( wp_unslash( $_POST['box_type'] ), FILTER_SANITIZE_STRING ) : '';

		$data = array(
			'post_title'  => $carrier_name . ' ' . $service_name . ' | ' . $box_type,
			'post_type'   => 'carrier',
			'post_status' => 'publish',
		);

		$new_post_id = wp_insert_post( $data );

		// send some information back to the javascipt handler.
		if ( ! is_wp_error( $new_post_id ) ) {
			if ( array_key_exists( 'service_id', $_POST ) ) {
				add_post_meta( $new_post_id, 'service_id', filter_var( wp_unslash( $_POST['service_id'] ), FILTER_SANITIZE_NUMBER_INT ) );
			}
			if ( array_key_exists( 'pickup_type', $_POST ) ) {
				add_post_meta( $new_post_id, 'pickup_type', filter_var( wp_unslash( $_POST['pickup_type'] ), FILTER_SANITIZE_NUMBER_INT ) );
			}
			if ( array_key_exists( 'carrier_id', $_POST ) ) {
				add_post_meta( $new_post_id, 'carrier_id', filter_var( wp_unslash( $_POST['carrier_id'] ), FILTER_SANITIZE_NUMBER_INT ) );
			}
			if ( array_key_exists( 'image_url', $_POST ) ) {
				add_post_meta( $new_post_id, 'image_url', filter_var( wp_unslash( $_POST['image_url'] ), FILTER_SANITIZE_URL ) );
			}
			if ( array_key_exists( 'carrier_ship_time', $_POST ) ) {
				add_post_meta( $new_post_id, 'carrier_ship_time', filter_var( wp_unslash( $_POST['carrier_ship_time'] ), FILTER_SANITIZE_STRING ) );
			}

			$response = array(
				'status'              => '200',
				'message'             => 'OK',
				'new_post_ID'         => $new_post_id,
				'nonce'               => wp_create_nonce( 'form-nonce' ),
				'delete_button_label' => esc_attr__( 'Delete', 'paccofacile-for-woocommerce' ),
			);
			foreach ( $_POST as $key => $value ) {
				$response[ $key ] = $value;
			}
		} else {
			$response = array(
				'status'  => '400',
				'message' => 'WP Error',
			);
		}

		// normally, the script expects a json respone.
		header( 'Content-Type: application/json; charset=utf-8' );
		echo wp_json_encode( $response );

		exit; // important!
	}

	/**
	 * Add box management Ajax Handler
	 *
	 * @return void
	 */
	public function pfwc_add_box_ajax_handler() {

		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'add_box_nonce' ) ) {
			return;
		}

		$plugin = new PFWC_Paccofacile();

		$action = ( array_key_exists( 'action', $_POST ) ) ? filter_var( wp_unslash( $_POST['action'] ), FILTER_SANITIZE_STRING ) : '';
		unset( $_POST['action'] );

		$box_type = ( array_key_exists( 'tipo', $_POST ) ) ? absint( $_POST['tipo'] ) : '';

		$params_package = array();

		$params_package['nome']       = ( array_key_exists( 'nome', $_POST ) ) ? filter_input( INPUT_POST, 'nome', FILTER_SANITIZE_STRING ) : '';
		$params_package['tipo']       = ( array_key_exists( 'tipo', $_POST ) ) ? filter_input( INPUT_POST, 'tipo', FILTER_SANITIZE_NUMBER_INT ) : '';
		$params_package['dim1']       = ( array_key_exists( 'dim1', $_POST ) ) ? filter_input( INPUT_POST, 'dim1', FILTER_SANITIZE_NUMBER_INT ) : '';
		$params_package['dim2']       = ( array_key_exists( 'dim2', $_POST ) ) ? filter_input( INPUT_POST, 'dim2', FILTER_SANITIZE_NUMBER_INT ) : '';
		$params_package['dim3']       = ( array_key_exists( 'dim3', $_POST ) ) ? filter_input( INPUT_POST, 'dim3', FILTER_SANITIZE_NUMBER_INT ) : '';
		$params_package['volume']     = ( array_key_exists( 'volume', $_POST ) ) ? filter_input( INPUT_POST, 'volume', FILTER_SANITIZE_NUMBER_INT ) : '';
		$params_package['peso_max']   = ( array_key_exists( 'peso_max', $_POST ) ) ? filter_input( INPUT_POST, 'peso_max', FILTER_SANITIZE_NUMBER_INT ) : '';
		$params_package['max_height'] = ( array_key_exists( 'max_height', $_POST ) ) ? filter_input( INPUT_POST, 'max_height', FILTER_SANITIZE_NUMBER_INT ) : '';

		if ( 'add_box' === $action ) {

			$imballo = $plugin->create_package( $params_package );

		} elseif ( 'edit_box' === $action ) {
			$imballo_id = ( array_key_exists( 'imballo_id', $_POST ) ) ? absint( $_POST['imballo_id'] ) : '';
			unset( $_POST['imballo_id'] );

			$imballo = $plugin->update_package( $imballo_id, $params_package );
		}

		if ( 1 === $box_type ) { // PACCO.

			$icon = '<svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M447.9 176c0-10.6-2.6-21-7.6-30.3l-49.1-91.9c-4.3-13-16.5-21.8-30.3-21.8H87.1c-13.8 0-26 8.8-30.4 21.9L7.6 145.8c-5 9.3-7.6 19.7-7.6 30.3C.1 236.6 0 448 0 448c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32 0 0-.1-211.4-.1-272zm-87-112l50.8 96H286.1l-12-96h86.8zM192 192h64v64h-64v-64zm49.9-128l12 96h-59.8l12-96h35.8zM87.1 64h86.8l-12 96H36.3l50.8-96zM32 448s.1-181.1.1-256H160v64c0 17.7 14.3 32 32 32h64c17.7 0 32-14.3 32-32v-64h127.9c0 74.9.1 256 .1 256H32z" class=""></path></svg>';

		} elseif ( 3 === $box_type ) { // PALLET.

			$icon = '<svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="currentColor" d="M144 288h352c8.8 0 16-7.2 16-16V16c0-8.8-7.2-16-16-16H144c-8.8 0-16 7.2-16 16v256c0 8.8 7.2 16 16 16zM288 32h64v76.2l-32-16-32 16V32zm-128 0h96v128l64-32 64 32V32h96v224H160V32zm472 320c4.4 0 8-3.6 8-8v-16c0-4.4-3.6-8-8-8H8c-4.4 0-8 3.6-8 8v16c0 4.4 3.6 8 8 8h56v128H8c-4.4 0-8 3.6-8 8v16c0 4.4 3.6 8 8 8h624c4.4 0 8-3.6 8-8v-16c0-4.4-3.6-8-8-8h-56V352h56zM160 480H96V352h64v128zm288 0H192V352h256v128zm96 0h-64V352h64v128z" class=""></path></svg>';

		} elseif ( 2 === $box_type ) { // BUSTA.

			$icon = '<svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M464 64H48C21.5 64 0 85.5 0 112v288c0 26.5 21.5 48 48 48h416c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48zM48 96h416c8.8 0 16 7.2 16 16v41.4c-21.9 18.5-53.2 44-150.6 121.3-16.9 13.4-50.2 45.7-73.4 45.3-23.2.4-56.6-31.9-73.4-45.3C85.2 197.4 53.9 171.9 32 153.4V112c0-8.8 7.2-16 16-16zm416 320H48c-8.8 0-16-7.2-16-16V195c22.8 18.7 58.8 47.6 130.7 104.7 20.5 16.4 56.7 52.5 93.3 52.3 36.4.3 72.3-35.5 93.3-52.3 71.9-57.1 107.9-86 130.7-104.7v205c0 8.8-7.2 16-16 16z" class=""></path></svg>';

		}

		$response = array(
			'status'                    => '200',
			'message'                   => 'OK',
			'new_post_ID'               => $imballo['imballo_id'],
			'nonce'                     => wp_create_nonce( 'form-nonce' ),
			'edit_button_label'         => esc_attr__( 'Edit', 'paccofacile-for-woocommerce' ),
			'delete_button_label'       => esc_attr__( 'Delete', 'paccofacile-for-woocommerce' ),
			'icon'                      => $icon,
			'box_name'                  => $imballo['nome'],
			'box_type'                  => $imballo['tipo'],
			'dim1'                      => $imballo['dim1'],
			'dim2'                      => $imballo['dim2'],
			'dim3'                      => $imballo['dim3'],
			'volume'                    => $imballo['volume'],
			'action'                    => $action,
			'confirm_edit_button_label' => esc_attr__( 'Edit package', 'paccofacile-for-woocommerce' ),
		);

		$response['max_weight'] = ( isset( $_POST['peso_max'] ) ) ? filter_var( wp_unslash( $_POST['peso_max'] ), FILTER_SANITIZE_STRING ) : 0;
		$response['max_height'] = ( isset( $_POST['altezza_max'] ) ) ? filter_var( wp_unslash( $_POST['altezza_max'] ), FILTER_SANITIZE_STRING ) : null;

		// send some information back to the javascipt handler.
		if ( ! $imballo ) {
			$response = array(
				'status'  => '400',
				'message' => 'WP Error',
			);
		}

		// normally, the script expects a json respone.
		header( 'Content-Type: application/json; charset=utf-8' );
		echo wp_json_encode( $response );

		exit; // important!
	}

	/**
	 * Delete carrier Ajax Handler
	 *
	 * @return void
	 */
	public function pfwc_delete_carrier_ajax_handler() {

		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'delete_carrier_nonce' ) ) {
			return;
		}

		$post_id = ( array_key_exists( 'post_id', $_POST ) && isset( $_POST['post_id'] ) ) ? absint( $_POST['post_id'] ) : 0;

		$return = wp_delete_post( $post_id, true );

		// send some information back to the javascipt handler.
		if ( $return ) {
			$response = array(
				'status'  => '200',
				'message' => 'OK',
			);
		} else {
			$response = array(
				'status'  => '400',
				'message' => 'error',
			);
		}

		// normally, the script expects a json respone.
		header( 'Content-Type: application/json; charset=utf-8' );
		echo wp_json_encode( $response );

		exit; // important!
	}

	/**
	 * Delete box Ajax Handler
	 *
	 * @return void
	 */
	public function pfwc_delete_box_ajax_handler() {

		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'delete_box_nonce' ) ) {
			return;
		}

		$plugin = new PFWC_Paccofacile();

		$imballo_id = ( array_key_exists( 'imballo_id', $_POST ) && isset( $_POST['imballo_id'] ) ) ? absint( $_POST['imballo_id'] ) : 0;

		$return = $plugin->delete_package( $imballo_id );

		// send some information back to the javascipt handler.
		if ( $return ) {
			$response = array(
				'status'  => '200',
				'message' => 'OK',
			);
		} else {
			$response = array(
				'status'  => '400',
				'message' => 'error',
			);
		}

		// normally, the script expects a json respone.
		header( 'Content-Type: application/json; charset=utf-8' );
		echo wp_json_encode( $response );

		exit; // important!
	}

	/**
	 * Add shipping customes Ajax Handler
	 *
	 * @return void
	 */
	public function pfwc_add_shipping_customes_ajax_handler() {

		$paccofacile_api = PFWC_Paccofacile_Api::get_instance();

		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'add_shipping_customes' ) ) {
			return;
		}

		if ( ! isset( $_POST['total_goods_value'] ) ) {
			$_POST['total_goods_value'] = 0;
		}

		$items_amount_sum = 0;
		$items_weight_sum = 0;

		for ( $i = 1; $i < 5; $i++ ) {
			if ( ! isset( $_POST[ 'customs_' . $i . '_quantity' ] ) ) {
				$_POST[ 'customs' . $i . '_quantity' ] = 0;
			}
			if ( ! isset( $_POST[ 'customs_' . $i . '_weight' ] ) ) {
				$_POST[ 'customs' . $i . '_weight' ] = 0;
			}
			if ( ! isset( $_POST[ 'customs_' . $i . '_amount' ] ) ) {
				$_POST[ 'customs' . $i . '_amount' ] = 0;
			}

			$items_weight_sum += filter_var( wp_unslash( $_POST[ 'customs_' . $i . '_weight' ] ), FILTER_SANITIZE_NUMBER_INT );
			$items_amount_sum += filter_var( wp_unslash( $_POST[ 'customs_' . $i . '_amount' ] ), FILTER_SANITIZE_NUMBER_INT );
		}

		// VALIDAZIONE!
		if ( $_POST['total_goods_value'] !== $items_amount_sum || ! isset( $_POST['order_weight'] ) || $_POST['order_weight'] !== $items_weight_sum ) {
			$response = array(
				'status' => '400',
			);
			if ( $_POST['total_goods_value'] !== $items_amount_sum ) {
				$response['message'][] = esc_attr__( 'The total goods amount must match the amounts of the articles to ship.', 'paccofacile-for-woocommerce' );
			}
			if ( $_POST['order_weight'] !== $items_weight_sum ) {
				/* translators: %s is replaced with the amount in Kg */
				$response['message'][] = esc_attr( sprintf( __( 'The sum of the articles weight must match the weight of the order. (%s Kg)', 'paccofacile-for-woocommerce' ), filter_var( wp_unslash( $_POST['order_weight'] ), FILTER_SANITIZE_STRING ) ) );
			}
		} else {
			// SALVO LE INFO DOGANALI NELL'ORDINE.

			if ( isset( $_POST['woo_order_id'] ) ) {
				$payload_ordine = get_post_meta( filter_var( wp_unslash( $_POST['woo_order_id'] ), FILTER_SANITIZE_STRING ), 'paccofacile_order_payload', true );
			}

			$articles = array();

			for ( $i = 1; $i < 5; $i++ ) {
				if ( 0 !== $_POST[ 'customs_' . $i . '_quantity' ] && 0 !== $_POST[ 'customs_' . $i . '_weight' ] && 0 !== $_POST[ 'customs_' . $i . '_amount' ] ) {

					$amount      = ( array_key_exists( 'customs_' . $i . '_amount', $_POST ) ) ? filter_var( wp_unslash( $_POST[ 'customs_' . $i . '_amount' ] ), FILTER_SANITIZE_NUMBER_INT ) : 0;
					$quantity    = ( array_key_exists( 'customs_' . $i . '_quantity', $_POST ) ) ? filter_var( wp_unslash( $_POST[ 'customs_' . $i . '_quantity' ] ), FILTER_SANITIZE_NUMBER_INT ) : 0;
					$weight      = ( array_key_exists( 'customs_' . $i . '_weight', $_POST ) ) ? filter_var( wp_unslash( $_POST[ 'customs_' . $i . '_weight' ] ), FILTER_SANITIZE_NUMBER_INT ) : 0;
					$description = ( array_key_exists( 'customs_' . $i . '_description', $_POST ) ) ? filter_var( wp_unslash( $_POST[ 'customs_' . $i . '_description' ] ), FILTER_SANITIZE_STRING ) : '';

					$article = array(
						'amount'                        => array(
							'value'    => $amount,
							'currency' => 'EUR',
						),
						'quantity'                      => $quantity,
						'weight'                        => $weight,
						'description'                   => $description,
						'iso_code_country_manufactured' => 'IT',
					);

					$articles[] = $article;
				}
			}

			if ( array_key_exists( 'total_goods_value', $_POST ) ) {
				$total_goods_value = filter_var( wp_unslash( $_POST['total_goods_value'] ), FILTER_SANITIZE_STRING );
			} else {
				$total_goods_value = 0;
			}
			$customs = array(
				'amount'   => array(
					'value'    => $total_goods_value,
					'currency' => 'EUR',
				),
				'articles' => $articles,
			);

			$payload_ordine['customs'] = $customs;

			if ( array_key_exists( 'shipment_id', $_POST ) && isset( $_POST['shipment_id'] ) ) {
				$response_ordine = $paccofacile_api->post( 'shipment/save', array(), $payload_ordine );
				update_post_meta( filter_var( wp_unslash( $_POST['woo_order_id'] ), FILTER_SANITIZE_NUMBER_INT ), 'paccofacile_order_payload', $payload_ordine );
			} else {
				$shipment_draft_id = get_post_meta( filter_var( wp_unslash( $_POST['woo_order_id'] ), FILTER_SANITIZE_NUMBER_INT ), 'shipment_draft_id', true );

				$payload_ordine['shipment_draft_id'] = $shipment_draft_id;

				$response_ordine = $paccofacile_api->post( 'shipment/save', array(), $payload_ordine );
			}

			if ( 200 === $response_ordine['code'] ) {
				update_post_meta( filter_var( wp_unslash( $_POST['woo_order_id'] ), FILTER_SANITIZE_NUMBER_INT ), 'customes', $customs );

				delete_post_meta( filter_var( wp_unslash( $_POST['woo_order_id'] ), FILTER_SANITIZE_NUMBER_INT ), 'shipment_draft_id' );

				$response = array(
					'status'  => '200',
					'message' => 'OK',
				);
			} else {

				$response = array(
					'status'  => '400',
					'message' => array( esc_attr__( 'Error while saving customs info. Please check the fields and retry.', 'paccofacile-for-woocommerce' ) ),
				);
			}
		}

		// normally, the script expects a json response.
		header( 'Content-Type: application/json; charset=utf-8' );
		$response['order_weight'] = filter_var( wp_unslash( $_POST['order_weight'] ), FILTER_SANITIZE_STRING );
		echo wp_json_encode( $response );

		exit; // important!
	}

	/**
	 * Search locality Ajax Handler
	 *
	 * @return void
	 */
	public function pfwc_search_locality_ajax_handler() {

		if ( ! ( isset( $_POST['woocommerce_meta_nonce'] ) || wp_verify_nonce( sanitize_key( $_POST['woocommerce_meta_nonce'] ), 'woocommerce_save_data' ) ) ) {
			return;
		}

		$iso_code = ( array_key_exists( 'iso_code', $_POST ) && isset( $_POST['iso_code'] ) ) ? filter_var( wp_unslash( $_POST['iso_code'] ), FILTER_SANITIZE_STRING ) : '';
		$city     = ( array_key_exists( 'city', $_POST ) && isset( $_POST['city'] ) ) ? filter_var( wp_unslash( $_POST['city'] ), FILTER_SANITIZE_STRING ) : '';

		$return = pfwc_search_locality( $iso_code, $city );

		// send some information back to the javascipt handler.
		if ( $return ) {
			$response = array(
				'status'  => '200',
				'message' => 'OK',
				'data'    => $return,
			);
		} else {
			$response = array(
				'status'  => '400',
				'message' => 'error',
			);
		}

		// normally, the script expects a json respone.
		header( 'Content-Type: application/json; charset=utf-8' );
		echo wp_json_encode( $response );

		exit; // important!
	}

	/**
	 * Add store locker Ajax Handler
	 *
	 * @return void
	 */
	public function pfwc_add_store_locker_ajax_handler() {

		if ( ! ( isset( $_POST['_wpnonce'] ) || wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'add_store_locker_nonce' ) ) ) {
			return;
		}

		$carrier_id = ( array_key_exists( 'carrier_id', $_POST ) && isset( $_POST['carrier_id'] ) ) ? filter_var( wp_unslash( $_POST['carrier_id'] ), FILTER_SANITIZE_NUMBER_INT ) : '';
		$locker_id  = ( array_key_exists( 'shipping_locker', $_POST ) && isset( $_POST['shipping_locker'] ) ) ? filter_var( wp_unslash( $_POST['shipping_locker'] ), FILTER_SANITIZE_STRING ) : '';

		$return = pfwc_add_store_locker( $carrier_id, $locker_id );

		// send some information back to the javascipt handler.
		if ( $return ) {
			$response = array(
				'status'  => '200',
				'message' => 'OK',
				'data'    => $return,
			);
		} else {
			$response = array(
				'status'  => '400',
				'message' => 'error',
			);
		}

		// normally, the script expects a json response.
		header( 'Content-Type: application/json; charset=utf-8' );
		echo wp_json_encode( $response );

		exit; // important!
	}

	/**
	 * Get lockers Ajax Handler
	 *
	 * @return void
	 */
	public function pfwc_get_lockers_ajax_handler() {

		if ( ! ( isset( $_POST['_wpnonce'] ) || wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'get_store_locker_nonce' ) ) ) {
			return;
		}

		$postcode = ( isset( $_POST['postcode'] ) ) ? filter_var( wp_unslash( $_POST['postcode'] ), FILTER_SANITIZE_STRING ) : '';
		$city     = ( isset( $_POST['city'] ) ) ? filter_var( wp_unslash( $_POST['city'] ), FILTER_SANITIZE_STRING ) : '';

		$return = pfwc_get_lockers( $postcode, $city );

		// send some information back to the javascipt handler.
		if ( $return ) {
			$response = array(
				'status'  => '200',
				'message' => 'OK',
				'data'    => $return,
			);
		} else {
			$response = array(
				'status'  => '400',
				'message' => 'error',
			);
		}

		// normally, the script expects a json response.
		header( 'Content-Type: application/json; charset=utf-8' );
		echo wp_json_encode( $response );

		exit; // important!
	}
}

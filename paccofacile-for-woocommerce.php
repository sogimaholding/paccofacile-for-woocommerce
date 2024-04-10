<?php
/**
 * Paccofacile.it for Woocommerce main file
 *
 * @link              #
 * @since             1.0.0
 * @package           Paccofacile
 *
 * @wordpress-plugin
 * Plugin Name:       Paccofacile.it for WooCommerce
 * Plugin URI:        https://www.paccofacile.it/integrazioni/woocommerce
 * Description:       Connect in few clicks your Paccofacile.it PRO's account and start saving money and time with our automatic shipping manager software.
 * Version:           1.1.3
 * Author:            Sogima Holding srl
 * Author URI:        https://www.paccofacile.it
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       paccofacile-for-woocommerce
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
	// Exit if accessed directly.
}

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 */
define( 'PFWC_PACCOFACILE_VERSION', '1.1.3' );
define( 'PFWC_PACCOFACILE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'PFWC_PACCOFACILE_BASENAME_FILE', plugin_basename( __FILE__ ) );
define( 'PFWC_PACCOFACILE_PLUGIN_URL', plugins_url( '', __FILE__ ) );

if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . '/wp-admin/includes/plugin.php';
}

/**
 * Check for the existence of WooCommerce and any other requirements.
 */
function pfwc_check_requirements() {
	if ( class_exists( 'WooCommerce' ) || is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
		return true;
	} else {
		add_action( 'admin_notices', 'pfwc_missing_wc_notice' );
		return false;
	}
}

/**
 * Custom function to declare compatibility with cart_checkout_blocks feature.
 */
function pfwc_declare_cart_checkout_blocks_compatibility() {
	// Check if the required class exists.
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		// Declare compatibility for 'cart_checkout_blocks'.
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, false );
	}
}
// Hook the custom function to the 'before_woocommerce_init' action.
add_action( 'before_woocommerce_init', 'pfwc_declare_cart_checkout_blocks_compatibility' );


/**
 * Display a message advising WooCommerce is required
 */
function pfwc_missing_wc_notice() {
	$class   = 'notice notice-error';
	$message = __( 'Paccofacile requires WooCommerce to be installed and active.', 'paccofacile-for-woocommerce' );

	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pfwc-paccofacile-activator.php
 */
function pfwc_activate_paccofacile() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pfwc-paccofacile-activator.php';
	PFWC_Paccofacile_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-paccofacile-deactivator.php
 */
function pfwc_deactivate_paccofacile() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pfwc-paccofacile-deactivator.php';
	PFWC_Paccofacile_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'pfwc_activate_paccofacile' );
register_deactivation_hook( __FILE__, 'pfwc_deactivate_paccofacile' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-pfwc-paccofacile.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function pfwc_run_paccofacile() {
	if ( pfwc_check_requirements() ) {
		$plugin = new PFWC_Paccofacile();
		$plugin->run();
	}
}
pfwc_run_paccofacile();

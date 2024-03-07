<?php
/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Paccofacile
 * @subpackage Paccofacile/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Paccofacile
 * @subpackage Paccofacile/includes
 * @author     Francesco Barberini <supporto.tecnico@paccofacile.it>
 */
class Paccofacile_I18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'paccofacile-for-woocommerce',
			false,
			dirname( PACCOFACILE_BASENAME_FILE ) . '/languages/'
		);
	}
}

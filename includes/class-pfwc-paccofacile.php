<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Paccofacile
 * @subpackage Paccofacile/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
	// Exit if accessed directly.
}

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Paccofacile
 * @subpackage Paccofacile/includes
 * @author     Francesco Barberini <supporto.tecnico@paccofacile.it>
 */
class PFWC_Paccofacile {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      PFWC_Paccofacile_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'PFWC_PACCOFACILE_VERSION' ) ) {
			$this->version = PFWC_PACCOFACILE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'paccofacile';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();

		$this->define_public_hooks();
		$this->add_settings_link();

		add_action( 'init', array( $this, 'register_courier_post_type' ) );
	}

	/**
	 * Register carrier post type
	 *
	 * @return void
	 */
	public function register_courier_post_type() {
		$labels = array(
			'name'                  => _x( 'Carriers', 'Post type general name', 'paccofacile-for-woocommerce' ),
			'singular_name'         => _x( 'Carrier', 'Post type singular name', 'paccofacile-for-woocommerce' ),
			'menu_name'             => _x( 'Carriers', 'Admin Menu text', 'paccofacile-for-woocommerce' ),
			'name_admin_bar'        => _x( 'Carrier', 'Add New on Toolbar', 'paccofacile-for-woocommerce' ),
			'add_new'               => __( 'Add New', 'paccofacile-for-woocommerce' ),
			'add_new_item'          => __( 'Add New Carrier', 'paccofacile-for-woocommerce' ),
			'new_item'              => __( 'New Carrier', 'paccofacile-for-woocommerce' ),
			'edit_item'             => __( 'Edit Carrier', 'paccofacile-for-woocommerce' ),
			'view_item'             => __( 'View Carrier', 'paccofacile-for-woocommerce' ),
			'all_items'             => __( 'All Carriers', 'paccofacile-for-woocommerce' ),
			'search_items'          => __( 'Search Carriers', 'paccofacile-for-woocommerce' ),
			'parent_item_colon'     => __( 'Parent Carrier:', 'paccofacile-for-woocommerce' ),
			'not_found'             => __( 'No Carriers found.', 'paccofacile-for-woocommerce' ),
			'not_found_in_trash'    => __( 'No Carriers found in Trash.', 'paccofacile-for-woocommerce' ),
			'featured_image'        => _x( 'Carrier Cover Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'paccofacile-for-woocommerce' ),
			'set_featured_image'    => _x( 'Set cover image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'paccofacile-for-woocommerce' ),
			'remove_featured_image' => _x( 'Remove cover image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'paccofacile-for-woocommerce' ),
			'use_featured_image'    => _x( 'Use as cover image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'paccofacile-for-woocommerce' ),
			'archives'              => _x( 'Carrier archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'paccofacile-for-woocommerce' ),
			'insert_into_item'      => _x( 'Insert into Carrier', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'paccofacile-for-woocommerce' ),
			'uploaded_to_this_item' => _x( 'Uploaded to this Carrier', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'paccofacile-for-woocommerce' ),
			'filter_items_list'     => _x( 'Filter Carriers list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'paccofacile-for-woocommerce' ),
			'items_list_navigation' => _x( 'Carriers list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'paccofacile-for-woocommerce' ),
			'items_list'            => _x( 'Carriers list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'paccofacile-for-woocommerce' ),
		);

		$args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => false,
			'show_in_rest'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'carrier' ),
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'thumbnail', 'excerpt', 'custom-fields' ),
		);

		register_post_type( 'carrier', $args );
	}


	/**
	 * Register filter for links on the plugin screen.
	 */
	public function add_settings_link() {
		add_filter( 'plugin_action_links_' . PFWC_PACCOFACILE_BASENAME_FILE, array( $this, 'create_configuration_link' ), 10, 5 );
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param array $links Plugin Action links.
	 * @param array $plugin_file Plugin file.
	 *
	 * @return array
	 */
	public function create_configuration_link( array $links, $plugin_file ) {
		$action_links = array(
			'settings' => '<a href="' . esc_attr( get_admin_url( null, 'admin.php?page=' . $this->plugin_name ) ) . '">' . esc_html__( 'Settings', 'paccofacile-for-woocommerce' ) . '</a>',
		);

		return array_merge( $action_links, $links );
	}


	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - PFWC_Paccofacile_Loader. Orchestrates the hooks of the plugin.
	 * - PFWC_Paccofacile_I18n. Defines internationalization functionality.
	 * - PFWC_Paccofacile_Admin. Defines all hooks for the admin area.
	 * - PFWC_Paccofacile_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-pfwc-paccofacile-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-pfwc-paccofacile-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-pfwc-paccofacile-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'public/class-pfwc-paccofacile-public.php';

		/**
		 * The class responsible for defining all actions that occur for integrate with WooCommerce
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/paccofacile-woocommerce.php';

		$this->loader = new PFWC_Paccofacile_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the PFWC_Paccofacile_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new PFWC_Paccofacile_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new PFWC_Paccofacile_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'pfwc_enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'pfwc_enqueue_scripts' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'pfwc_register_settings' );
		$this->loader->add_action( 'wp_ajax_paccofacile_pay_order', $plugin_admin, 'pfwc_pay_order_ajax_handler' );
		$this->loader->add_action( 'wp_ajax_paccofacile_ship_with', $plugin_admin, 'pfwc_ship_with_ajax_handler' );
		$this->loader->add_action( 'wp_ajax_add_carrier', $plugin_admin, 'pfwc_add_carrier_ajax_handler' );
		$this->loader->add_action( 'wp_ajax_delete_carrier', $plugin_admin, 'pfwc_delete_carrier_ajax_handler' );
		$this->loader->add_action( 'wp_ajax_add_box', $plugin_admin, 'pfwc_add_box_ajax_handler' );
		$this->loader->add_action( 'wp_ajax_edit_box', $plugin_admin, 'pfwc_add_box_ajax_handler' );
		$this->loader->add_action( 'wp_ajax_delete_box', $plugin_admin, 'pfwc_delete_box_ajax_handler' );
		$this->loader->add_action( 'wp_ajax_add_shipping_customes', $plugin_admin, 'pfwc_add_shipping_customes_ajax_handler' );
		$this->loader->add_action( 'wp_ajax_search_locality', $plugin_admin, 'pfwc_search_locality_ajax_handler' );
		$this->loader->add_action( 'wp_ajax_get_lockers', $plugin_admin, 'pfwc_get_lockers_ajax_handler' );
		$this->loader->add_action( 'wp_ajax_add_store_locker', $plugin_admin, 'pfwc_add_store_locker_ajax_handler' );
		$plugin_admin->load_admin_menu();
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new PFWC_Paccofacile_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'pfwc_enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'pfwc_enqueue_scripts' );
		$this->loader->add_action( 'init', $this, 'pfwc_register_session' );
		$this->loader->add_filter( 'body_class', $plugin_public, 'pfwc_body_classes' );
		$this->loader->add_action( 'wp_ajax_get_lockers', $plugin_public, 'pfwc_get_lockers_ajax_handler' );
		$this->loader->add_action( 'wp_ajax_get_city_coordinates', $plugin_public, 'get_city_coordinates_ajax_handler' );
		$this->loader->add_action( 'wp_ajax_locker_id_session', $plugin_public, 'locker_id_session_ajax_handler' );
	}

	/**
	 * Register session
	 *
	 * @return void
	 */
	public function pfwc_register_session() {
		if ( ! session_id() ) {
			session_start( [ 'read_and_close' => true ] );
		}
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    PFWC_Paccofacile_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

	/**
	 * Get shipping boxes
	 *
	 * @return array
	 */
	public function get_shipping_boxes() {

		$paccofacile_api = PFWC_Paccofacile_Api::get_instance();

		$shipping_boxes = $paccofacile_api->get( 'packaging/list', array(), array() );

		if ( $shipping_boxes['data'] && array_key_exists( 'items', $shipping_boxes['data'] ) ) {
			return $shipping_boxes['data']['items'];
		} else {
			return array();
		}
	}

	/**
	 * Get package
	 *
	 * @param [type] $id Package id.
	 * @return string
	 */
	public function get_package( $id ) {
		$paccofacile_api = PFWC_Paccofacile_Api::get_instance();

		$package = $paccofacile_api->get( 'packaging/' . $id, array(), array() );

		return $package['data'];
	}

	/**
	 * Get package types
	 *
	 * @return string
	 */
	public function get_package_types() {
		$paccofacile_api = PFWC_Paccofacile_Api::get_instance();

		$types = $paccofacile_api->get( 'packaging/list_types', array(), array() );

		return $types['data'];
	}

	/**
	 * Get package variations
	 *
	 * @param string $type Type id.
	 * @return string
	 */
	public function get_package_type_variation( $type ) {
		$paccofacile_api = PFWC_Paccofacile_Api::get_instance();

		$variations = $paccofacile_api->get( 'packaging/list_variation/' . $type, array(), array() );

		return $variations['data'];
	}

	/**
	 * Delete package
	 *
	 * @param [type] $id Package id.
	 * @return string
	 */
	public function delete_package( $id ) {
		$paccofacile_api = PFWC_Paccofacile_Api::get_instance();

		$response = $paccofacile_api->delete( 'packaging/' . $id, array(), array() );

		return $response['data'];
	}

	/**
	 * Create Package
	 *
	 * @param [type] $payload Payload.
	 * @return string
	 */
	public function create_package( $payload ) {
		$paccofacile_api = PFWC_Paccofacile_Api::get_instance();

		$response = $paccofacile_api->post( 'packaging', array(), $payload );

		return $response['data'];
	}

	/**
	 * Update package
	 *
	 * @param [type] $id Package id.
	 * @param [type] $payload Payload.
	 * @return string
	 */
	public function update_package( $id, $payload ) {
		$paccofacile_api = PFWC_Paccofacile_Api::get_instance();

		$response = $paccofacile_api->put( 'packaging/' . $id, array(), $payload );

		return $response['data'];
	}
}

<?php
/**
 * Tracking info email class
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
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'PFWC_Tracking_Info_Order_Email', false ) ) :

	/**
	 * A custom Expedited Order WooCommerce Email class
	 *
	 * @since 0.1
	 * @extends \WC_Email
	 */
	class PFWC_Tracking_Info_Order_Email extends WC_Email {

		/**
		 * Tracking info
		 *
		 * @var [type]
		 */
		public $tracking_info;

		/**
		 * Set email defaults
		 *
		 * @since 0.1
		 */
		public function __construct() {

			// set ID, this simply needs to be a unique name.
			$this->id             = 'pfwc_tracking_info_order';
			$this->customer_email = true;

			// this is the title in WooCommerce Email settings.
			$this->title = __( 'Tracking Info', 'paccofacile-for-woocommerce' );

			// this is the description in WooCommerce email settings.
			$this->description = __( 'Tracking Info Notification emails are sent when tracking checkpoints are available for the client\'s order', 'paccofacile-for-woocommerce' );

			// these are the default heading and subject lines that can be overridden using the settings.
			$this->heading = __( 'Tracking Info', 'paccofacile-for-woocommerce' );
			$this->subject = __( 'Tracking Info', 'paccofacile-for-woocommerce' );

			// these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar.
			$this->template_html  = 'emails/order-tracking-info.php';
			$this->template_plain = 'emails/plain/order-tracking-info.php';

			// Trigger on new paid orders.
			add_action( 'paccofacile_order_tracking_info_sent', array( $this, 'trigger' ), 10, 2 );

			// Call parent constructor to load any other defaults not explicity defined here.
			parent::__construct();

			// this sets the recipient to the settings defined below in init_form_fields().
			$this->recipient = $this->get_option( 'recipient' );

			// if none was entered, just use the WP admin email as a fallback.
			if ( ! $this->recipient ) {
				$this->recipient = get_option( 'admin_email' );
			}

			$this->template_base = PFWC_PACCOFACILE_PATH . '/';
		}

		/**
		 * Determine if the email should actually be sent and setup email merge variables
		 *
		 * @since 0.1
		 * @param int   $order_id Order id.
		 * @param mixed $response_tracking Tracking response.
		 */
		public function trigger( $order_id, $response_tracking ) {
			$this->setup_locale();

			// bail if no order ID is present.
			if ( ! $order_id ) {
				return;
			}

			// setup order object.
			$this->object = wc_get_order( $order_id );

			$this->tracking_info = $response_tracking;

			// replace variables in the subject/headings.
			$this->find[]    = '{order_date}';
			$this->replace[] = date_i18n( wc_date_format(), strtotime( $this->object->get_date_created() ) );

			$this->find[]    = '{order_number}';
			$this->replace[] = $this->object->get_order_number();

			if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
				return;
			}

			// woohoo, send the email!
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

			$this->restore_locale();
		}

		/**
		 * Get content html.
		 *
		 * @return string
		 */
		public function get_content_html() {
			return wc_get_template_html(
				$this->template_html,
				array(
					'order'              => $this->object,
					'email_heading'      => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'tracking_info'      => $this->tracking_info,
					'sent_to_admin'      => false,
					'plain_text'         => false,
					'email'              => $this,
				),
				'',
				$this->template_base
			);
		}

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			return wc_get_template_html(
				$this->template_plain,
				array(
					'order'              => $this->object,
					'email_heading'      => $this->get_heading(),
					'additional_content' => $this->get_additional_content(),
					'tracking_info'      => $this->tracking_info,
					'sent_to_admin'      => false,
					'plain_text'         => true,
					'email'              => $this,
				),
				'',
				$this->template_base
			);
		}
	} // End \PFWC_Tracking_Info_Order_Email class.

endif;

<?php
/**
 * Customer Order tracking info added email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/order-tracking-info.php.
 *
 * HOWEVER, on occasion Paccofacile will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.

 * @package Paccofacile
 * @subpackage Paccofacile/emails/plain
 */

defined( 'ABSPATH' ) || exit;

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<?php /* translators: %s: Customer first name */ ?>
<p><?php printf( esc_html__( 'Hi %s,', 'paccofacile-for-woocommerce' ), esc_html( $order->get_billing_first_name() ) ); ?></p>
<p><?php esc_html_e( 'The following note has been added to your order:', 'paccofacile-for-woocommerce' ); ?></p>

<?php
/*
<pre><?php print_r($tracking_info); ?></pre>

<blockquote><?php echo wpautop( wptexturize( make_clickable( $tracking_info ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></blockquote>
*/

if ( ! empty( $tracking_info['elenco']['checkpoints'] ) ) :
	$checkpoints      = $tracking_info['elenco']['checkpoints'];
	$options_tracking = get_option( 'paccofacile_settings' )['tracking_to_show'];
	$text_align       = is_rtl() ? 'right' : 'left';
	?>

	<h2><?php echo esc_html( apply_filters( 'paccofacile_order_tracking_title', __( 'Order tracking', 'paccofacile-for-woocommerce' ) ) ); ?></h2>

	<div style="margin-bottom: 40px;">
		<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
			<tbody>
				<?php $count_checkpoints = count( $checkpoints ); ?>
				<?php for ( $i = 0; $i < $count_checkpoints; $i++ ) : ?>
					<?php if ( array_key_exists( $checkpoints[ $i ]['tag'], $options_tracking ) && 1 === $options_tracking[ $checkpoints[ $i ]['tag'] ] ) : ?>
						<tr>
							<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
								<?php echo '<b>' . esc_html( $checkpoints[ $i ]['checkpoint_time'] ) . '</b><br />- ' . esc_html( $checkpoints[ $i ]['message'] ) . ' [' . esc_html( $checkpoints[ $i ]['city'] ) . ']'; ?>
							</td>
						</tr>
					<?php endif; ?>
				<?php endfor; ?>
			</tbody>
		</table>
	</div>

<?php endif; ?>

<p><?php esc_html_e( 'As a reminder, here are your order details:', 'paccofacile-for-woocommerce' ); ?></p>

<?php
/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );

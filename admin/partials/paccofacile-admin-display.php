<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       #
 * @since      1.0.0
 *
 * @package    Paccofacile
 * @subpackage Paccofacile/admin/partials
 */

?>

<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
	// Exit if accessed directly.
}


require_once PFWC_PACCOFACILE_PATH . '/includes/class-paccofacile-api.php';
$paccofacile_api = PFWC_Paccofacile_Api::get_instance();

?>

<div class="wrap paccofacile_config">
	<h2><?php esc_html_e( 'Paccofacile', 'paccofacile-for-woocommerce' ); ?></h2>
	<?php settings_errors(); ?>

	<?php
	if ( isset( $_GET['nonce'] ) && ! wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'paccofacile_settings_nonce' ) ) {
		return;
	}
	?>

	<?php
	$active_tab = '';
	if ( isset( $_GET['tab'] ) ) {
		$active_tab = filter_var( wp_unslash( $_GET['tab'] ), FILTER_SANITIZE_STRING );
	} else {
		$active_tab = 'api_settings';
	}
	?>

	<h2 class="nav-tab-wrapper">
		<a href="?page=paccofacile&tab=api_settings&nonce=<?php echo esc_attr( wp_create_nonce( 'paccofacile_settings_nonce' ) ); ?>" class="nav-tab <?php echo 'api_settings' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'API Settings', 'paccofacile-for-woocommerce' ); ?></a>
		<?php if ( get_option( 'paccofacile_api_valid' ) == 1 ) : ?>
			<a href="?page=paccofacile&tab=shipping_services&nonce=<?php echo esc_attr( wp_create_nonce( 'paccofacile_settings_nonce' ) ); ?>" class="nav-tab <?php echo 'shipping_services' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Shipping services', 'paccofacile-for-woocommerce' ); ?></a>
			<a href="?page=paccofacile&tab=manage_boxes&nonce=<?php echo esc_attr( wp_create_nonce( 'paccofacile_settings_nonce' ) ); ?>" class="nav-tab <?php echo 'manage_boxes' === $active_tab ? 'nav-tab-active' : ''; ?>"><?php esc_html_e( 'Packages', 'paccofacile-for-woocommerce' ); ?></a>
			<?php /* <a href="?page=paccofacile&tab=refund_method" class="nav-tab <?php echo $active_tab == 'refund_method' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Refund method', 'paccofacile-for-woocommerce' ); ?></a> */ ?>
		<?php endif; ?>
	</h2>

	<?php
	if ( 'api_settings' === $active_tab ) {
		?>
		
		<form action="options.php" method="post">

			<?php
			settings_fields( 'paccofacile_settings' );
			do_settings_sections( 'paccofacile' );
			do_settings_sections( 'paccofacile_tracking' );
			submit_button();
			?>

		</form>
		
	<?php } elseif ( 'shipping_services' === $active_tab ) { ?>

		<?php if ( 1 === get_option( 'paccofacile_api_valid' ) || '1' === get_option( 'paccofacile_api_valid' ) ) : ?>

			<?php add_thickbox(); ?>

			<div class="wrap">
				<a href="#TB_inline?width=600&height=550&inlineId=add_courier_modal" class="button button-primary thickbox"><?php esc_html_e( 'Add service', 'paccofacile-for-woocommerce' ); ?></a>

				<div id="add_courier_modal" style="display:none;">

					<?php

					$args_carrier = array(
						'post_type'   => 'carrier',
						'post_status' => 'publish',
					);

					$carriers          = new WP_Query( $args_carrier );
					$response_corrieri = $paccofacile_api->get( 'carriers' );

					if ( array_key_exists( 'data', $response_corrieri ) ) {
						$corrieri = $response_corrieri['data'];
					} else {
						$corrieri = array();
					}

					$carriers_ids = wp_list_pluck( $carriers->posts, 'ID' );

					$carriers_service_ids = array();
					$count_carrier_ids    = count( $carriers_ids );

					for ( $i = 0; $i < $count_carrier_ids; $i++ ) {
						$carriers_service_ids[] = get_post_meta( $carriers_ids[ $i ], 'service_id', true );
					}

					if ( ! empty( $corrieri ) ) :
						$count_corrieri = count( $corrieri );
						?>
						<div class="lista_corrieri api">
							<?php for ( $i = 0; $i < $count_corrieri; $i++ ) : ?>
								<div class="corriere 
									<?php
									if ( in_array( $corrieri[ $i ]['service_id'], $carriers_service_ids, true ) ) {
										echo esc_attr( 'spento' );
									}
									?>
									serviceid_<?php echo esc_attr( $corrieri[ $i ]['service_id'] ); ?>">
									<div class="inner">
										<?php if ( $corrieri[ $i ]['image_url'] ) : ?>
											<div class="image">
												<img src="<?php echo esc_attr( $corrieri[ $i ]['image_url'] ); ?>" alt="<?php echo esc_attr( $corrieri[ $i ]['carrier_name'] ); ?>">
											</div>
										<?php endif; ?>
										<h3><?php echo esc_html( $corrieri[ $i ]['carrier_name'] ); ?> <?php echo esc_html( $corrieri[ $i ]['service_name'] ); ?> | <?php echo esc_html( $corrieri[ $i ]['box_type'] ); ?></h3>
										<p><?php echo esc_html( $corrieri[ $i ]['carrier_ship_time'] ); ?></p>
										<form action="" class="add_carrier_form" method="post">
											<input type="hidden" name="carrier_name" value="<?php echo esc_attr( $corrieri[ $i ]['carrier_name'] ); ?>">
											<input type="hidden" name="service_name" value="<?php echo esc_attr( $corrieri[ $i ]['service_name'] ); ?>">
											<input type="hidden" name="pickup_type" value="<?php echo esc_attr( $corrieri[ $i ]['pickup_type'] ); ?>">
											<input type="hidden" name="box_type" value="<?php echo esc_attr( $corrieri[ $i ]['box_type'] ); ?>">
											<input type="hidden" name="image_url" value="<?php echo esc_attr( $corrieri[ $i ]['image_url'] ); ?>">
											<input type="hidden" name="carrier_ship_time" value="<?php echo esc_attr( $corrieri[ $i ]['carrier_ship_time'] ); ?>">
											<input type="hidden" name="service_id" value="<?php echo esc_attr( $corrieri[ $i ]['service_id'] ); ?>">
											<input type="hidden" name="carrier_id" value="<?php echo esc_attr( $corrieri[ $i ]['carrier_id'] ); ?>">
											<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'add_carrier_nonce' ) ); ?>" />
											<input type="hidden" name="action" value="add_carrier" />
											<input type="submit" 
												<?php
												if ( in_array( $corrieri[ $i ]['service_id'], $carriers_service_ids, true ) ) {
													echo esc_attr( 'disabled' );
												}
												?>
												name="add_carrier_submit" class="button button-primary add_carrier_button" value="<?php esc_attr_e( 'Add service', 'paccofacile-for-woocommerce' ); ?>">
										</form>
										
									</div>
								</div>
							<?php endfor; ?>
						</div>
					<?php endif; ?>
					
				</div>

				<div class="lista_corrieri">

					<?php if ( $carriers->have_posts() ) : ?>
						
						<?php
						while ( $carriers->have_posts() ) :
							$carriers->the_post();
							?>
						
							<div class="corriere serviceid_<?php echo esc_attr( get_post_meta( get_the_ID(), 'service_id' )[0] ); ?>" data-service-id="<?php echo esc_attr( get_post_meta( get_the_ID(), 'service_id' )[0] ); ?>">
								<div class="inner">
									<?php
									$pickup_type    = get_post_meta( get_the_ID(), 'pickup_type' )[0];
									$carrier_id     = get_post_meta( get_the_ID(), 'carrier_id' )[0];
									$store_city     = get_option( 'woocommerce_store_city' );
									$store_postcode = get_option( 'woocommerce_store_postcode' );
									$pickup_locker  = get_option( 'paccofacile_pickup_locker_' . $carrier_id );
									if ( 4 === (int) $pickup_type || 6 === (int) $pickup_type ) :
										?>
										<a href="#TB_inline?width=600&height=550&inlineId=manage_pickup_modal" name="<?php esc_attr_e( 'Choose a pickup locker', 'paccofacile-for-woocommerce' ); ?>" class="thickbox manage_pickup_modal_open"><i class="fa-solid fa-lg fa-gears"></i></a>
										<div id="manage_pickup_modal" style="display:none;" data-carrier-id="<?php echo esc_attr( $carrier_id ); ?>">
											<div id="paccofacile-map" class="paccofacile-map" data-postcode="<?php echo esc_attr( $store_postcode ); ?>" data-city="<?php echo esc_attr( $store_city ); ?>" data-carrier-id="<?php echo esc_attr( $carrier_id ); ?>" data-store-nonce="<?php echo esc_attr( wp_create_nonce( 'get_store_locker_nonce' ) ); ?>">
												<div id="popup" class="ol-popup">
													<a href="#" id="popup-closer" class="ol-popup-closer"></a>
													<div id="popup-content"></div>
												</div>
											</div>
											<form action="" class="add_store_locker_form" method="post">
												<div class="paccofacile-lockers-list" 
													<?php
													if ( $pickup_locker ) {
														echo 'data-pickup-locker="' . esc_attr( $pickup_locker ) . '"';
													}
													?>
													>
													</div>
												<input type="hidden" name="carrier_id" value="<?php echo esc_attr( $carrier_id ); ?>">
												<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'add_store_locker_nonce' ) ); ?>">
												<input type="hidden" name="action" value="add_store_locker" />
												<input type="submit" name="add_store_locker_submit" class="button button-primary add_store_locker_button" value="<?php esc_attr_e( 'Save as departure locker', 'paccofacile-for-woocommerce' ); ?>">
											</form>
											
										</div>
									<?php endif; ?>
									<?php
									if ( get_post_meta( get_the_ID(), 'image_url' ) ) :
										?>
										<div class="image">
											<img src="<?php echo esc_attr( get_post_meta( get_the_ID(), 'image_url' )[0] ); ?>" alt="<?php the_title(); ?>">
										</div>
									<?php endif; ?>
									<h3><?php the_title(); ?></h3>
									<p><?php echo esc_html( get_post_meta( get_the_ID(), 'carrier_ship_time' )[0] ); ?></p>

									<form action="" class="delete_carrier_form" method="post">
										<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'delete_carrier_nonce' ) ); ?>" />
										<input type="hidden" name="action" value="delete_carrier" />
										<input type="hidden" name="post_id" value="<?php the_ID(); ?>">
										<?php /* <a href="<?php echo get_edit_post_link(); ?>" class="button button-primary"><?php esc_attr_e('Manage', 'paccofacile-for-woocommerce'); ?></a> */ ?>
										<input type="submit" class="button button-primary delete_carrier" value="<?php esc_attr_e( 'Delete', 'paccofacile-for-woocommerce' ); ?>">
										<span class="spinner"></span>
									</form>
									
								</div>
							</div>
							
							<?php
						endwhile;
						wp_reset_postdata();
						?>

					<?php endif; ?>
				
				</div>
					
			</div>

		<?php endif; ?>


		
		
		<?php

	} elseif ( 'manage_boxes' === $active_tab ) {

		if ( 1 === get_option( 'paccofacile_api_valid' ) || '1' === get_option( 'paccofacile_api_valid' ) ) :

			add_thickbox();

			$plugin_paccofacile = new PFWC_Paccofacile();

			$shipping_boxes = $plugin_paccofacile->get_shipping_boxes();

			?>
			<div class="wrap">
				<a href="#TB_inline?width=600&height=550&inlineId=add_box_modal" class="button button-primary thickbox"><?php esc_html_e( 'Add package', 'paccofacile-for-woocommerce' ); ?></a>

				<div id="add_box_modal" style="display:none;">
					<form action="" class="add_box_form" method="post">

						<p class="form-field box_name_field ">
							<label for="box_name"><?php esc_html_e( 'Package name', 'paccofacile-for-woocommerce' ); ?></label>
							<input type="text" class="short" style="" required name="box_name" id="box_name" value="" placeholder=""> 
							<span class="description"><?php esc_html_e( 'Give a name to the package.', 'paccofacile-for-woocommerce' ); ?></span>
						</p>

						<?php $package_types = $plugin_paccofacile->get_package_types(); ?>

						<fieldset class="form-field paccofacile_box_type_field form-field-wide">
							<legend><?php esc_html_e( 'Package type', 'paccofacile-for-woocommerce' ); ?></legend>
							<ul class="wc-radios">
								<?php foreach ( $package_types as $package_type ) : ?>
									<?php
										$array_nome_type = json_decode( $package_type['nome'], true );
										$nome_type       = $array_nome_type['en'];
									?>
									<li>
										<label>
											<input name="paccofacile_box_type" value="<?php echo esc_attr( $package_type['imballi_tipo_id'] ); ?>" type="radio" class="select short" style="width:16px"> 
											<?php
												printf(
													/* translators: %s: Box type */
													esc_html__( 'Type %s', 'paccofacile-for-woocommerce' ),
													esc_html( $nome_type )
												);
											?>
										</label>
									</li>
								<?php endforeach; ?>
							</ul>
						</fieldset>
						
						<div class="paccofacile_box_fields">

							<p class="form-field dim1_field ">
								<label for="dim1"><?php esc_html_e( 'Side 1', 'paccofacile-for-woocommerce' ); ?> (cm)</label><input type="text" class="short" style="" name="dim1" id="dim1" value="" placeholder=""> 
							</p>
							<p class="form-field dim2_field ">
								<label for="dim2"><?php esc_html_e( 'Side 2', 'paccofacile-for-woocommerce' ); ?> (cm)</label><input type="text" class="short" style="" name="dim2" id="dim2" value="" placeholder=""> 
							</p>
							<p class="form-field dim3_field ">
								<label for="dim3"><?php esc_html_e( 'Side 3', 'paccofacile-for-woocommerce' ); ?> (cm)</label><input type="text" class="short" style="" name="dim3" id="dim3" value="" placeholder=""> 
							</p>
							<p class="form-field max_weight_field ">
								<label for="max_weight"><?php esc_html_e( 'Maximum weight', 'paccofacile-for-woocommerce' ); ?> (kg)</label><input type="text" class="short" style="" name="max_weight" id="max_weight" value="" placeholder=""> 
							</p>
						
						</div>

						<?php
						$pallet_options = $plugin_paccofacile->get_package_type_variation( 3 );
						?>

						<p class="paccofacile_pallet_type form-field pallet_type_field">
							<label for="pallet_type"><?php esc_html_e( 'Pallet dimensions', 'paccofacile-for-woocommerce' ); ?></label>
							<select style="" id="pallet_type" name="pallet_type" class="select short">
								<?php foreach ( $pallet_options as $variazione ) : ?>
									<option data-dim1="<?php echo esc_attr( $variazione['dim1'] ); ?>" data-dim2="<?php echo esc_attr( $variazione['dim2'] ); ?>" data-pesomax="<?php echo esc_attr( $variazione['peso_max'] ); ?>" value="<?php echo esc_attr( $variazione['variante_id'] ); ?>">
										<?php
										printf(
											/* translators: %1$d: Pallet base dim1, %2$d: Pallet base dim2 */
											esc_html__( 'Base %1$dx%2$d', 'paccofacile-for-woocommerce' ),
											floatval( $variazione['dim1'] ),
											floatval( $variazione['dim2'] )
										);
										?>
									</option>
								<?php endforeach; ?>
							</select>
						</p>

						<p class="form-field max_height_field paccofacile_pallet_max_height">
							<label for="max_height"><?php esc_html_e( 'Maximum height', 'paccofacile-for-woocommerce' ); ?> (cm)</label>
							<input type="text" class="short" style="" name="max_height" id="max_height" value="" placeholder=""> 
						</p>

						<?php

						$envelope_options = $plugin_paccofacile->get_package_type_variation( 2 );

						?>

						<p class="paccofacile_envelope_type form-field envelope_type_field">
							<label for="envelope_type"><?php esc_html_e( 'Envelope dimensions', 'paccofacile-for-woocommerce' ); ?></label>
							<select style="" id="envelope_type" name="envelope_type" class="select short">
								<?php foreach ( $envelope_options as $variazione ) : ?>
									<?php
										$array_nome_variante = json_decode( $variazione['nome_variante'], true );
										$nome_variante       = $array_nome_variante['en'];
									?>
									<option data-dim1="<?php echo esc_attr( $variazione['dim1'] ); ?>" data-dim2="<?php echo esc_attr( $variazione['dim2'] ); ?>" data-dim3="<?php echo esc_attr( $variazione['dim3'] ); ?>" data-pesomax="<?php echo esc_attr( $variazione['peso_max'] ); ?>" value="<?php echo esc_attr( $variazione['variante_id'] ); ?>">
										<?php
										printf(
											/* translators: %1$s: Envelope variant name, %2$d dim1, %3$d dim2, %4$d dim3, %5$d max weight  */
											esc_html__( '%1$s %2$dx%3$dx%4$d cm - Max %5$d kg', 'paccofacile-for-woocommerce' ),
											esc_html( $nome_variante ),
											floatval( $variazione['dim1'] ),
											floatval( $variazione['dim2'] ),
											floatval( $variazione['dim3'] ),
											floatval( $variazione['peso_max'] )
										);
										?>
									</option>
								<?php endforeach; ?>
							</select>
						</p>
						
						<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'add_box_nonce' ) ); ?>" />
						<input type="hidden" name="action" value="add_box" />
						<input type="submit" name="add_box_submit" class="button button-primary add_box_button" value="<?php esc_attr_e( 'Add package', 'paccofacile-for-woocommerce' ); ?>">
					</form>
				</div>
				
					
				<div class="lista_imballi">
					<table class="widefat fixed">
						<thead>
							<tr>
								<th width="80px"></th>
								<th><?php esc_html_e( 'Package name', 'paccofacile-for-woocommerce' ); ?></th>
								<th><?php esc_html_e( 'Dimensions', 'paccofacile-for-woocommerce' ); ?> (cm)</th>
								<th><?php esc_html_e( 'Volume', 'paccofacile-for-woocommerce' ); ?> (cm<sup>3</sup>)</th>
								<th><?php esc_html_e( 'Actions', 'paccofacile-for-woocommerce' ); ?></th>
							</tr>
						</thead>
						<tbody>

							<?php if ( ! empty( $shipping_boxes ) ) : ?>
						
								<?php foreach ( $shipping_boxes as $package ) : ?>

									<?php

									$package_name = $package['nome'];
									$package_id   = absint( $package['imballo_id'] );

									$paccofacile_box_type = $package['tipo'];
									$envelope_type        = '';
									$pallet_type          = '';
									$dim1                 = floatval( $package['dim1'] );
									$dim2                 = floatval( $package['dim2'] );
									$dim3                 = floatval( $package['dim3'] );
									$dimensions           = $dim1 . 'x' . $dim2 . 'x' . $dim3;
									$max_weight           = $package['peso_max'];
									$volume               = floatval( $package['volume'] );
									$max_height           = $package['altezza_max'];

									if ( 1 === $paccofacile_box_type ) { // pacco!
										$icon = PFWC_PACCOFACILE_PLUGIN_URL . '/public/images/pacco.svg';
									} elseif ( 2 === $paccofacile_box_type ) { // busta!
										$icon = PFWC_PACCOFACILE_PLUGIN_URL . '/public/images/busta.svg';
									} elseif ( 3 === $paccofacile_box_type ) { // pallet!
										$icon = PFWC_PACCOFACILE_PLUGIN_URL . '/public/images/pallet.svg';
									}

									?>

									<tr class="imballo imballo_<?php echo esc_attr( $package_id ); ?>">
										<td><img src="<?php echo esc_attr( $icon ); ?>" alt="box type"></td>
										<td><?php echo esc_html( $package_name ); ?></td>
										<td><?php echo esc_html( $dimensions ); ?></td>
										<td><?php echo esc_html( $volume ); ?></td>
										<td>
											<form action="" class="delete_box_form" method="post">
												<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( wp_create_nonce( 'delete_box_nonce' ) ); ?>" />
												<input type="hidden" name="action" value="delete_box" />
												<input type="hidden" name="imballo_id" value="<?php echo esc_attr( $package_id ); ?>">
												<?php /* <a href="<?php echo get_edit_post_link(); ?>" class="button button-primary"><?php _e('Edit', 'paccofacile-for-woocommerce'); ?></a> */ ?>
												<a href="#TB_inline?width=600&height=550&inlineId=edit_box_modal_<?php echo esc_attr( $package_id ); ?>" class="button button-primary thickbox modale-modifica" data-imballo-id="<?php echo esc_attr( $package_id ); ?>"><?php esc_html_e( 'Edit', 'paccofacile-for-woocommerce' ); ?></a>
												<input type="submit" class="button button-primary delete_box" value="<?php esc_attr_e( 'Delete', 'paccofacile-for-woocommerce' ); ?>">
												<span class="spinner"></span>
											</form>
											<div id="edit_box_modal_<?php echo esc_attr( $package_id ); ?>" style="display:none;">
												<form action="" class="add_box_form" method="post">
													<?php
													woocommerce_wp_text_input(
														array(
															'id'          => 'box_name',
															'label'       => __( 'Package name', 'paccofacile-for-woocommerce' ),
															'description' => __( 'Give a name to the package.', 'paccofacile-for-woocommerce' ),
															'value'       => $package_name,
														)
													);

													$options_tipo = array();

													foreach ( $package_types as $package_type ) {
														$array_nome_type = json_decode( $package_type['nome'], true );
														$nome_type       = $array_nome_type['en'];

														$options_tipo[ $package_type['imballi_tipo_id'] ] = $nome_type;
													}

													woocommerce_wp_radio(
														array(
															'id'      => 'paccofacile_box_type',
															'label'   => __( 'Package type', 'paccofacile-for-woocommerce' ),
															'value'   => $paccofacile_box_type,
															'options' => $options_tipo,
															'style'   => 'width:16px', // required for checkboxes and radio buttons!
															'wrapper_class' => 'form-field-wide', // always add this class!
														)
													);

													?>



													<div class="paccofacile_box_fields"
														<?php if ( 1 !== $paccofacile_box_type ) { ?>
															style="display:none;"
														<?php } ?>
														>
													<?php
														woocommerce_wp_text_input(
															array(
																'id'          => 'dim1',
																'label'       => __( 'Side 1', 'paccofacile-for-woocommerce' ) . ' (cm)',
																'value'       => $dim1,
															)
														);
														woocommerce_wp_text_input(
															array(
																'id'          => 'dim2',
																'label'       => __( 'Side 2', 'paccofacile-for-woocommerce' ) . ' (cm)',
																'value'       => $dim2,
															)
														);
														woocommerce_wp_text_input(
															array(
																'id'          => 'dim3',
																'label'       => __( 'Side 3', 'paccofacile-for-woocommerce' ) . ' (cm)',
																'value'       => $dim3,
															)
														);
														woocommerce_wp_text_input(
															array(
																'id'          => 'max_weight',
																'label'       => __( 'Maximum weight', 'paccofacile-for-woocommerce' ) . ' (kg)',
																'value'       => $max_weight,
															)
														);

													?>
													</div>


													<p class="paccofacile_pallet_type form-field pallet_type_field" 
														<?php if ( 3 !== $paccofacile_box_type ) { ?>
															style="display:none;"
														<?php } ?>
														>
														<label for="pallet_type"><?php esc_html_e( 'Pallet dimensions', 'paccofacile-for-woocommerce' ); ?></label>
														<select style="" id="pallet_type" name="pallet_type" class="select short">
															<?php foreach ( $pallet_options as $variazione ) : ?>
																<option data-dim1="<?php echo esc_attr( $variazione['dim1'] ); ?>" data-dim2="<?php echo esc_attr( $variazione['dim2'] ); ?>" data-pesomax="<?php echo esc_attr( $variazione['peso_max'] ); ?>" value="<?php echo esc_attr( $variazione['variante_id'] ); ?>">
																	<?php
																	printf(
																		/* translators: %1$d: Pallet base dim1, %2$d: Pallet base dim2 */
																		esc_html__( 'Base %1$dx%2$d', 'paccofacile-for-woocommerce' ),
																		floatval( $variazione['dim1'] ),
																		floatval( $variazione['dim2'] )
																	);
																	?>
																</option>
															<?php endforeach; ?>
														</select>
													</p>

													

													<p class="form-field max_height_field paccofacile_pallet_max_height" 
														<?php if ( 3 !== $paccofacile_box_type ) { ?>
															style="display:none;"
														<?php } ?>>
														<label for="max_height"><?php esc_html_e( 'Maximum height', 'paccofacile-for-woocommerce' ); ?> (cm)</label>
														<input type="text" class="short" style="" name="max_height" id="max_height" value="<?php echo floatval( $max_height ); ?>" placeholder=""> 
													</p>

													<p class="paccofacile_envelope_type form-field envelope_type_field" 
														<?php if ( 2 !== $paccofacile_box_type ) { ?>
															style="display:none;"
														<?php } ?>
														>
														<label for="envelope_type"><?php esc_html_e( 'Envelope dimensions', 'paccofacile-for-woocommerce' ); ?></label>
														<select style="" id="envelope_type" name="envelope_type" class="select short">
															<?php foreach ( $envelope_options as $variazione ) : ?>
																<?php
																	$array_nome_variante = json_decode( $variazione['nome_variante'], true );
																	$nome_variante       = $array_nome_variante['en'];
																?>
																<option data-dim1="<?php echo esc_attr( $variazione['dim1'] ); ?>" data-dim2="<?php echo esc_attr( $variazione['dim2'] ); ?>" data-dim3="<?php echo esc_attr( $variazione['dim3'] ); ?>" data-pesomax="<?php echo esc_attr( $variazione['peso_max'] ); ?>" value="<?php echo esc_attr( $variazione['variante_id'] ); ?>">
																	<?php
																	printf(
																		/* translators: %1$s: Envelope variant name, %2$d dim1, %3$d dim2, %4$d dim3, %5$d max weight  */
																		esc_html__( '%1$s %2$dx%3$dx%4$d cm - Max %5$d kg', 'paccofacile-for-woocommerce' ),
																		esc_html( $nome_variante ),
																		floatval( $variazione['dim1'] ),
																		floatval( $variazione['dim2'] ),
																		floatval( $variazione['dim3'] ),
																		floatval( $variazione['peso_max'] )
																	);
																	?>
																</option>
															<?php endforeach; ?>
														</select>
													</p>

													
													<input type="hidden" name="nonce" value="<?php echo esc_attr( wp_create_nonce( 'form-nonce' ) ); ?>" />
													<input type="hidden" name="action" value="edit_box" />
													<input type="hidden" name="imballo_id" value="<?php echo esc_attr( $package_id ); ?>" />
													<input type="submit" name="add_box_submit" class="button button-primary add_box_button" value="<?php esc_attr_e( 'Edit package', 'paccofacile-for-woocommerce' ); ?>">
												</form>
											</div>
										</td>
									</tr>
									
									<?php
								endforeach;
								?>

							<?php endif; ?>

						</tbody>

					</table>

				</div>
				
			</div>
		
			<?php

		endif;

	}

	/*
	Refund method!
	elseif ( $active_tab == 'refund_method' ) {

		if ( get_option( 'paccofacile_api_valid' ) == 1 ) : ?>

			<form action="options.php" method="post">

				<?php
				settings_fields( 'paccofacile_settings_refund' );
				do_settings_sections( 'paccofacile_refund' );
				do_settings_sections( 'paccofacile_refund_paypal' );
				do_settings_sections( 'paccofacile_refund_wire_transfer' );
				submit_button();
				?>

			</form>

		<?php endif;

	}
	*/
	?>
	
</div>
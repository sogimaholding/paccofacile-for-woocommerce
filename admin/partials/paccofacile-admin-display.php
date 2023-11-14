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

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<?php

require_once PACCOFACILE_PATH . '/includes/class-paccofacile-api.php';
$paccofacile_api = Paccofacile_Api::getInstance();

?>

<div class="wrap paccofacile_config">
    <h2><?php _e('Paccofacile', 'paccofacile'); ?></h2>
    <?php settings_errors(); ?>

    <?php
        if ( isset( $_GET['nonce'] ) && !wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'paccofacile_settings_nonce' ) ) {
            return;
        }
    ?>

    <?php
    $active_tab = '';
    if( isset( $_GET[ 'tab' ] ) ) {
        $active_tab = $_GET[ 'tab' ];
    } else {
        $active_tab = 'api_settings';
    } ?>

    <h2 class="nav-tab-wrapper">
        <a href="?page=paccofacile&tab=api_settings&nonce=<?php echo wp_create_nonce( 'paccofacile_settings_nonce' );?>" class="nav-tab <?php echo $active_tab == 'api_settings' ? 'nav-tab-active' : ''; ?>"><?php _e( 'API Settings', 'paccofacile' ); ?></a>
        <?php if( get_option( 'paccofacile_api_valid' ) == 1 ) : ?>
            <a href="?page=paccofacile&tab=shipping_services&nonce=<?php echo wp_create_nonce( 'paccofacile_settings_nonce' );?>" class="nav-tab <?php echo $active_tab == 'shipping_services' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Shipping services', 'paccofacile' ); ?></a>
            <a href="?page=paccofacile&tab=manage_boxes&nonce=<?php echo wp_create_nonce( 'paccofacile_settings_nonce' );?>" class="nav-tab <?php echo $active_tab == 'manage_boxes' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Packages', 'paccofacile' ); ?></a>
            <?php /* <a href="?page=paccofacile&tab=refund_method" class="nav-tab <?php echo $active_tab == 'refund_method' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Refund method', 'paccofacile' ); ?></a> */ ?>
        <?php endif; ?>
    </h2>

    <?php
    if( $active_tab == 'api_settings' ) { ?>
        
        <form action="options.php" method="post">

            <?php
            settings_fields( 'paccofacile_settings' );
            do_settings_sections( 'paccofacile' );
            do_settings_sections( 'paccofacile_tracking' );
            
            submit_button();
            ?>

        </form>
        
    <?php } elseif( $active_tab == 'shipping_services' ) { ?>

        <?php if( get_option( 'paccofacile_api_valid' ) == 1 ) : ?>

            <?php add_thickbox(); ?>

            <div class="wrap">
                <a href="#TB_inline?width=600&height=550&inlineId=add_courier_modal" class="button button-primary thickbox"><?php _e('Add service', 'paccofacile'); ?></a>

                <div id="add_courier_modal" style="display:none;">

                    <?php

                    $args_carrier = array(
                        'post_type' => 'carrier',
                        'post_status' => 'publish'
                    );

                    $carriers = new WP_Query($args_carrier);
                    
                    $response_corrieri = $paccofacile_api->get('carriers');

                    /* error_log( print_r( $response_corrieri, true ) ); */

                    $corrieri = $response_corrieri['data'];

                    $carriers_ids = wp_list_pluck( $carriers->posts, 'ID' );

                    $carriers_service_ids = array();
                    for($i=0; $i<sizeof($carriers_ids); $i++) {
                        $carriers_service_ids[] = get_post_meta( $carriers_ids[$i], 'service_id', true );
                    }
                    
                    if( !empty($corrieri) ) : ?>
                        <div class="lista_corrieri api">
                            <?php for($i=0; $i < sizeof($corrieri); $i++ ) : ?>
                                <div class="corriere <?php if( in_array($corrieri[$i]['service_id'], $carriers_service_ids) ) { echo 'spento'; } ?> serviceid_<?php echo $corrieri[$i]['service_id']; ?>">
                                    <div class="inner">
                                        <?php if($corrieri[$i]['image_url']) : ?>
                                            <div class="image">
                                                <img src="<?php echo $corrieri[$i]['image_url']; ?>" alt="<?php echo $corrieri[$i]['carrier_name']; ?>">
                                            </div>
                                        <?php endif; ?>
                                        <h3><?php echo $corrieri[$i]['carrier_name']; ?> <?php echo $corrieri[$i]['service_name']; ?> | <?php echo $corrieri[$i]['box_type']; ?></h3>
                                        <p><?php echo $corrieri[$i]['carrier_ship_time']; ?></p>
                                        <form action="" class="add_carrier_form" method="post">
                                            <input type="hidden" name="carrier_name" value="<?php echo $corrieri[$i]['carrier_name']; ?>">
                                            <input type="hidden" name="service_name" value="<?php echo $corrieri[$i]['service_name']; ?>">
                                            <input type="hidden" name="pickup_type" value="<?php echo $corrieri[$i]['pickup_type']; ?>">
                                            <input type="hidden" name="box_type" value="<?php echo $corrieri[$i]['box_type']; ?>">
                                            <input type="hidden" name="image_url" value="<?php echo $corrieri[$i]['image_url']; ?>">
                                            <input type="hidden" name="carrier_ship_time" value="<?php echo $corrieri[$i]['carrier_ship_time']; ?>">
                                            <input type="hidden" name="service_id" value="<?php echo $corrieri[$i]['service_id']; ?>">
                                            <input type="hidden" name="carrier_id" value="<?php echo $corrieri[$i]['carrier_id']; ?>">
                                            <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'add_carrier_nonce' );?>" />
                                            <input type="hidden" name="action" value="add_carrier" />
                                            <input type="submit" <?php if( in_array($corrieri[$i]['service_id'], $carriers_service_ids) ) { echo 'disabled'; } ?> name="add_carrier_submit" class="button button-primary add_carrier_button" value="<?php _e('Add service', 'paccofacile'); ?>">
                                        </form>
                                        
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                    
                </div>

                <div class="lista_corrieri">

                    <?php if( $carriers->have_posts() ) : ?>
                        
                        <?php while( $carriers->have_posts() ) : $carriers->the_post(); ?>
                        
                            <div class="corriere serviceid_<?php echo get_post_meta( get_the_ID(), 'service_id')[0]; ?>" data-service-id="<?php echo get_post_meta( get_the_ID(), 'service_id')[0]; ?>">
                                <div class="inner">
                                    <?php
                                    $pickup_type = get_post_meta( get_the_ID(), 'pickup_type')[0];
                                    $carrier_id = get_post_meta( get_the_ID(), 'carrier_id')[0];
                                    $store_city        = get_option( 'woocommerce_store_city' );
                                    $store_postcode    = get_option( 'woocommerce_store_postcode' );
                                    $pickup_locker    = get_option( 'paccofacile_pickup_locker_'.$carrier_id );
                                    if( $pickup_type == 4 || $pickup_type == 6 ) : ?>
                                        <a href="#TB_inline?width=600&height=550&inlineId=manage_pickup_modal" name="<?php _e('Choose a pickup locker', 'paccofacile'); ?>" class="thickbox manage_pickup_modal_open"><i class="fa-solid fa-lg fa-gears"></i></a>
                                        <div id="manage_pickup_modal" style="display:none;" data-carrier-id="<?php echo $carrier_id; ?>">
                                            <div id="paccofacile-map" class="paccofacile-map" data-postcode="<?php echo $store_postcode; ?>" data-city="<?php echo $store_city; ?>" data-carrier-id="<?php echo $carrier_id; ?>" data-store-nonce="<?php echo wp_create_nonce( 'get_store_locker_nonce' );?>">
                                                <div id="popup" class="ol-popup">
                                                    <a href="#" id="popup-closer" class="ol-popup-closer"></a>
                                                    <div id="popup-content"></div>
                                                </div>
                                            </div>
                                            <form action="" class="add_store_locker_form" method="post">
                                                <div class="paccofacile-lockers-list" <?php if($pickup_locker) { echo 'data-pickup-locker="'.$pickup_locker.'"'; } ?>></div>
                                                <input type="hidden" name="carrier_id" value="<?php echo $carrier_id; ?>">
                                                <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'add_store_locker_nonce' );?>">
                                                <input type="hidden" name="action" value="add_store_locker" />
                                                <input type="submit" name="add_store_locker_submit" class="button button-primary add_store_locker_button" value="<?php _e('Save as departure locker', 'paccofacile'); ?>">
                                            </form>
                                            
                                        </div>
                                    <?php endif; ?>
                                    <?php if( get_post_meta( get_the_ID(), 'image_url') ) : ?>
                                        <div class="image">
                                            <img src="<?php echo get_post_meta( get_the_ID(), 'image_url')[0]; ?>" alt="<?php the_title(); ?>">
                                        </div>
                                    <?php endif; ?>
                                    <h3><?php the_title(); ?></h3>
                                    <?php /* <p><?php echo 'Service ID: '.get_post_meta( get_the_ID(), 'service_id' )[0]; ?></p> */ ?>
                                    <p><?php echo get_post_meta( get_the_ID(), 'carrier_ship_time' )[0]; ?></p>

                                    <form action="" class="delete_carrier_form" method="post">
                                        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'delete_carrier_nonce' );?>" />
                                        <input type="hidden" name="action" value="delete_carrier" />
                                        <input type="hidden" name="post_id" value="<?php the_ID(); ?>">
                                        <?php /* <a href="<?php echo get_edit_post_link(); ?>" class="button button-primary"><?php _e('Manage', 'paccofacile'); ?></a> */ ?>
                                        <input type="submit" class="button button-primary delete_carrier" value="<?php _e('Delete', 'paccofacile'); ?>">
                                        <span class="spinner"></span>
                                    </form>
                                    
                                </div>
                            </div>
                            
                        <?php endwhile;
                        wp_reset_postdata(); ?>

                    <?php endif; ?>
                
                </div>
                    
            </div>

        <?php endif; ?>


        
        
        <?php

    } elseif( $active_tab == 'manage_boxes' ) {

        if( get_option( 'paccofacile_api_valid' ) == 1 ) :
        
            add_thickbox();

            $plugin = new Paccofacile();

            $shipping_boxes = $plugin->get_shipping_boxes();

            ?>
            <div class="wrap">
                <a href="#TB_inline?width=600&height=550&inlineId=add_box_modal" class="button button-primary thickbox"><?php _e('Add package', 'paccofacile'); ?></a>

                <div id="add_box_modal" style="display:none;">
                    <form action="" class="add_box_form" method="post">

                        <p class="form-field box_name_field ">
                            <label for="box_name"><?php _e( 'Package name', 'paccofacile' ); ?></label>
                            <input type="text" class="short" style="" required name="box_name" id="box_name" value="" placeholder=""> 
                            <span class="description"><?php _e( 'Give a name to the package.', 'paccofacile' ); ?></span>
                        </p>

                        <?php $package_types = $plugin->get_package_types(); ?>

                        <fieldset class="form-field paccofacile_box_type_field form-field-wide">
                            <legend><?php _e('Package type', 'paccofacile'); ?></legend>
                            <ul class="wc-radios">
                                <?php foreach( $package_types as $package_type ) : ?>
                                    <?php
                                        $array_nome_type = json_decode($package_type['nome'], true);
                                        $nome_type = $array_nome_type['en'];
                                    ?>
                                    <li><label><input name="paccofacile_box_type" value="<?php echo $package_type['imballi_tipo_id']; ?>" type="radio" class="select short" style="width:16px"> <?php _e($nome_type, "paccofacile"); ?></label></li>
                                <?php endforeach; ?>
                            </ul>
                        </fieldset>
                        
                        <div class="paccofacile_box_fields">

                            <p class="form-field dim1_field ">
                                <label for="dim1"><?php _e( 'Side 1', 'paccofacile' ); ?> (cm)</label><input type="text" class="short" style="" name="dim1" id="dim1" value="" placeholder=""> 
                            </p>
                            <p class="form-field dim2_field ">
		                        <label for="dim2"><?php _e( 'Side 2', 'paccofacile' ); ?> (cm)</label><input type="text" class="short" style="" name="dim2" id="dim2" value="" placeholder=""> 
                            </p>
                            <p class="form-field dim3_field ">
		                        <label for="dim3"><?php _e( 'Side 3', 'paccofacile' ); ?> (cm)</label><input type="text" class="short" style="" name="dim3" id="dim3" value="" placeholder=""> 
                            </p>
                            <p class="form-field max_weight_field ">
		                        <label for="max_weight"><?php _e( 'Maximum weight', 'paccofacile' ); ?> (kg)</label><input type="text" class="short" style="" name="max_weight" id="max_weight" value="" placeholder=""> 
                            </p>
                        
                        </div>

                        <?php

                        $pallet_options = $plugin->get_package_type_variation(3);
                        
                        ?>

                        <p class="paccofacile_pallet_type form-field pallet_type_field">
                            <label for="pallet_type"><?php _e('Pallet dimensions', 'paccofacile'); ?></label>
                            <select style="" id="pallet_type" name="pallet_type" class="select short">
                                <?php foreach($pallet_options as $variazione) : ?>
                                    <option data-dim1="<?php echo $variazione['dim1']; ?>" data-dim2="<?php echo $variazione['dim2']; ?>" data-pesomax="<?php echo $variazione['peso_max']; ?>" value="<?php echo $variazione['variante_id']; ?>"><?php _e('Base '.floatval($variazione['dim1']).'x'.floatval($variazione['dim2']), 'paccofacile'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>

                        <p class="form-field max_height_field paccofacile_pallet_max_height">
		                    <label for="max_height"><?php _e( 'Maximum height', 'paccofacile' ); ?> (cm)</label>
                            <input type="text" class="short" style="" name="max_height" id="max_height" value="" placeholder=""> 
                        </p>

                        <?php

                        $envelope_options = $plugin->get_package_type_variation(2);

                        ?>

                        <p class="paccofacile_envelope_type form-field envelope_type_field">
                            <label for="envelope_type"><?php _e('Envelope dimensions', 'paccofacile'); ?></label>
                            <select style="" id="envelope_type" name="envelope_type" class="select short">
                                <?php foreach($envelope_options as $variazione) : ?>
                                    <?php
                                        $array_nome_variante = json_decode($variazione['nome_variante'], true);
                                        $nome_variante = $array_nome_variante['en'];
                                    ?>
                                    <option data-dim1="<?php echo $variazione['dim1']; ?>" data-dim2="<?php echo $variazione['dim2']; ?>" data-dim3="<?php echo $variazione['dim3']; ?>" data-pesomax="<?php echo $variazione['peso_max']; ?>" value="<?php echo $variazione['variante_id']; ?>"><?php _e( $nome_variante.' '.floatval($variazione['dim1']).'x'.floatval($variazione['dim2']).'x'.floatval($variazione['dim3']).' cm - Max '.floatval($variazione['peso_max']).' kg', 'paccofacile'); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </p>
                        
                        <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'add_box_nonce' );?>" />
                        <input type="hidden" name="action" value="add_box" />
                        <input type="submit" name="add_box_submit" class="button button-primary add_box_button" value="<?php _e('Add package', 'paccofacile'); ?>">
                    </form>
                </div>
                
                    
                <div class="lista_imballi">
                    <table class="widefat fixed">
                        <thead>
                            <tr>
                                <th width="80px"></th>
                                <th><?php _e('Package name', 'paccofacile'); ?></th>
                                <th><?php _e('Dimensions', 'paccofacile'); ?> (cm)</th>
                                <th><?php _e('Volume', 'paccofacile'); ?> (cm<sup>3</sup>)</th>
                                <th><?php _e('Actions', 'paccofacile'); ?></th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php if( !empty( $shipping_boxes ) ) : ?>
                        
                                <?php foreach( $shipping_boxes as $package ) : ?>

                                    <?php

                                    $package_name = $package['nome'];
                                    $package_id = $package['imballo_id'];

                                    //$is_pallet = get_post_meta( get_the_ID(), 'is_pallet', true );
                                    $paccofacile_box_type = $package['tipo']; //get_post_meta( get_the_ID(), 'box_type', true );
                                    $envelope_type = '';
                                    $pallet_type = '';

                                    $dim1 = floatval($package['dim1']);
                                    $dim2 = floatval($package['dim2']);
                                    $dim3 = floatval($package['dim3']);
                                    $dimensions = $dim1.'x'.$dim2.'x'.$dim3;
                                    $max_weight = $package['peso_max'];
                                    $volume = floatval($package['volume']);
                                    $max_height = $package['altezza_max'];

                                    if( $paccofacile_box_type == 1 ) { // pacco
                                        $icon = '<svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path fill="currentColor" d="M447.9 176c0-10.6-2.6-21-7.6-30.3l-49.1-91.9c-4.3-13-16.5-21.8-30.3-21.8H87.1c-13.8 0-26 8.8-30.4 21.9L7.6 145.8c-5 9.3-7.6 19.7-7.6 30.3C.1 236.6 0 448 0 448c0 17.7 14.3 32 32 32h384c17.7 0 32-14.3 32-32 0 0-.1-211.4-.1-272zm-87-112l50.8 96H286.1l-12-96h86.8zM192 192h64v64h-64v-64zm49.9-128l12 96h-59.8l12-96h35.8zM87.1 64h86.8l-12 96H36.3l50.8-96zM32 448s.1-181.1.1-256H160v64c0 17.7 14.3 32 32 32h64c17.7 0 32-14.3 32-32v-64h127.9c0 74.9.1 256 .1 256H32z" class=""></path></svg>';
                                    } elseif( $paccofacile_box_type == 2 ) { // busta
                                        $icon = '<svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path fill="currentColor" d="M464 64H48C21.5 64 0 85.5 0 112v288c0 26.5 21.5 48 48 48h416c26.5 0 48-21.5 48-48V112c0-26.5-21.5-48-48-48zM48 96h416c8.8 0 16 7.2 16 16v41.4c-21.9 18.5-53.2 44-150.6 121.3-16.9 13.4-50.2 45.7-73.4 45.3-23.2.4-56.6-31.9-73.4-45.3C85.2 197.4 53.9 171.9 32 153.4V112c0-8.8 7.2-16 16-16zm416 320H48c-8.8 0-16-7.2-16-16V195c22.8 18.7 58.8 47.6 130.7 104.7 20.5 16.4 56.7 52.5 93.3 52.3 36.4.3 72.3-35.5 93.3-52.3 71.9-57.1 107.9-86 130.7-104.7v205c0 8.8-7.2 16-16 16z" class=""></path></svg>';
                                    } elseif( $paccofacile_box_type == 3 ) { // pallet
                                        $icon = '<svg aria-hidden="true" focusable="false" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><path fill="currentColor" d="M144 288h352c8.8 0 16-7.2 16-16V16c0-8.8-7.2-16-16-16H144c-8.8 0-16 7.2-16 16v256c0 8.8 7.2 16 16 16zM288 32h64v76.2l-32-16-32 16V32zm-128 0h96v128l64-32 64 32V32h96v224H160V32zm472 320c4.4 0 8-3.6 8-8v-16c0-4.4-3.6-8-8-8H8c-4.4 0-8 3.6-8 8v16c0 4.4 3.6 8 8 8h56v128H8c-4.4 0-8 3.6-8 8v16c0 4.4 3.6 8 8 8h624c4.4 0 8-3.6 8-8v-16c0-4.4-3.6-8-8-8h-56V352h56zM160 480H96V352h64v128zm288 0H192V352h256v128zm96 0h-64V352h64v128z" class=""></path></svg>';
                                    }

                                    ?>

                                    <tr class="imballo imballo_<?php echo $package_id; ?>">
                                        <td><?php echo $icon; ?></td>
                                        <td><?php echo $package_name; ?></td>
                                        <td><?php echo $dimensions; ?></td>
                                        <td><?php echo $volume; ?></td>
                                        <td>
                                            <form action="" class="delete_box_form" method="post">
                                                <input type="hidden" name="_wpnonce" value="<?php echo wp_create_nonce( 'delete_box_nonce' );?>" />
                                                <input type="hidden" name="action" value="delete_box" />
                                                <input type="hidden" name="imballo_id" value="<?php echo $package_id; ?>">
                                                <?php /* <a href="<?php echo get_edit_post_link(); ?>" class="button button-primary"><?php _e('Edit', 'paccofacile'); ?></a> */ ?>
                                                <a href="#TB_inline?width=600&height=550&inlineId=edit_box_modal_<?php echo $package_id; ?>" class="button button-primary thickbox modale-modifica" data-imballo-id="<?php echo $package_id; ?>"><?php _e('Edit', 'paccofacile'); ?></a>
                                                <input type="submit" class="button button-primary delete_box" value="<?php _e('Delete', 'paccofacile'); ?>">
                                                <span class="spinner"></span>
                                            </form>
                                            <div id="edit_box_modal_<?php echo $package_id; ?>" style="display:none;">
                                                <form action="" class="add_box_form" method="post">
                                                    <?php
                                                    woocommerce_wp_text_input(
                                                        array(
                                                            'id'          => 'box_name',
                                                            'label'       => __( 'Package name', 'paccofacile' ),
                                                            'description' => __( 'Give a name to the package.', 'paccofacile' ),
                                                            'value'       => $package_name,
                                                        )
                                                    );

                                                    $options_tipo = array();

                                                    foreach( $package_types as $package_type ) {
                                                        $array_nome_type = json_decode($package_type['nome'], true);
                                                        $nome_type = $array_nome_type['en'];
                                                        
                                                        $options_tipo[$package_type['imballi_tipo_id']] = __($nome_type, "paccofacile");
                                                    }

                                                    woocommerce_wp_radio( array(
                                                        'id' => 'paccofacile_box_type',
                                                        'label' => __('Package type', 'paccofacile'),
                                                        'value' => $paccofacile_box_type,
                                                        'options' => $options_tipo,
                                                        'style' => 'width:16px', // required for checkboxes and radio buttons
                                                        'wrapper_class' => 'form-field-wide' // always add this class
                                                    ) );

                                                    ?>



                                                    <div class="paccofacile_box_fields" <?php if($paccofacile_box_type != 1) { ?>style="display:none;"<?php } ?>>
                                                    <?php
                                                    
                                                        woocommerce_wp_text_input(
                                                            array(
                                                                'id'          => 'dim1',
                                                                'label'       => __( 'Side 1', 'paccofacile' ) . ' (cm)',
                                                                'value'       => $dim1,
                                                            )
                                                        );
                                                        woocommerce_wp_text_input(
                                                            array(
                                                                'id'          => 'dim2',
                                                                'label'       => __( 'Side 2', 'paccofacile' ) . ' (cm)',
                                                                'value'       => $dim2,
                                                            )
                                                        );
                                                        woocommerce_wp_text_input(
                                                            array(
                                                                'id'          => 'dim3',
                                                                'label'       => __( 'Side 3', 'paccofacile' ) . ' (cm)',
                                                                'value'       => $dim3,
                                                            )
                                                        );
                                                        woocommerce_wp_text_input(
                                                            array(
                                                                'id'          => 'max_weight',
                                                                'label'       => __( 'Maximum weight', 'paccofacile' ) . ' (kg)',
                                                                'value'       => $max_weight,
                                                            )
                                                        );

                                                    ?>
                                                    </div>


                                                    <p class="paccofacile_pallet_type form-field pallet_type_field" <?php if($paccofacile_box_type != 3) { ?>style="display:none;"<?php } ?>>
                                                        <label for="pallet_type"><?php _e('Pallet dimensions', 'paccofacile'); ?></label>
                                                        <select style="" id="pallet_type" name="pallet_type" class="select short">
                                                            <?php foreach($pallet_options as $variazione) : ?>
                                                                <option data-dim1="<?php echo $variazione['dim1']; ?>" data-dim2="<?php echo $variazione['dim2']; ?>" data-pesomax="<?php echo $variazione['peso_max']; ?>" value="<?php echo $variazione['variante_id']; ?>"><?php _e('Base '.floatval($variazione['dim1']).'x'.floatval($variazione['dim2']), 'paccofacile'); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </p>

                                                    

                                                    <p class="form-field max_height_field paccofacile_pallet_max_height" <?php if($paccofacile_box_type != 3) { ?>style="display:none;"<?php } ?>>
                                                        <label for="max_height"><?php _e( 'Maximum height', 'paccofacile' ); ?> (cm)</label>
                                                        <input type="text" class="short" style="" name="max_height" id="max_height" value="<?php echo floatval($max_height); ?>" placeholder=""> 
                                                    </p>

                                                    <p class="paccofacile_envelope_type form-field envelope_type_field" <?php if($paccofacile_box_type != 2) { ?>style="display:none;"<?php } ?>>
                                                        <label for="envelope_type"><?php _e('Envelope dimensions', 'paccofacile'); ?></label>
                                                        <select style="" id="envelope_type" name="envelope_type" class="select short">
                                                            <?php foreach($envelope_options as $variazione) : ?>
                                                                <?php
                                                                    $array_nome_variante = json_decode($variazione['nome_variante'], true);
                                                                    $nome_variante = $array_nome_variante['en'];
                                                                ?>
                                                                <option data-dim1="<?php echo $variazione['dim1']; ?>" data-dim2="<?php echo $variazione['dim2']; ?>" data-dim3="<?php echo $variazione['dim3']; ?>" data-pesomax="<?php echo $variazione['peso_max']; ?>" value="<?php echo $variazione['variante_id']; ?>"><?php _e( $nome_variante.' '.floatval($variazione['dim1']).'x'.floatval($variazione['dim2']).'x'.floatval($variazione['dim3']).' cm - Max '.floatval($variazione['peso_max']).' kg', 'paccofacile'); ?></option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </p>

                                                    
                                                    <input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'form-nonce' );?>" />
                                                    <input type="hidden" name="action" value="edit_box" />
                                                    <input type="hidden" name="imballo_id" value="<?php echo $package_id; ?>" />
                                                    <input type="submit" name="add_box_submit" class="button button-primary add_box_button" value="<?php _e('Edit package', 'paccofacile'); ?>">
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                <?php endforeach;
                                //wp_reset_postdata(); ?>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>
                
            </div>
        
        <?php

        endif;

    } /* elseif( $active_tab == 'refund_method' ) {

        if( get_option( 'paccofacile_api_valid' ) == 1 ) : ?>

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

    } */
    
    
    ?>
    
</div>
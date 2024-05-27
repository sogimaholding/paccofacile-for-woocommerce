<?php
/**
 * The API functions
 *
 * This class manages the API calls to Paccofacile APIs.

 * @link       #
 * @since      1.0.0
 * @package    Paccofacile
 * @subpackage Paccofacile/includes
 * @author     Francesco Barberini <supporto.tecnico@paccofacile.it>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
	// Exit if accessed directly.
}

/**
 * The service class for managing the API calls to Paccofacile.
 *
 * Method get and post to Paccofacile.
 *
 * @package    Paccofacile
 * @subpackage Paccofacile/includes
 * @author     Francesco Barberini <supporto.tecnico@paccofacile.it>
 */
class PFWC_Paccofacile_Api {

	/**
	 * The class instance
	 *
	 * @var [obj]
	 */
	private static $instance;

	/**
	 * The api base url
	 *
	 * @var [string]
	 */
	private $api_base_url;

	/**
	 * The api keys
	 *
	 * @var [array]
	 */
	private $keys;

	/**
	 * Calls headers
	 *
	 * @var [array]
	 */
	private $httpheader;

	/**
	 * Want to write logs?
	 *
	 * @var [bool]
	 */
	private $debug;

	/**
	 * Class constructor
	 */
	public function __construct() {

		$this->api_base_url = 'https://paccofacile.tecnosogima.cloud/live/v1/service/';

		$this->keys       = get_option(
			'paccofacile_settings',
			array(
				'account_number' => '',
				'api_key'        => '',
				'token'          => '',
			)
		);
		$this->httpheader = array(
			'Content-Type'   => 'application/json',
			'Account-Number' => $this->keys['account_number'],
			'api-key'        => $this->keys['api_key'],
			'Authorization'  => 'Bearer ' . $this->keys['token'],
		);

		$this->debug = false;
	}

	/**
	 * Call method get.
	 *
	 * @param string $endpoint The endpoint to call.
	 * @param array  $headers The http headers.
	 * @param array  $params The additional parameters to pass.
	 * @return array
	 */
	public function get( $endpoint = '', $headers = array(), $params = array() ) {
		$headers = array_merge( $this->httpheader, $headers );

		$httpheaders = array();

		foreach ( $headers as $key => $value ) {
			$httpheaders[ $key ] = $value;
		}

		$get_params = '';
		if ( count( $params ) > 0 ) {
			$get_params = '?';
			$count      = 0;
			foreach ( $params as $key => $value ) {
				$count++;
				$get_params .= $key . '=' . $value;
				if ( $count < count( $params ) ) {
					$get_params .= '&';
				}
			}
		}

		$args = array(
			'headers' => $httpheaders,
		);

		if ( true === $this->debug ) {
			error_log( gmdate( 'Y-m-d H:i:s' ) . ' - [GET REQUEST ' . $endpoint . '] -> ' . wp_json_encode( $args ) );
		}

		$response = wp_remote_get( $this->api_base_url . $endpoint . $get_params, $args );

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		$httpcode              = wp_remote_retrieve_response_code( $response );
		$response_body['code'] = $httpcode;

		if ( true === $this->debug ) {
			error_log( gmdate( 'Y-m-d H:i:s' ) . ' - [GET RESPONSE ' . $endpoint . '] -> ' . wp_json_encode( $response_body ) );
		}

		return $response_body;
	}

	/**
	 * Call method post.
	 *
	 * @param string $endpoint The endpoint to call.
	 * @param array  $headers The http headers.
	 * @param array  $payload The additional parameters to pass.
	 * @return array
	 */
	public function post( $endpoint = '', $headers = array(), $payload = array() ) {

		$headers = array_merge( $this->httpheader, $headers );

		$httpheaders = array();

		foreach ( $headers as $key => $value ) {
			$httpheaders[ $key ] = $value;
		}
		$httpheaders['Session-Id'] = 'aaa';

		if ( is_array( $payload ) ) {
			$payload = wp_json_encode( $payload );
		}

		$base_url = $this->api_base_url;

		$args = array(
			'method'  => 'POST',
			'headers' => $httpheaders,
			'body'    => $payload,
		);

		if ( true === $this->debug ) {
			error_log( gmdate( 'Y-m-d H:i:s' ) . ' - [POST REQUEST ' . $endpoint . '] -> ' . wp_json_encode( $args ) );
		}

		$response = wp_remote_post( $base_url . $endpoint, $args );

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		$httpcode              = wp_remote_retrieve_response_code( $response );
		$response_body['code'] = $httpcode;

		if ( true === $this->debug ) {
			error_log( gmdate( 'Y-m-d H:i:s' ) . ' - [POST RESPONSE ' . $endpoint . '] -> ' . wp_json_encode( $response_body ) );
		}

		return $response_body;
	}

	/**
	 * Call method delete.
	 *
	 * @param string $endpoint The endpoint to call.
	 * @param array  $headers The http headers.
	 * @param array  $payload The additional parameters to pass.
	 * @return array
	 */
	public function delete( $endpoint = '', $headers = array(), $payload = array() ) {

		$headers = array_merge( $this->httpheader, $headers );

		$httpheaders = array();

		foreach ( $headers as $key => $value ) {
			$httpheaders[ $key ] = $value;
		}
		$httpheaders['Session-Id'] = 'aaa';

		if ( is_array( $payload ) ) {
			$payload = wp_json_encode( $payload );
		}

		$args = array(
			'method'  => 'DELETE',
			'headers' => $httpheaders,
			'body'    => $payload,
		);

		if ( true === $this->debug ) {
			error_log( gmdate( 'Y-m-d H:i:s' ) . ' - [DELETE REQUEST ' . $endpoint . '] -> ' . wp_json_encode( $args ) );
		}

		$response = wp_remote_request( $this->api_base_url . $endpoint, $args );

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		$httpcode              = wp_remote_retrieve_response_code( $response );
		$response_body['code'] = $httpcode;

		if ( true === $this->debug ) {
			error_log( gmdate( 'Y-m-d H:i:s' ) . ' - [DELETE RESPONSE ' . $endpoint . '] -> ' . wp_json_encode( $response_body ) );
		}

		return $response_body;
	}

	/**
	 * Call method put.
	 *
	 * @param string $endpoint The endpoint to call.
	 * @param array  $headers The http headers.
	 * @param array  $payload The additional parameters to pass.
	 * @return array
	 */
	public function put( $endpoint = '', $headers = array(), $payload = array() ) {

		$headers = array_merge( $this->httpheader, $headers );

		$httpheaders = array();

		foreach ( $headers as $key => $value ) {
			$httpheaders[] = $key . ': ' . $value;
		}
		$httpheaders[] = 'Session-Id: aaa';

		if ( is_array( $payload ) ) {
			$payload = wp_json_encode( $payload );
		}

		$args = array(
			'method'  => 'PUT',
			'headers' => $httpheaders,
			'body'    => $payload,
		);

		if ( true === $this->debug ) {
			error_log( gmdate( 'Y-m-d H:i:s' ) . ' - [PUT REQUEST ' . $endpoint . '] -> ' . wp_json_encode( $args ) );
		}

		$response = wp_remote_request( $this->api_base_url . $endpoint, $args );

		$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

		$httpcode              = wp_remote_retrieve_response_code( $response );
		$response_body['code'] = $httpcode;

		if ( true === $this->debug ) {
			error_log( gmdate( 'Y-m-d H:i:s' ) . ' - [PUT RESPONSE ' . $endpoint . '] -> ' . wp_json_encode( $response_body ) );
		}

		return $response_body;
	}

	/**
	 * Calculate quote method
	 *
	 * @param string  $pickup_contry         pickup country iso code.
	 * @param string  $pickup_state          pickup state or province code.
	 * @param string  $pickup_postcode       pickup postalcode.
	 * @param string  $pickup_city           pickup city.
	 * @param string  $destination_country   destination country name.
	 * @param string  $destination_state     destination state or province code.
	 * @param string  $destination_postcode  destination postalcode.
	 * @param string  $destination_city      destination city.
	 * @param array   $parcels               shipping parcels.
	 * @param boolean $service_id            service id.
	 * @return string
	 */
	public function calculate_quote( $pickup_contry, $pickup_state, $pickup_postcode, $pickup_city, $destination_country, $destination_state, $destination_postcode, $destination_city, $parcels = array(), $service_id = false ) {
		$payload = array(
			'shipment_service' => array(
				'parcels'              => $parcels,
				'package_content_type' => 'GOODS',
			),
			'pickup'           => array(
				'iso_code'            => $pickup_contry,
				'postal_code'         => $pickup_postcode,
				'city'                => $pickup_city,
				'StateOrProvinceCode' => $pickup_state,
			),
			'destination'      => array(
				'iso_code'            => $destination_country,
				'postal_code'         => $destination_postcode,
				'city'                => $destination_city,
				'StateOrProvinceCode' => $destination_state,
			),
		);
		if ( $service_id ) {
			$payload['shipment_service']['service_id'] = $service_id;
		}

		$response = $this->post( 'shipment/quote?field_pickup_date=1', array(), $payload );

		return $response;
	}

	/**
	 * Get instance method.
	 *
	 * @return [class obj]
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new PFWC_Paccofacile_Api();
		}

		return self::$instance;
	}
}

$paccofacile_api = PFWC_Paccofacile_Api::get_instance();

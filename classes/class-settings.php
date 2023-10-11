<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class Mai_Sellers_JSON_Settings {

	/**
	 * Mai_Sellers_JSON_Settings constructor.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Runs hooks.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function hooks() {
		add_action( 'acf/init',                                      [ $this, 'register' ] );
		add_action( 'acf/render_field/key=maisj_identifiers',        [ $this, 'admin_css' ] );
		add_filter( 'acf/load_field/key=maisj_contact_address',      [ $this, 'load_contact_address' ] );
		add_filter( 'acf/load_field/key=maisj_contact_email',        [ $this, 'load_contact_email' ] );
		add_filter( 'acf/load_field/key=maisj_version',              [ $this, 'load_version' ] );
		add_filter( 'acf/load_field/key=maisj_identifiers',          [ $this, 'load_identifiers' ] );
		add_filter( 'acf/load_field/key=maisj_sellers',              [ $this, 'load_sellers' ] );
		add_filter( 'acf/validate_value/name=maisj_identifier_name', [ $this, 'validate_identifier_name' ], 10, 4 );
		add_action( 'acf/save_post',                                 [ $this, 'save' ], 99 );
		add_filter( 'plugin_action_links_mai-sellers-json/mai-sellers-json.php', [ $this, 'add_settings_link' ], 10, 4 );
	}

	/**
	 * Registers options page and field groups from settings and custom block.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function register() {
		acf_add_options_sub_page(
			[
				'menu_title' => class_exists( 'Mai_Engine' ) ? __( 'Settings.json', 'mai-settings-json' ) : __( 'Mai Settings.json', 'mai-settings-json' ),
				'page_title' => __( 'Mai Settings.json', 'mai-settings-json' ),
				'parent'     => class_exists( 'Mai_Engine' ) ? 'mai-theme' : 'options-general.php',
				'menu_slug'  => 'mai-settings-json',
				'capability' => 'manage_options',
				'position'   => 4,
			]
		);

		acf_add_local_field_group(
			[
				'key'    => 'maisj_options',
				'title'  => __( 'Mai Sellers.json', 'mai-settings-json' ),
				'style'  => 'seamless',
				'fields' => [
					[
						'key'      => 'maisj_message',
						'type'     => 'message',
						'message'  => sprintf( '<p>%s <a target="_blank" href="https://iabtechlab.com/wp-content/uploads/2019/07/Sellers.json_Final.pdf">%s.</a></p>', __( 'This is a custom options page for the', 'mai-settings-json' ), __( 'IAB Tech Lab Sellers.json', 'mai-settings-json' ) ),
						'esc_html' => 0,
					],
					[
						'label' => __( 'Contact Address', 'mai-settings-json' ),
						'key'   => 'maisj_contact_address',
						'name'  => 'maisj_contact_address',
						'type'  => 'text',
					],
					[
						'label' => __( 'Contact Email', 'mai-settings-json' ),
						'key'   => 'maisj_contact_email',
						'name'  => 'maisj_contact_email',
						'type'  => 'email',
					],
					[
						'label'         => __( 'Version', 'mai-settings-json' ),
						'key'           => 'maisj_version',
						'name'          => 'maisj_version',
						'type'          => 'text',
						'default_value' => '1.0',
					],
					[
						'label'         => __( 'Identifiers', 'mai-settings-json' ),
						'instructions'  => sprintf( '%s<br>%s', __( 'Add your identifiers here.', 'mai-sellers-json' ), __( 'Shift + Click the up/down arrow on the left to toggle open/closed.', 'mai-settings-json' ) ),
						'key'           => 'maisj_identifiers',
						'name'          => 'maisj_identifiers',
						'type'          => 'repeater',
						'collapsed'     => 'maisj_identifier_name',
						'min'           => 0,
						'max'           => 0,
						'layout'        => 'block',
						'button_label'  => __( 'Add New Seller', 'mai-settings-json' ),
						'sub_fields'    => [
							[
								'label'    => __( 'Name', 'mai-settings-json' ),
								'key'      => 'maisj_identifier_name',
								'name'     => 'name',
								'type'     => 'text',
								'required' => 1,
								'wrapper'  => [
									'width' => '50',
								],
							],
							[
								'label'    => __( 'Value', 'mai-settings-json' ),
								'key'      => 'maisj_identifier_value',
								'name'     => 'value',
								'type'     => 'text',
								'required' => 1,
								'wrapper'  => [
									'width' => '50',
								],
							],
						],
					],
					[
						'label'         => __( 'Sellers', 'mai-settings-json' ),
						'instructions'  => sprintf( '%s<br>%s', __( 'Add your sellers here. Name and Domain are required when Is Confidential field is unchecked.', 'mai-sellers-json' ), __( 'Shift + Click the up/down arrow on the left to toggle open/closed.', 'mai-settings-json' ) ),
						'key'           => 'maisj_sellers',
						'name'          => 'maisj_sellers',
						'type'          => 'repeater',
						'collapsed'     => 'maisj_seller_name',
						'layout'        => 'block',
						'button_label'  => __( 'Add New Seller', 'mai-settings-json' ),
						'sub_fields'    => [
							[
								'label'    => __( 'Name', 'mai-settings-json' ) . ' *',
								'key'      => 'maisj_seller_name',
								'name'     => 'name',
								'type'     => 'text',
								'wrapper'  => [
									'width' => '50',
								],
							],
							[
								'label'    => __( 'Seller ID', 'mai-settings-json' ),
								'key'      => 'maisj_seller_id',
								'name'     => 'seller_id',
								'type'     => 'text',
								'required' => 1,
								'wrapper'  => [
									'width' => '50',
								],
							],
							[
								'label'    => __( 'Domain', 'mai-settings-json' ) . ' *',
								'key'      => 'maisj_seller_domain',
								'name'     => 'domain',
								'type'     => 'text',
								'wrapper'  => [
									'width' => '50',
								],
							],
							[
								'label'    => __( 'Seller Type', 'mai-settings-json' ),
								'key'      => 'maisj_seller_type',
								'name'     => 'seller_type',
								'type'     => 'select',
								'required' => 1,
								'choices'  => [
									''             => __( 'Choose one', 'mai-settings-json' ),
									'PUBLISHER'    => __( 'Publisher', 'mai-settings-json' ),
									'INTERMEDIARY' => __( 'Intermediary', 'mai-settings-json' ),
									'BOTH'         => __( 'Both', 'mai-settings-json' ),
								],
								'wrapper'  => [
									'width' => '50',
								],
							],
							[
								'message'  => __( 'Is Confidential', 'mai-settings-json' ),
								'key'      => 'maisj_is_confidential',
								'name'     => 'is_confidential',
								'type'     => 'true_false',
								'wrapper'  => [
									'width' => '25',
								],
							],
							[
								'message'  => __( 'Is Passthrough', 'mai-settings-json' ),
								'key'      => 'maisj_is_passthrough',
								'name'     => 'is_passthrough',
								'type'     => 'true_false',
								'wrapper'  => [
									'width' => '25',
								],
							],
							[
								'placeholder' => __( 'Description for this inventory...', 'mai-settings-json' ),
								'key'         => 'maisj_comment',
								'name'        => 'comment',
								'type'        => 'textarea',
								'rows'        => 2,
								'wrapper'     => [
									'width' => '50',
								],
							],
						],
					],
				],
				'location' => [
					[
						[
							'param'    => 'options_page',
							'operator' => '==',
							'value'    => 'mai-settings-json',
						],
					],
				],
			]
		);
	}

	/**
	 * Gets inline admin CSS.
	 *
	 * @since 1.1.0
	 *
	 * @param array $field
	 *
	 * @return void
	 */
	function admin_css( $field ) {
		?>
		<style>
		.acf-field-number input[type="number"] {
			max-width: 100px;
		}

		.acf-repeater .acf-url input[type="url"] {
			display: inline-flex;
			justify-content: flex-end;
		}

		.acf-repeater .acf-actions {
			text-align: start;
		}

		.acf-repeater .acf-actions .acf-button {
			float: none !important;
		}
		</style>
		<?php
	}

	/**
	 * Loads contact address field value.
	 *
	 * @since 0.1.0
	 *
	 * @param array $field The field data.
	 *
	 * @return array
	 */
	function load_contact_address( $field ) {
		$field['value'] = sanitize_text_field( maisj_get_value( 'contact_address' ) );

		return $field;
	}

	/**
	 * Loads contact email field value.
	 *
	 * @since 0.1.0
	 *
	 * @param array $field The field data.
	 *
	 * @return array
	 */
	function load_contact_email( $field ) {
		$field['value'] = sanitize_email( maisj_get_value( 'contact_email' ) );

		return $field;
	}

	/**
	 * Loads version field value.
	 *
	 * @since 0.1.0
	 *
	 * @param array $field The field data.
	 *
	 * @return array
	 */
	function load_version( $field ) {
		$field['value'] = sanitize_text_field( maisj_get_value( 'version' ) );

		return $field;
	}

	/**
	 * Loads identifiers repeater field values.
	 *
	 * @since 0.1.0
	 *
	 * @param array $field The field data.
	 *
	 * @return array
	 */
	function load_identifiers( $field ) {
		$field['value'] = [];
		$identifiers    = maisj_get_value( 'identifiers' );

		if ( ! $identifiers ) {
			return $field;
		}

		foreach ( $identifiers as $key => $values ) {
			$field['value'][] = [
				'maisj_identifier_name'  => isset( $values['name'] ) ? sanitize_text_field( $values['name'] ) : '',
				'maisj_identifier_value' => isset( $values['value'] ) ? sanitize_text_field( $values['value'] ) : '',
			];
		}

		return $field;
	}

	/**
	 * Loads sellers repeater field values.
	 *
	 * @since 0.1.0
	 *
	 * @param array $field The field data.
	 *
	 * @return array
	 */
	function load_sellers( $field ) {
		$field['value'] = [];
		$sellers        = maisj_get_value( 'sellers' );

		if ( ! $sellers ) {
			return $field;
		}

		foreach ( $sellers as $key => $values ) {
			$field['value'][] = [
				'maisj_seller_id'       => isset( $values['seller_id'] ) ? sanitize_text_field( $values['seller_id'] ) : '',
				'maisj_seller_name'     => isset( $values['name'] ) ? sanitize_text_field( $values['name'] ) : '',
				'maisj_seller_domain'   => isset( $values['domain'] ) ? $this->get_url_host( $values['domain'] ) : '',
				'maisj_seller_type'     => isset( $values['seller_type'] ) ? sanitize_text_field( $values['seller_type'] ) : '',
				'maisj_is_confidential' => isset( $values['is_confidential'] ) ? rest_sanitize_boolean( $values['is_confidential'] ) : 0,
				'maisj_is_passthrough'  => isset( $values['is_passthrough'] ) ? rest_sanitize_boolean( $values['is_passthrough'] ) : 0,
				'maisj_comment'         => isset( $values['comment'] ) ? sanitize_text_field( $values['comment'] ) : '',
			];
		}

		return $field;
	}

	/**
	 * Sanitizes domain to be used in GAM.
	 *
	 * @since 0.1.0
	 *
	 * @param string $domain The domain.
	 *
	 * @return string
	 */
	function get_url_host( string $domain ) {
		$domain = $domain ? (string) wp_parse_url( esc_url( (string) $domain ), PHP_URL_HOST ) : '';
		$domain = str_replace( 'www.', '', $domain );

		return $domain;
	}

	function validate_identifier_name( $valid, $value, $field, $input ){
		$sellers = $_POST['acf']['maisj_sellers'];

		ray( $sellers );

		// if($sellers){
		// 	if(!$value){
		// 		$valid = __('This field is required for public events');
		// 	}
		// }

		return $valid;

	}

	/**
	 * Updates and deletes options when saving the settings page.
	 *
	 * @since 0.1.0
	 *
	 * @param mixed $post_id The post ID from ACF.
	 *
	 * @return void
	 */
	function save( $post_id ) {
		// Bail if no data.
		if ( ! isset( $_POST['acf'] ) || empty( $_POST['acf'] ) ) {
			return;
		}

		// Bail if not saving an options page.
		if ( 'options' !== $post_id ) {
			return;
		}

		// Current screen.
		$screen = get_current_screen();

		// Bail if not our options page.
		if ( ! $screen || false === strpos( $screen->id, 'mai-settings-json' ) ) {
			return;
		}

		// Set data var.
		$data  = [
			'contact_email'   => (string) get_field( 'maisj_contact_email', 'option' ),
			'contact_address' => (string) get_field( 'maisj_contact_address', 'option' ),
			'version'         => (float) get_field( 'maisj_version', 'option' ),
		];

		// Get repeaters.
		$identifiers = (array) get_field( 'maisj_identifiers', 'option' );
		$sellers     = (array) get_field( 'maisj_sellers', 'option' );

		// Format identifiers.
		foreach ( $identifiers as $values ) {
			$name  = isset( $values['name'] ) ? sanitize_text_field( $values['name'] ) : '';
			$value = isset( $values['value'] ) ? sanitize_text_field( $values['value'] ) : '';

			if ( ! ( $name && $value ) ) {
				continue;
			}

			// Add to data to save.
			$data['identifiers'][] = [
				'name'  => $name,
				'value' => $value,
			];
		}

		// Format sellers.
		foreach ( $sellers as $values ) {
			$id           = isset( $values['seller_id'] ) ? sanitize_text_field( $values['seller_id'] ) : '';
			$name         = isset( $values['name'] ) ? sanitize_text_field( $values['name'] ) : '';
			$domain       = isset( $values['domain'] ) ? $this->get_url_host( $values['domain'] ) : '';
			$type         = isset( $values['seller_type'] ) ? sanitize_text_field( $values['seller_type'] ) : '';
			$confidential = isset( $values['is_confidential'] ) ? absint( $values['is_confidential'] ) : 0;
			$passthrough  = isset( $values['is_passthrough'] ) ? absint( $values['is_passthrough'] ) : 0;
			$comment      = isset( $values['comment'] ) ? sanitize_text_field( $values['comment'] ) : '';

			if ( ! ( $id && $name && $domain && $type ) ) {
				continue;
			}

			// Add to data to save.
			$data['sellers'][] = [
				'seller_id'       => $id,
				'name'            => $name,
				'domain'          => $domain,
				'seller_type'     => $type,
				'is_confidential' => $confidential,
				'is_passthrough'  => $passthrough,
				'comment'         => $comment,
			];
		}

		// Save new data to our field key.
		update_option( 'mai_sellers_json', $data );

		// Clear repeater fields.
		update_field( 'maisj_identifiers', null, $post_id );
		update_field( 'maisj_sellers', null, $post_id );

		// To delete.
		$options = [
			'options_maisj_contact_address',
			'options_maisj_contact_email',
			'options_maisj_version',
			'options_maisj_identifiers',
			'options_maisj_sellers',
			'_options_maisj_contact_address',
			'_options_maisj_contact_email',
			'_options_maisj_version',
			'_options_maisj_identifiers',
			'_options_maisj_sellers',
		];

		// Delete remaining options manually.
		foreach ( $options as $option ) {
			delete_option( $option );
		}
	}

	/**
	 * Return the plugin action links.  This will only be called if the plugin is active.
	 *
	 * @since 0.1.0
	 *
	 * @param array  $actions     Associative array of action names to anchor tags
	 * @param string $plugin_file Plugin file name, ie my-plugin/my-plugin.php
	 * @param array  $plugin_data Associative array of plugin data from the plugin file headers
	 * @param string $context     Plugin status context, ie 'all', 'active', 'inactive', 'recently_active'
	 *
	 * @return array associative array of plugin action links
	 */
	function add_settings_link( $actions, $plugin_file, $plugin_data, $context ) {
		if ( ! class_exists( 'acf_pro' ) ) {
			return $actions;
		}

		$actions['settings'] = $this->get_settings_link( __( 'Settings', 'mai-settings-json' ) );

		return $actions;
	}

	/**
	 * Gets settings link.
	 *
	 * @since 0.1.0
	 *
	 * @param string $text
	 *
	 * @return string
	 */
	function get_settings_link( $text ) {
		$url  = esc_url( admin_url( sprintf( '%s.php?page=mai-settings-json', class_exists( 'Mai_Engine' ) ? 'admin' : 'options-general' ) ) );
		$link = sprintf( '<a href="%s">%s</a>', $url, $text );

		return $link;
	}
}
<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class Mai_Sellers_JSON_Settings {
	protected $path;
	protected $exists;
	protected $writable;

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
		add_action( 'admin_notices',                                 [ $this, 'admin_notice' ] );
		add_action( 'acf/input/admin_enqueue_scripts',               [ $this, 'enqueue_script' ] );
		add_action( 'acf/init',                                      [ $this, 'register' ] );
		add_action( 'acf/render_field/key=maisj_identifiers',        [ $this, 'admin_css' ] );
		add_filter( 'acf/load_field/key=maisj_contact_address',      [ $this, 'load_contact_address' ] );
		add_filter( 'acf/load_field/key=maisj_contact_email',        [ $this, 'load_contact_email' ] );
		add_filter( 'acf/load_field/key=maisj_version',              [ $this, 'load_version' ] );
		add_filter( 'acf/load_field/key=maisj_identifiers',          [ $this, 'load_identifiers' ] );
		add_filter( 'acf/load_field/key=maisj_sellers',              [ $this, 'load_sellers' ] );
		add_filter( 'acf/validate_value/key=maisj_seller_name',      [ $this, 'validate_seller_name_domain' ], 10, 4 );
		add_filter( 'acf/validate_value/key=maisj_seller_domain',    [ $this, 'validate_seller_name_domain' ], 10, 4 );
		add_action( 'acf/save_post',                                 [ $this, 'save' ], 99 );
		add_filter( 'plugin_action_links_mai-sellers-json/mai-sellers-json.php', [ $this, 'add_settings_link' ], 10, 4 );
	}

	/**
	 * Adds admin notice if sellers.json file does not exist or is not writeable.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function admin_notice() {
		// Current screen.
		$screen = get_current_screen();

		// Bail if not our options page.
		if ( ! $screen || false === strpos( $screen->id, 'mai-sellers-json' ) ) {
			return;
		}

		// Check if sellers.json exists.
		if ( ! $this->exists ) {
			printf( '<div class="notice notice-warning"><p>%s</p></div>', __( 'A sellers.json file does not exist. Updating this page will attempt to create a new file.', 'mai-sellers-json' ) );
		} elseif ( ! $this->writeable ) {
			printf( '<div class="notice notice-error"><p>%s</p></div>', __( 'The sellers.json file is not writable. Please make sure it is writable. Updating these settings will save to the DB but will not write to the sellers.json file.', 'mai-sellers-json' ) );
		}
	}

	/**
	 * Enqueue script for encoder/decoder.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function enqueue_script() {
		// Current screen.
		$screen = get_current_screen();

		// Bail if not our options page.
		if ( ! $screen || false === strpos( $screen->id, 'mai-sellers-json' ) ) {
			return;
		}

		wp_enqueue_script( 'mai-sellers-json', MAI_SELLERS_JSON_URL . 'assets/js/mai-sellers-json.js', [], MAI_SELLERS_JSON_VERSION, true );
	}

	/**
	 * Registers options page and field groups from settings and custom block.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	function register() {
		$this->path      = get_home_path() . 'sellers.json';
		$this->exists    = file_exists( $this->path );
		$this->writeable = is_writable( $this->path );

		acf_add_options_sub_page(
			[
				'menu_title'      => class_exists( 'Mai_Engine' ) ? __( 'Sellers.json', 'mai-sellers-json' ) : __( 'Mai Sellers.json', 'mai-sellers-json' ),
				'page_title'      => __( 'Mai Sellers.json', 'mai-sellers-json' ),
				'parent'          => class_exists( 'Mai_Engine' ) ? 'mai-theme' : 'options-general.php',
				'menu_slug'       => 'mai-sellers-json',
				'capability'      => 'manage_options',
				'position'        => 4,
				'updated_message' => __( 'Values updated in DB.', 'mai-sellers-json' ) . ( $this->writeable ? ' ' . __( 'File updated.', 'mai-sellers-json' ) : __( ' File was not updated because it is not writeable.', 'mai-sellers-json' ) ),
			]
		);

		acf_add_local_field_group(
			[
				'key'    => 'maisj_options',
				'title'  => __( 'Mai Sellers.json', 'mai-sellers-json' ),
				'style'  => 'seamless',
				'fields' => [
					[
						'key'      => 'maisj_message',
						'type'     => 'message',
						'message'  => sprintf( '<p>%s <a target="_blank" href="https://iabtechlab.com/wp-content/uploads/2019/07/Sellers.json_Final.pdf">%s.</a></p>', __( 'This is a custom options page for the', 'mai-sellers-json' ), __( 'IAB Tech Lab Sellers.json', 'mai-sellers-json' ) ),
						'esc_html' => 0,
					],
					[
						'label'    => __( 'Contact Address', 'mai-sellers-json' ),
						'key'      => 'maisj_contact_address',
						'name'     => 'maisj_contact_address',
						'type'     => 'text',
						'required' => 1,
					],
					[
						'label'    => __( 'Contact Email', 'mai-sellers-json' ),
						'key'      => 'maisj_contact_email',
						'name'     => 'maisj_contact_email',
						'type'     => 'email',
						'required' => 1,
					],
					[
						'label'         => __( 'Version', 'mai-sellers-json' ),
						'key'           => 'maisj_version',
						'name'          => 'maisj_version',
						'type'          => 'text',
						'required'      => 1,
						'default_value' => '1.0',
					],
					[
						'label'         => __( 'Identifiers', 'mai-sellers-json' ),
						'instructions'  => sprintf( '%s<br>%s', __( 'Add your identifiers here.', 'mai-sellers-json' ), __( 'Shift + Click the up/down arrow on the left to toggle open/closed.', 'mai-sellers-json' ) ),
						'key'           => 'maisj_identifiers',
						'name'          => 'maisj_identifiers',
						'type'          => 'repeater',
						'collapsed'     => 'maisj_identifier_name',
						'min'           => 0,
						'max'           => 0,
						'layout'        => 'block',
						'button_label'  => __( 'Add New Seller', 'mai-sellers-json' ),
						'sub_fields'    => [
							[
								'label'    => __( 'Name', 'mai-sellers-json' ),
								'key'      => 'maisj_identifier_name',
								'name'     => 'name',
								'type'     => 'text',
								'required' => 1,
								'wrapper'  => [
									'width' => '50',
								],
							],
							[
								'label'    => __( 'Value', 'mai-sellers-json' ),
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
						'label'         => __( 'Sellers', 'mai-sellers-json' ),
						'instructions'  => sprintf( '%s<br>%s', __( 'Add your sellers here. Name and Domain are required when Is Confidential field is unchecked.', 'mai-sellers-json' ), __( 'Shift + Click the up/down arrow on the left to toggle open/closed.', 'mai-sellers-json' ) ),
						'key'           => 'maisj_sellers',
						'name'          => 'maisj_sellers',
						'type'          => 'repeater',
						'collapsed'     => 'maisj_seller_name',
						'layout'        => 'block',
						'button_label'  => __( 'Add New Seller', 'mai-sellers-json' ),
						'sub_fields'    => [
							[
								'label'    => __( 'Name', 'mai-sellers-json' ) . ' *',
								'key'      => 'maisj_seller_name',
								'name'     => 'name',
								'type'     => 'text',
								'wrapper'  => [
									'width' => '50',
								],
							],
							[
								'label'    => __( 'Seller ID', 'mai-sellers-json' ),
								'key'      => 'maisj_seller_id',
								'name'     => 'seller_id',
								'type'     => 'text',
								'required' => 1,
								'wrapper'  => [
									'width' => '50',
								],
							],
							[
								'label'    => __( 'Domain', 'mai-sellers-json' ) . ' *',
								'key'      => 'maisj_seller_domain',
								'name'     => 'domain',
								'type'     => 'text',
								'wrapper'  => [
									'width' => '50',
								],
							],
							[
								'label'    => __( 'Seller Type', 'mai-sellers-json' ),
								'key'      => 'maisj_seller_type',
								'name'     => 'seller_type',
								'type'     => 'select',
								'required' => 1,
								'choices'  => [
									''             => __( 'Choose one', 'mai-sellers-json' ),
									'PUBLISHER'    => __( 'Publisher', 'mai-sellers-json' ),
									'INTERMEDIARY' => __( 'Intermediary', 'mai-sellers-json' ),
									'BOTH'         => __( 'Both', 'mai-sellers-json' ),
								],
								'wrapper'  => [
									'width' => '50',
								],
							],
							[
								'message'  => __( 'Is Confidential', 'mai-sellers-json' ),
								'key'      => 'maisj_seller_is_confidential',
								'name'     => 'is_confidential',
								'type'     => 'true_false',
								'wrapper'  => [
									'width' => '25',
								],
							],
							[
								'message'  => __( 'Is Passthrough', 'mai-sellers-json' ),
								'key'      => 'maisj_seller_is_passthrough',
								'name'     => 'is_passthrough',
								'type'     => 'true_false',
								'wrapper'  => [
									'width' => '25',
								],
							],
							[
								'placeholder' => __( 'Description for this inventory...', 'mai-sellers-json' ),
								'key'         => 'maisj_seller_comment',
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
							'value'    => 'mai-sellers-json',
						],
					],
				],
			]
		);

		// Encoder/Decoder.
		acf_add_local_field_group(
			[
				'key'    => 'maisj_encode_decode',
				'title'  => __( 'ID Encoder/Decoder', 'mai-sellers-json' ),
				'style'  => 'seamless',
				'fields' => [
					[
						'key'      => 'maisj_encode_decode_message',
						'type'     => 'message',
						'message'  => sprintf( '<h2 style="margin:0;padding:0;font-weight:bold;">%s</h2><p>%s</p>', __( 'ID Encoder/Decoder', 'mai-sellers-json' ), __( 'Encode or decode a publishers GAM Network Code', 'mai-sellers-json' ) ),
						'esc_html' => 0,
					],
					[
						'label' => __( 'Input', 'mai-sellers-json' ),
						'key'   => 'maisj_encode_decode_input',
						'name'  => 'maisj_encode_decode_input',
						'type'  => 'text',
					],
					[
						'label' => __( 'Output', 'mai-sellers-json' ),
						'key'   => 'maisj_encode_decode_output',
						'name'  => 'maisj_encode_decode_output',
						'type'  => 'text',
					],
					[
						'key'         => 'maisj_encode_decode_toggle',
						'name'        => 'maisj_encode_decode_toggle',
						'type'        => 'true_false',
						'ui'          => 1,
						'ui_on_text'  => __( 'Decode', 'mai-sellers-json' ),
						'ui_off_text' => __( 'Encode', 'mai-sellers-json' ),
					],
				],
				'position' => 'side',
				'location' => [
					[
						[
							'param'    => 'options_page',
							'operator' => '==',
							'value'    => 'mai-sellers-json',
						],
					],
				],
			]
		);
	}

	/**
	 * Gets inline admin CSS.
	 *
	 * @since 0.1.0
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

		#acf-maisj_encode_decode {
			box-sizing: border-box;
			padding: 24px;
			background: rgba(0, 0, 0, 0.05);
		}

		#acf-maisj_encode_decode .acf-field-maisj-encode-decode-message .acf-label {
			display: none;
		}

		#acf-maisj_encode_decode > .inside {
			margin: 0 !important;
		}

		#acf-maisj_encode_decode .acf-fields > .acf-field {
			padding: 0;
		}

		#acf-maisj_encode_decode .acf-field-maisj-encode-decode-output {
			margin-block: 16px;
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
				'maisj_seller_id'              => isset( $values['seller_id'] ) ? sanitize_text_field( $values['seller_id'] ) : '',
				'maisj_seller_name'            => isset( $values['name'] ) ? sanitize_text_field( $values['name'] ) : '',
				'maisj_seller_domain'          => isset( $values['domain'] ) ? $this->get_url_host( $values['domain'] ) : '',
				'maisj_seller_type'            => isset( $values['seller_type'] ) ? sanitize_text_field( $values['seller_type'] ) : '',
				'maisj_seller_is_confidential' => isset( $values['is_confidential'] ) ? rest_sanitize_boolean( $values['is_confidential'] ) : 0,
				'maisj_seller_is_passthrough'  => isset( $values['is_passthrough'] ) ? rest_sanitize_boolean( $values['is_passthrough'] ) : 0,
				'maisj_seller_comment'         => isset( $values['comment'] ) ? sanitize_text_field( $values['comment'] ) : '',
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

	/**
	 * Validates seller name and domain are required.
	 *
	 * @param $valid (mixed)  Whether or not the value is valid (boolean) or a custom error message (string).
	 * @param $value (mixed)  The field value.
	 * @param $field (array)  The field array containing all settings.
	 * @param $input (string) The field DOM element name attribute.
	 *
	 * @return mixed
	 */
	function validate_seller_name_domain( $valid, $value, $field, $input ) {
		if ( ! $valid ) {
			return $valid;
		}

		// Get name.
		switch ( $field['key'] ) {
			case 'maisj_seller_name':
				$name = __( 'Name', 'mai-sellers-json' );
			break;
			case 'maisj_seller_domain':
				$name = __( 'Domain', 'mai-sellers-json' );
			break;
			default:
				return $valid;

		}

		// Start counts.
		static $counts = [
			'maisj_seller_name'   => -1,
			'maisj_seller_domain' => -1,
		];

		// Increment.
		$counts[ $field['key'] ]++;

		$sellers = $_POST['acf']['maisj_sellers'];
		$current = isset( $sellers[ "row-{$counts[ $field['key'] ]}" ] ) ? $sellers[ "row-{$counts[ $field['key'] ]}" ] : null;

		if ( ! $current ) {
			return $valid;
		}

		$confidential = isset( $current['maisj_seller_is_confidential'] ) ? rest_sanitize_boolean( $current['maisj_seller_is_confidential'] ) : 0;

		if ( $confidential && ! $value ) {
			return $name . ' ' . __( 'is required when "Is Confidential" field is checked.', 'mai-sellers-json' );
		}

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
		if ( ! $screen || false === strpos( $screen->id, 'mai-sellers-json' ) ) {
			return;
		}

		// Set data var.
		$data  = [
			'contact_email'   => sanitize_text_field( get_field( 'maisj_contact_email', 'option' ) ),
			'contact_address' => sanitize_text_field( get_field( 'maisj_contact_address', 'option' ) ),
			'version'         => sanitize_text_field( get_field( 'maisj_version', 'option' ) ),
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
			'options_maisj_encode_decode_message',
			'options_maisj_encode_decode_input',
			'options_maisj_encode_decode_output',
			'options_maisj_encode_decode_toggle',
			'_options_maisj_contact_address',
			'_options_maisj_contact_email',
			'_options_maisj_version',
			'_options_maisj_identifiers',
			'_options_maisj_sellers',
			'_options_maisj_encode_decode_message',
			'_options_maisj_encode_decode_input',
			'_options_maisj_encode_decode_output',
			'_options_maisj_encode_decode_toggle',
		];

		// Delete remaining options manually.
		foreach ( $options as $option ) {
			delete_option( $option );
		}

		// Get values.
		$array = get_option( 'mai_sellers_json' );

		// JSON encode and write to sellers.json file.
		$json = json_encode( $array, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		file_put_contents( $this->path, $json );
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

		$actions['settings'] = $this->get_settings_link( __( 'Settings', 'mai-sellers-json' ) );

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

add_action('admin_menu', 'custom_sidebar_meta_box_og');

function custom_sidebar_meta_box_og() {
    add_meta_box(
        'custom-sidebar-box',  // Unique ID
        'Custom Sidebar Content',  // Box title
        'render_custom_sidebar_content_og',  // Callback function to display the content
        'acf-options-mai-sellers-json',  // ACF options page name (menu slug)
        'side',  // Context (right sidebar)
        'default'  // Priority
    );
}

function render_custom_sidebar_content_og($post) {
    // Content you want to display in the custom meta box
    echo 'Your custom content goes here.';
}

add_action( 'add_meta_boxes', 'custom_sidebar_meta_box');
function custom_sidebar_meta_box() {
    // Make sure the ACF options page exists
    if (!function_exists('acf_add_options_sub_page')) {
        return;
    }

    // Add the meta box to the ACF options page
    add_meta_box(
        'custom-sidebar-box', // Unique ID
        'Custom Sidebar Content', // Box title
        'render_custom_sidebar_content', // Callback function to display the content
		'acf-options-mai-sellers-json', // ACF options page name
        'side', // Context (right sidebar)
        'high' // Priority
    );
}

// Render the custom meta box content
function render_custom_sidebar_content( $post ) {
    // Content you want to display in the custom meta box
    echo 'Your custom content goes here.';
}
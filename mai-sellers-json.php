<?php

/**
 * Plugin Name:     Mai Sellers.json
 * Plugin URI:      https://bizbudding.com
 * Description:     Manage your sellers.json file for advertising.
 * Version:         0.1.4
 *
 * Author:          BizBudding
 * Author URI:      https://bizbudding.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

// Must be at the top of the file.
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * Main Mai_Sellers_JSON_Plugin Class.
 *
 * @since 0.1.0
 */
final class Mai_Sellers_JSON_Plugin {

	/**
	 * @var   Mai_Sellers_JSON_Plugin The one true Mai_Sellers_JSON_Plugin
	 * @since 0.1.0
	 */
	private static $instance;

	/**
	 * Main Mai_Sellers_JSON_Plugin Instance.
	 *
	 * Insures that only one instance of Mai_Sellers_JSON_Plugin exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since   0.1.0
	 * @static  var array $instance
	 * @uses    Mai_Sellers_JSON_Plugin::setup_constants() Setup the constants needed.
	 * @uses    Mai_Sellers_JSON_Plugin::includes() Include the required files.
	 * @uses    Mai_Sellers_JSON_Plugin::hooks() Activate, deactivate, etc.
	 * @see     Mai_Sellers_JSON_Plugin()
	 * @return  object | Mai_Sellers_JSON_Plugin The one true Mai_Sellers_JSON_Plugin
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			// Setup the setup.
			self::$instance = new Mai_Sellers_JSON_Plugin;
			// Methods.
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->hooks();
		}
		return self::$instance;
	}

	/**
	 * Throw error on object clone.
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since   0.1.0
	 * @access  protected
	 * @return  void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'textdomain' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class.
	 *
	 * @since   0.1.0
	 * @access  protected
	 * @return  void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden.
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'textdomain' ), '1.0' );
	}

	/**
	 * Setup plugin constants.
	 *
	 * @access  private
	 * @since   0.1.0
	 * @return  void
	 */
	private function setup_constants() {
		// Plugin version.
		if ( ! defined( 'MAI_SELLERS_JSON_VERSION' ) ) {
			define( 'MAI_SELLERS_JSON_VERSION', '0.1.4' );
		}

		// Plugin Folder Path.
		if ( ! defined( 'MAI_SELLERS_JSON_DIR' ) ) {
			define( 'MAI_SELLERS_JSON_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL.
		if ( ! defined( 'MAI_SELLERS_JSON_URL' ) ) {
			define( 'MAI_SELLERS_JSON_URL', plugin_dir_url( __FILE__ ) );
		}
	}

	/**
	 * Include required files.
	 *
	 * @access  private
	 * @since   0.1.0
	 * @return  void
	 */
	private function includes() {
		// Include vendor libraries.
		require_once __DIR__ . '/vendor/autoload.php';

		// Includes.
		foreach ( glob( MAI_SELLERS_JSON_DIR . 'includes/*.php' ) as $file ) { include $file; }

		if ( is_admin() ) {
			include __DIR__ . '/classes/class-settings.php';
		}
	}

	/**
	 * Run the hooks.
	 *
	 * @since   0.1.0
	 * @return  void
	 */
	public function hooks() {
		add_action( 'plugins_loaded', [ $this, 'updater' ] );
		add_action( 'plugins_loaded', [ $this, 'classes' ] );
	}

	/**
	 * Setup the updater.
	 *
	 * composer require yahnis-elsts/plugin-update-checker
	 *
	 * @since 0.1.0
	 *
	 * @uses https://github.com/YahnisElsts/plugin-update-checker/
	 *
	 * @return void
	 */
	public function updater() {
		// Bail if plugin updater is not loaded.
		if ( ! class_exists( 'YahnisElsts\PluginUpdateChecker\v5\PucFactory' ) ) {
			return;
		}

		// Setup the updater.
		$updater = PucFactory::buildUpdateChecker( 'https://github.com/maithemewp/mai-sellers-json/', __FILE__, 'mai-sellers-json' );

		// Set the branch that contains the stable release.
		$updater->setBranch( 'main' );

		// Maybe set github api token.
		if ( defined( 'MAI_GITHUB_API_TOKEN' ) ) {
			$updater->setAuthentication( MAI_GITHUB_API_TOKEN );
		}

		// Add icons for Dashboard > Updates screen.
		if ( function_exists( 'mai_get_updater_icons' ) && $icons = mai_get_updater_icons() ) {
			$updater->addResultFilter(
				function ( $info ) use ( $icons ) {
					$info->icons = $icons;
					return $info;
				}
			);
		}
	}

	/**
	 * Loads classes.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function classes() {
		if ( is_admin() ) {
			$settings = new Mai_Sellers_JSON_Settings;
		}
	}
}

/**
 * The main function for that returns Mai_Sellers_JSON_Plugin
 *
 * The main function responsible for returning the one true Mai_Sellers_JSON_Plugin
 * Instance to functions everywhere.
 *
 * @since 0.1.0
 *
 * @return object|Mai_Sellers_JSON_Plugin The one true Mai_Sellers_JSON_Plugin Instance.
 */
function mai_sellers_json_plugin() {
	return Mai_Sellers_JSON_Plugin::instance();
}

// Get Mai_Sellers_JSON_Plugin Running.
mai_sellers_json_plugin();

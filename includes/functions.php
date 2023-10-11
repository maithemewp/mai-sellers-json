<?php

/**
 * Gets a single option value by key.
 *
 * @since 0.1.0
 *
 * @param string $key
 * @param mixed  $default
 *
 * @return mixed
 */
function maisj_get_value( $key, $default = null ) {
	$options = maisj_get_values();
	return isset( $options[ $key ] ) ? $options[ $key ] : $default;
}

/**
 * Gets all options.
 *
 * @since 0.1.0
 *
 * @return array
 */
function maisj_get_values() {
	static $cache = null;

	if ( ! is_null( $cache ) ) {
		return $cache;
	}

	if ( file_exists( get_home_path() . 'sellers.json' ) ) {
		$cache = json_decode( file_get_contents( get_home_path() . 'sellers.json' ), true );
	} else {
		$cache = (array) get_option( 'mai_settings_json', [] );
	}

	return $cache;
}

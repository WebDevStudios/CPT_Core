<?php
/*
Plugin Name: WDS CPT Core
Plugin URI: http://webdevstudios.com
Description: CPT registration starter class
Version: 1.0.3
Author: WebDevStudios.com
Author URI: http://webdevstudios.com
License: GPLv2
Domain: cpt-core
Path: languages
*/

/**
 * Loader versioning: http://jtsternberg.github.io/wp-lib-loader/
 */

if ( ! class_exists( 'CPT_Core_011', false ) ) {

	/**
	 * Versioned loader class-name
	 *
	 * This ensures each version is loaded/checked.
	 *
	 * @category WordPressLibrary
	 * @package  CPT_Core
	 * @author   WebDevStudios <contact@webdevstudios.com>
	 * @license  GPL-2.0+
	 * @version  1.0.3
	 * @link     https://webdevstudios.com
	 * @since    1.0.3
	 */
	class CPT_Core_011 {

		/**
		 * CPT_Core version number
		 * @var   string
		 * @since 1.0.3
		 */
		const VERSION = '1.0.3';

		/**
		 * Current version hook priority.
		 * Will decrement with each release
		 *
		 * @var   int
		 * @since 1.0.3
		 */
		const PRIORITY = 9998;

		/**
		 * Starts the version checking process.
		 * Creates CPT_CORE_LOADED definition for early detection by
		 * other scripts.
		 *
		 * Hooks CPT_Core inclusion to the cpt_core_load hook
		 * on a high priority which decrements (increasing the priority) with
		 * each version release.
		 *
		 * @since 1.0.3
		 */
		public function __construct() {
			if ( ! defined( 'CPT_CORE_LOADED' ) ) {
				/**
				 * A constant you can use to check if CPT_Core is loaded
				 * for your plugins/themes with CPT_Core dependency.
				 *
				 * Can also be used to determine the priority of the hook
				 * in use for the currently loaded version.
				 */
				define( 'CPT_CORE_LOADED', self::PRIORITY );
			}

			// Use the hook system to ensure only the newest version is loaded.
			add_action( 'cpt_core_load', array( $this, 'include_lib' ), self::PRIORITY );

			/*
			 * Hook in to the first hook we have available and
			 * fire our `cpt_core_load' hook.
			 */
			add_action( 'muplugins_loaded', array( __CLASS__, 'fire_hook' ), 9 );
			add_action( 'plugins_loaded', array( __CLASS__, 'fire_hook' ), 9 );
			add_action( 'after_setup_theme', array( __CLASS__, 'fire_hook' ), 9 );
		}

		/**
		 * Fires the cpt_core_load action hook.
		 *
		 * @since 1.0.3
		 */
		public static function fire_hook() {
			if ( ! did_action( 'cpt_core_load' ) ) {
				// Then fire our hook.
				do_action( 'cpt_core_load' );
			}
		}

		/**
		 * A final check if CPT_Core exists before kicking off
		 * our CPT_Core loading.
		 *
		 * CPT_CORE_VERSION and CPT_CORE_DIR constants are
		 * set at this point.
		 *
		 * @since  1.0.3
		 */
		public function include_lib() {
			if ( class_exists( 'CPT_Core', false ) ) {
				return;
			}

			if ( ! defined( 'CPT_CORE_VERSION' ) ) {
				/**
				 * Defines the currently loaded version of CPT_Core.
				 */
				define( 'CPT_CORE_VERSION', self::VERSION );
			}

			if ( ! defined( 'CPT_CORE_DIR' ) ) {
				/**
				 * Defines the directory of the currently loaded version of CPT_Core.
				 */
				define( 'CPT_CORE_DIR', dirname( __FILE__ ) . '/' );
			}

			// Include and initiate CPT_Core.
			require_once CPT_CORE_DIR . 'lib/init.php';
		}

	}

	// Kick it off.
	new CPT_Core_011;
}

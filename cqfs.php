<?php
/*
Plugin Name: Classic Quiz Feedback Survey
Plugin URI: https://github.com/amitbiswas06/classic-quiz-feedback-survey
Description: It's a classic plugin for quiz app, feedback or survey. Advanced custom fields plugin is required.
Version: 1.0.0
Author: Amit Biswas
Author URI: https://templateartist.com
License: GPLv2 and later
Text Domain: cqfs
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Main Classic Quiz Feedback Survey Class
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 1.0.0
 */
class CQFS {

	/**
	 * Plugin Version
	 *
	 * @since 1.0.0
	 *
	 * @var string The plugin version.
	 */
	const CQFS_VERSION = '1.0.0';

	/**
	 * Minimum ACF Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum ACF version required to run the plugin.
	 */
	const MINIMUM_ACF_VERSION = '5.8.12';

	/**
	 * Minimum PHP Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum PHP version required to run the plugin.
	 */
	const MINIMUM_PHP_VERSION = '7.4.0';

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @static
	 *
	 * @var CQFS The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @return CQFS An instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function __construct() {

		add_action( 'init', [ $this, 'i18n' ] );
		add_action( 'plugins_loaded', [ $this, 'init' ] );
		//$this->required_files();

	}

	/**
	 * Load Textdomain
	 *
	 * Load plugin localization files.
	 *
	 * Fired by `init` action hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function i18n() {

		load_plugin_textdomain( 'cqfs' );

	}

	/**
	 * Initialize the plugin
	 *
	 * Load the plugin only after ACF (and other plugins) are loaded.
	 * Checks for basic plugin requirements, if one check fail don't continue,
	 * if all check have passed load the files required to run the plugin.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init() {

		// Check if ACF installed and activated
		if ( ! class_exists('ACF') ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
			return;
		}

		// Check for required ACF version
		if ( ! version_compare( ACF_VERSION, self::MINIMUM_ACF_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_MINIMUM_ACF_VERSION' ] );
			return;
		}

		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return;
		}

		require __DIR__ . '/inc/cpt.php';
		require __DIR__ . '/inc/roles.php';
		register_activation_hook( __FILE__, [ $this, 'rewrite_flush' ] );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have ACF installed or activated.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_missing_main_plugin() {

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
			/* translators: 1: Plugin name 2: ACF */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'cqfs' ),
			'<strong>' . esc_html__( 'Classic Quiz Feedback Survey', 'cqfs' ) . '</strong>',
			'<strong>' . esc_html__( 'Advanced Custom Fields', 'cqfs' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required ACF version.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_MINIMUM_ACF_VERSION() {

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
			/* translators: 1: Plugin name 2: ACF 3: Required ACF version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'cqfs' ),
			'<strong>' . esc_html__( 'Classic Quiz Feedback Survey', 'cqfs' ) . '</strong>',
			'<strong>' . esc_html__( 'Advanced Custom Fields', 'cqfs' ) . '</strong>',
			 self::MINIMUM_ACF_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_php_version() {

		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'cqfs' ),
			'<strong>' . esc_html__( 'Classic Quiz Feedback Survey', 'cqfs' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'cqfs' ) . '</strong>',
			 self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}


	/**
	 * Init Styles
	 *
	 * Register style files
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	private function rewrite_flush() {
		// First, we "add" the custom post types via the create_cpt.php.
		
		cqsf_quiz();

		// ATTENTION: This is *only* done during plugin activation hook!
		// You should *NEVER EVER* do this on every page load!!
		flush_rewrite_rules();
	}
//register_activation_hook( __FILE__, 'xgenie__rewrite_flush' );

}

CQFS::instance();
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
		require __DIR__ . '/inc/cpt.php';
		require __DIR__ . '/inc/roles.php';
        register_activation_hook( __FILE__, array( 'Cqfs_Roles', 'add_caps_admin' ) );
		register_deactivation_hook( __FILE__, array( 'Cqfs_Roles', 'remove_caps_admin' ) );
		
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

		//ACF save point
		//must be removed in distribution
		add_filter('acf/settings/save_json', [ $this, 'acf_save_point' ] );

		//ACF load point
		add_filter('acf/settings/load_json', [ $this, 'acf_load_point' ] );

		//shortcode display metabox
		add_action('add_meta_boxes', [ $this, 'display_shortcode_metabox' ]);

		//admin columns
		require __DIR__ . '/inc/admin-columns.php';

		//build shortcode
		require __DIR__ . '/inc/shortcode.php';

		//enqueue scripts
		add_action('wp_enqueue_scripts', [$this, 'cqfs_enqueue_scripts']);


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

	public function acf_save_point( $path ) {
    
		// update path
		$path = plugin_dir_path( __FILE__ ) . 'assets/acf-fields';
		
		// return
		return $path;
		
	}

	public function acf_load_point( $paths ) {
    
		// remove original path (optional)
		unset($paths[0]);
		
		// append path
		$paths[] = plugin_dir_path( __FILE__ ) . 'assets/acf-fields';
		
		// return
		return $paths;
		
	}

	public function display_shortcode_metabox(){
		$screens = ['cqfs_build'];
		foreach ($screens as $screen) {
			add_meta_box(
				'cqfs_shortcode',
				esc_html__('Build Type Shortcode', 'cqfs'),
				[ $this, 'cqfs_shortcode_metabox_html' ],
				$screen
			);
		}
	}

	public function cqfs_shortcode_metabox_html($post){

		printf(
			'<div class="acf-field"><div class="acf-input-wrap">
			<input type="text" readonly value="[cqfs id=%s]"></div></div>',
			esc_attr( $post->ID )
		);
	}

	public function cqfs_enqueue_scripts(){
		
		//for multi page question
		wp_enqueue_script(
			'cqfs-multi', 
			esc_url( plugin_dir_url(__FILE__) . 'assets/js/cqfs-multi.js'),
			NULL,
			'1.0.0',
			true
		);

		//style css
		wp_enqueue_style(
			'cqfs-style',
			esc_url( plugin_dir_url(__FILE__) . 'assets/css/cqfs-styles.css'),
			NULL,
			'1.0.0'
		);

	}


}//class CQFS end

CQFS::instance();
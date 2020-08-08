<?php
/*
Plugin Name: Classic Quiz Feedback Survey
Plugin URI: https://github.com/amitbiswas06/classic-quiz-feedback-survey
Description: It's a classic plugin for quiz app, feedback or survey.
Version: 1.0.0
Author: Amit Biswas
Author URI: https://templateartist.com
License: GPLv2 and later
Text Domain: cqfs
Domain Path: /languages/
*/

//define namespace
namespace CQFS\ROOT;

define( 'CQFS_PATH', plugin_dir_path( __FILE__ ) );

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
	 * Minimum PHP Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum PHP version required to run the plugin.
	 */
	const MINIMUM_PHP_VERSION = '7.4.0';

	// define( 'MY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

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

		//register custom post type
		require CQFS_PATH . 'inc/cpt.php';

		//add custom post type capabilities to admin
		require CQFS_PATH . 'inc/roles.php';
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
	 * Load the plugin only after checking.
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

		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return;
		}

		//admin menu pages
		require CQFS_PATH . 'admin/menu-pages.php';

		//cqfs_question metaboxes
		require CQFS_PATH . 'admin/meta-boxes/metabox-question.php';

		//cqfs_build metaboxes
		require CQFS_PATH . 'admin/meta-boxes/metabox-build.php';

		//cqfs_entry metaboxes
		require CQFS_PATH . 'admin/meta-boxes/metabox-entry.php';

		//admin columns
		require CQFS_PATH . 'inc/admin-columns.php';

		//admin scripts
		require CQFS_PATH . 'admin/admin-scripts.php';

		//utility class object
		require CQFS_PATH . 'inc/utilities.php';

		//build shortcode
		require CQFS_PATH . 'inc/shortcode.php';

		//enqueue scripts to front
		add_action('wp_enqueue_scripts', [$this, 'cqfs_enqueue_scripts']);

		// add login form for CQFS use
		add_action( 'wp_footer', [ 'CQFS\INC\UTIL\Utilities', 'cqfs_login_submit_form'] );

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


	public function cqfs_enqueue_scripts(){
		
		//for all types of CQFS form
		wp_enqueue_script(
			'cqfs-multi', 
			esc_url( plugin_dir_url(__FILE__) . 'assets/js/cqfs-multi.js'),
			'NULL',
			self::CQFS_VERSION,
			true
		);

		//localize script for front end
		wp_localize_script( 'cqfs-multi', '_cqfs',
			array( 
				'ajaxurl'		=> esc_url( admin_url( 'admin-ajax.php' ) ),
				'login_status'	=> is_user_logged_in(),
				'form_handle'	=> esc_attr( get_option('_cqfs_form_handle') ),
			)
		);

		//for localization of string in JS use in front
		$cqfs_thank_msg = apply_filters('cqfs_thankyou_message', esc_html__('Thank you for participating.', 'cqfs'));
		$invalid_result = apply_filters('cqfs_invalid_result', esc_html__('Invalid Result','cqfs'));
		$you_ans = apply_filters('cqfs_result_you_answered', esc_html__('You answered&#58; ', 'cqfs'));
		$status = apply_filters('cqfs_result_ans_status', esc_html__('Status&#58; ', 'cqfs'));
		$note = apply_filters('cqfs_result_ans_note', esc_html__('Note&#58; ', 'cqfs'));

		//localize script for JS strings
		wp_localize_script( 'cqfs-multi', '_cqfs_lang',
			array( 
				'thank_msg'		=> esc_html( $cqfs_thank_msg ),
				'invalid_result'=> esc_html($invalid_result),
				'you_ans'		=> esc_html($you_ans),
				'status'		=> esc_html($status),
				'note'			=> esc_html($note),
			)
		);


		//style css enqueue for front end
		wp_enqueue_style(
			'cqfs-style',
			esc_url( plugin_dir_url(__FILE__) . 'assets/css/cqfs-styles.css'),
			NULL,
			self::CQFS_VERSION
		);

	}


}//class CQFS end

CQFS::instance();
<?php
/**
 * Custom scripts for CQFS admin
 * @since 1.0.0
 */

//define namespaces
namespace CQFS\ADMIN\SCRIPTS;
use CQFS\INC\UTIL\Utilities as util;
use CQFS\ROOT\CQFS as CQFS;

class Scripts {

    /**
     * constructor
     */
    public function __construct(){

		//admin enqueue scripts
		add_action('admin_enqueue_scripts', [$this, 'cqfs_admin_css']);

		//admin enqueue scripts
		add_action('admin_enqueue_scripts', [$this, 'cqfs_admin_scripts']);

	}

	public function cqfs_admin_css(){
		// enqueue styles
		wp_enqueue_style(
			'cqfs-admin-style', 
			plugin_dir_url(__FILE__) . 'css/cqfs-admin-style.css', 
			array(), 
			CQFS::CQFS_VERSION,
			'all'
		);
	}

	/**
	 * admin scripts and localize
	 */
	public function cqfs_admin_scripts() {

		//grab the current post ID
		$post_id = filter_input(INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT);

		//grab "action=edit" screen
		$action = filter_input(INPUT_GET, 'action', FILTER_DEFAULT);
		
		// get current admin screen, or null
		$screen = get_current_screen();

		// verify admin screen object
		if ( is_object( $screen ) ) {
			// enqueue only for specific post types and admin pages of CQFS
			if ( in_array( $screen->post_type, 
			['cqfs_entry','cqfs_question','cqfs_build'] ) ||
			$screen->base === 'toplevel_page_cqfs-settings' ) {

				//set only for cqfs_entry edit screen
				if( $screen->post_type === 'cqfs_entry' && isset($post_id) && isset($action) && $action === 'edit' ){
					$cqfs_entry_form_type = util::cqfs_entry_obj($post_id)['form_type'];
					$edit_page = $action;
				}elseif( $screen->post_type === 'cqfs_entry' && $screen->action === 'add'){
					//set add new page
					$cqfs_entry_form_type = '';
					$edit_page = esc_html($screen->action);
				}else{
					//all other page
					$cqfs_entry_form_type = '';
					$edit_page = '';
				}

				// enqueue script
				wp_enqueue_script(
					'cqfs_admin_script', 
					plugin_dir_url(__FILE__) . 'js/cqfs-admin.js', 
					NULL, 
					CQFS::CQFS_VERSION, 
					true
				);
				// localize script, create a custom js object
				wp_localize_script(
					'cqfs_admin_script',
					'cqfs_admin_obj',
					[
						'ajax_url' 		=> esc_url(admin_url('admin-ajax.php')),
						'post_type'		=> esc_html( $screen->post_type ),
						'base'			=> esc_html( $screen->base ),
						'entry_type'	=> esc_html( $cqfs_entry_form_type ),//only for cqfs_entry
						'action'		=> esc_html( $edit_page ),//only for cqfs_entry
						'err_msg'		=> esc_html__('Required field contains invalid entry.','cqfs'),
						'require_msg'	=> esc_html__('Required field value cannot be null.','cqfs'),
					]
				);
			}
		}
		
	}


}

$scripts = new Scripts;
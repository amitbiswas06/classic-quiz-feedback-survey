<?php
/**
 * Custom metaboxes for few screens in admin
 * @since 1.0.0
 */

//define namespaces
namespace CQFS\ADMIN\METABOXES;
use CQFS\INC\UTIL\Utilities as util;

class MetaBoxes {

    /**
     * constructor
     */
    public function __construct(){

		//admin scripts
		add_action('admin_enqueue_scripts', [$this, 'cqfs_admin_scripts']);

        //CPT - cqfs_build
        add_action('add_meta_boxes', [ $this, 'cqfs_build_metaboxes' ]);
        
        //CPT - cqfs_entry
		add_action('add_meta_boxes', [ $this, 'cqfs_entry_metaboxes' ]);

    }


    /**
     * CPT - cqfs_build
     */
    public function cqfs_build_metaboxes(){
		$screens = ['cqfs_build'];
		foreach ($screens as $screen) {
			add_meta_box(
				'cqfs_build_shortcode',
				esc_html__('Build Type Shortcode', 'cqfs'),
				[ $this, 'cqfs_build__shortcode_metabox' ],
				$screen
			);
		}
	}

	public function cqfs_build__shortcode_metabox($post){

		printf(
			'<div class="acf-field"><div class="acf-input-wrap">
			<input type="text" readonly value="[cqfs id=%s]"></div></div>',
			esc_attr( $post->ID )
		);
    }
    
    /*********** end cqfs_build ***********/

    /**
     * CPT - cqfs_entry
     */
    public function cqfs_entry_metaboxes(){
		$screens = ['cqfs_entry'];
		foreach ($screens as $screen) {
			
			//edit buttons
			add_meta_box(
				'cqfs_entry_edit_btn',
				esc_html__('Edit This Entry', 'cqfs'),
				[ $this, 'cqfs_build__edit_btn_metabox' ],
                $screen,
                'side'
			);

			//email buttons
			add_meta_box(
				'cqfs_entry_email_btn',
				esc_html__('Email Options', 'cqfs'),
				[ $this, 'cqfs_build__email_btn_metabox' ],
                $screen,
                'side'
			);
		}
	}

	public function cqfs_build__edit_btn_metabox($post){

		printf(
			'<div class="cqfs-metabox">
				<button id="cqfs-entry-enable" class="button button-primary button-large">%s</button>
				<button id="cqfs-entry-disable" class="button button-secondary button-large">%s</button>
			</div>',
			esc_html__('Enable', 'cqfs'),
			esc_html__('Disable', 'cqfs')
		);
	}
	
	public function cqfs_build__email_btn_metabox($post){

		printf(
			'<div class="cqfs-metabox">
				<button id="cqfs-entry-email-admin" class="button button-secondary button-large">%s</button>
				<button id="cqfs-entry-email-user" class="button button-secondary button-large">%s</button>
			</div>',
			esc_html__('Email to admin', 'cqfs'),
			esc_html__('Email to user', 'cqfs')
		);
    }


	/**
	 * admin enqueue scripts
	 */
	public function cqfs_admin_scripts() {

		//grab the current post ID
		$post_id = filter_input(INPUT_GET, 'post', FILTER_SANITIZE_NUMBER_INT);

		//grab "action=edit" screen
		$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
		
		// get current admin screen, or null
		$screen = get_current_screen(); 
		// var_dump($screen);

		// verify admin screen object
		if ( is_object( $screen ) ) {
			// enqueue only for specific post types
			if ( in_array( $screen->post_type, ['cqfs_entry'] ) ) {

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
				wp_enqueue_script('cqfs_admin_script', plugin_dir_url(__FILE__) . 'js/cqfs-admin.js', NULL, '1.0.0', true);
				// localize script, create a custom js object
				wp_localize_script(
					'cqfs_admin_script',
					'cqfs_admin_obj',
					[
						'url' 			=> admin_url('admin-ajax.php'),
						'post_type'		=> esc_html( $screen->post_type ),
						'entry_type'	=> esc_html( $cqfs_entry_form_type ),//only for cqfs_entry
						'action'		=> esc_html( $edit_page ),//only for cqfs_entry
					]
				);
			}
		}
		
	}



}

$meta_boxes = new MetaBoxes;
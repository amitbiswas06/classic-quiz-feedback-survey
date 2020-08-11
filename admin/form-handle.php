<?php
/**
 * Form handle class for CQFS admin settings pages
 * Handled by `admin-post.php`
 * @since 1.0.0
 */

//define namespaces
namespace CQFS\ADMIN\FORMHANDLE;

class FormHandle {

    // admin settings form nonce
    const SETTINGS_NONCE = 'cqfs_admin_settings';

    // this will store post variables
    protected $values;

    // failure url arguments
    protected $failure_args;

    // success url arguments
    protected $success_args;

    // failure url
    protected $failure_url;

    // success url
    protected $success_url;

    public function __construct() {

        //sanitize the global POST var. XSS ok.
		//all form inputs and security inputs
        $this->values = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        // prepare failure url args
        $this->failure_args = [
            'page'          => urlencode(sanitize_text_field('cqfs-settings')),
            '_cqfs_status'  => urlencode(sanitize_text_field('update-failed')),
        ];

        // prepare success url args
        $this->success_args = [
            'page'          => urlencode(sanitize_text_field('cqfs-settings')),
            '_cqfs_status'  => urlencode(sanitize_text_field('settings-updated')),
        ];

        // check if we are at admin-post.php or not
        if( isset( $this->values['_wp_http_referer'] ) && !empty( $this->values['_wp_http_referer'] ) ){
            // set failure url
            $this->failure_url = esc_url_raw(
                add_query_arg( $this->failure_args, wp_unslash( esc_url( strtok( $this->values['_wp_http_referer'], '?') ) ) )
            );

            // set success url
            $this->success_url = esc_url_raw(
                add_query_arg( $this->success_args, wp_unslash( esc_url( strtok( $this->values['_wp_http_referer'], '?') ) ) )
            );
        }else{
            $this->failure_url = "";
            $this->success_url = "";
        }

        //hooks goes here
        //Authenticated action for the CQFS form. action value `cqfs_response`
        add_action( 'admin_post_cqfs_admin_response', [$this, 'admin_submission'] );//php req
    
    }


    /**
     * Submission to `admin-post.php`
     * Validate and return
     */
    public function admin_submission(){

        // check nonce
		if ( !isset( $this->values['_cqfs_admin_nonce'] ) || !wp_verify_nonce( $this->values['_cqfs_admin_nonce'], self::SETTINGS_NONCE )){
			//failure return
			wp_safe_redirect( $this->failure_url );

            //exit immediately
			exit();
		}

		// Check the user's permissions.
        if ( ! current_user_can( 'manage_options' ) ) {
            //failure return
			wp_safe_redirect( $this->failure_url );

            //exit immediately
			exit();
        }

        /**************************************************************/
        // run if nonce and user permission is ok

        // check if key exists and get then set the value
        if( array_key_exists('_cqfs', $this->values ) ){

            // received value of form-handle
            $form_handle_mode = sanitize_text_field( $this->values['_cqfs']['form-handle'] );
            // received value of allow-all
            $allow_guest = rest_sanitize_boolean( $this->values['_cqfs']['allow-all'] );

            // updates and stores form-handle
            if( $form_handle_mode ){
                update_option('_cqfs_form_handle', $form_handle_mode );
            }

            // update allow-all
            update_option('_cqfs_allow_all', $allow_guest );


            //success return
            wp_safe_redirect( $this->success_url );

        }else{
            //failure return
            wp_safe_redirect( $this->failure_url );
        }
        
        //exit immediately
        exit();

    }

}

// instanciate if admin
if( is_admin() ){
    $form_handle = new FormHandle;
}

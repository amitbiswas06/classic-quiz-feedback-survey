<?php
/**
 * Form handle class for CQFS admin settings pages
 * Handled by `admin-post.php`
 * @since 1.0.0
 */

//define namespaces
namespace CQFS\ADMIN\FORMHANDLE;

class FormHandle {

    const SETTINGS_NONCE = 'cqfs_admin_settings';
    protected $values;
    protected $failure_args;
    protected $success_args;

    public function __construct() {

        //sanitize the global POST var. XSS ok.
		//all form inputs and security inputs
        $this->values = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        $this->failure_args = [
            'page'          => urlencode(sanitize_text_field('cqfs-settings')),
            '_cqfs_status'  => urlencode(sanitize_text_field('update-failed')),
        ];

        $this->success_args = [
            'page'          => urlencode(sanitize_text_field('cqfs-settings')),
            '_cqfs_status'  => urlencode(sanitize_text_field('settings-updated')),
        ];

        //hooks goes here
        //Authenticated action for the CQFS form. action value `cqfs_response`
        add_action( 'admin_post_cqfs_admin_response', [$this, 'admin_submission'] );//php req
    
    }


    /**
     * Submission to `admin-post.php`
     * Validate and return
     */
    public function admin_submission(){

        $failure_url = esc_url_raw(
            add_query_arg( $this->failure_args, wp_unslash( esc_url( strtok( $this->values['_wp_http_referer'], '?') ) ) )
        );

        $success_url = esc_url_raw(
            add_query_arg( $this->success_args, wp_unslash( esc_url( strtok( $this->values['_wp_http_referer'], '?') ) ) )
        );

        // check nonce
		if ( !isset( $this->values['_cqfs_admin_nonce'] ) || !wp_verify_nonce( $this->values['_cqfs_admin_nonce'], self::SETTINGS_NONCE )){
			//failure return
			wp_safe_redirect( $failure_url );

            //exit immediately
			exit();
		}

		// Check the user's permissions.
        if ( ! current_user_can( 'manage_options' ) ) {
            //failure return
			wp_safe_redirect( $failure_url );

            //exit immediately
			exit();
        }

        /**************************************************************/
        // run if nonce, user permission is ok
        
        // set blank on purpose of error free while updating
        $form_handle_mode = '';
        $update_form_handle = false;

        // check if key exists and get then set the value
        if( array_key_exists('_cqfs', $this->values ) ){

            // the received value of this field
            $form_handle_mode = sanitize_text_field( $this->values['_cqfs']['form_handle'] );

            // updates and stores boolean
            $update_form_handle = !empty($form_handle_mode) ? update_option('_cqfs_form_handle', $form_handle_mode ) : false;
        }

        //if option updated successfully
        if( $update_form_handle ){
            //success return
            wp_safe_redirect( $success_url );
        }else{
            //failure return
            wp_safe_redirect( $failure_url );
        }
        
        //exit immediately
        exit();

    }

}

// instanciate if admin
if( is_admin() ){
    $form_handle = new FormHandle;
}

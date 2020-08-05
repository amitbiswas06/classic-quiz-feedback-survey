<?php
/**
 * Form handle class for CQFS admin settings pages
 * Handled by `admin-post.php`
 * @since 1.0.0
 */

//define namespaces
namespace CQFS\ADMIN\FORMHANDLE;

class FormHandle {

    public function __construct() {
        //hooks goes here
        //Authenticated action for the CQFS form. action value `cqfs_response`
        add_action( 'admin_post_cqfs_admin_response', [$this, 'admin_submission'] );//php req
    }


    /**
     * Submission to `admin-post.php`
     * Validate and return
     */
    public function admin_submission(){

        //sanitize the global POST var. XSS ok.
        //all form inputs and security inputs
        //hidden keys (_cqfs_id, action, _cqfs_nonce, _wp_http_referer)
        $user_capability = current_user_can('manage_options');
        $values = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        var_dump($values);//main post

        //get the nonce
        $nonce = sanitize_text_field( $values['_cqfs_admin_nonce'] );

        //failure url args
        $failure_args = [
            'page'          => urlencode(sanitize_text_field('cqfs-settings')),
            '_cqfs_status'  => urlencode(sanitize_text_field('update-failed')),
        ];

        //failure return url
        $failure_url = esc_url_raw(
            add_query_arg( $failure_args, wp_unslash( esc_url( strtok($values['_wp_http_referer'], '?') ) ) )
        );

        //bail early if found suspecious with nonce verification.
		if ( ! wp_verify_nonce( $nonce, 'cqfs_admin_post' ) ) {

            //failure return
			wp_safe_redirect( $failure_url );

            //exit immediately
			exit();

        }

        /**************************************************************/
        //run if nonce ok
        
        $form_handle_mode = sanitize_text_field( $values['_cqfs_form_handle'] );

        //if user's capability check is true
        if( $user_capability ){
            //then update the form handle option and store boolean in variable
            $update_opt = update_option('_cqfs_form_handle', $form_handle_mode );
        }

        //success url args
        $success_args = [
            'page'          => urlencode(sanitize_text_field('cqfs-settings')),
            '_cqfs_status'  => urlencode(sanitize_text_field('settings-updated')),
        ];

        //success return url
        $success_url = esc_url_raw(
            add_query_arg( $success_args, wp_unslash( esc_url( strtok($values['_wp_http_referer'], '?') ) ) )
        );

        //if option updated successfully
        if( $update_opt ){
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

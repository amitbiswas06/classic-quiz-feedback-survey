<?php
/**
 * Form handle class for CQFS admin settings pages
 * Handled by `admin-post.php`
 * @since 1.0.0
 */

//define namespaces
namespace CQFS\ADMIN\FORMHANDLE;
use CQFS\INC\UTIL\Utilities as Util;

class FormHandle {

    // admin settings form nonce
    const Mail_Settings_Nonce = 'cqfs_mail_settings';

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
        $this->values = filter_input_array(INPUT_POST);

        // prepare failure url args
        $this->failure_args = [
            'page'          => urlencode( sanitize_text_field('cqfs-settings') ),
            '_cqfs_status'  => urlencode( sanitize_text_field('update-failed') ),
        ];

        // prepare success url args
        $this->success_args = [
            'page'          => urlencode( sanitize_text_field('cqfs-settings') ),
            '_cqfs_status'  => urlencode( sanitize_text_field('settings-updated') ),
        ];

        // set failure url
        $this->failure_url = esc_url_raw(
            add_query_arg( $this->failure_args, admin_url('admin.php') )
        );

        // set success url
        $this->success_url = esc_url_raw(
            add_query_arg( $this->success_args, admin_url('admin.php') )
        );

        //hooks goes here
        //Authenticated action for the CQFS form. action value `cqfs_response`
        add_action( 'admin_post_cqfs_mail_settings_action', [$this, 'cqfs_mail_settings_action'] );//php req
    
    }


    /**
     * Submission to `admin-post.php`
     * Validate and return
     */
    public function cqfs_mail_settings_action(){

        // check nonce
		if ( !isset( $this->values['_cqfs_mail_settings_nonce'] ) || !wp_verify_nonce( $this->values['_cqfs_mail_settings_nonce'], self::Mail_Settings_Nonce )){
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
        if( array_key_exists( '_cqfs', $this->values ) ){

            $sender_email = '';
            $mail_admin = false;
            $mail_user = false;
            $email_notes = '';
            $email_footer = '';

            // received value of admin email. input type email.
            if( isset( $this->values['_cqfs']['mail-sender-email'] ) ){
                $sender_email = sanitize_email( $this->values['_cqfs']['mail-sender-email'] );
            }

            // update admin email. input type email.
            update_option( '_cqfs_sender_email', $sender_email );

            // received value of mail to admin. checkbox
            if( isset( $this->values['_cqfs']['mail-admin'] ) ){
                $mail_admin = sanitize_text_field( $this->values['_cqfs']['mail-admin'] );
            }

            // update mail to admin. checkbox
            update_option( '_cqfs_mail_admin', $mail_admin );
            
            // received value of mail to user. checkbox
            if( isset( $this->values['_cqfs']['mail-user'] ) ){
                $mail_user = rest_sanitize_boolean( $this->values['_cqfs']['mail-user'] );
            }

            // update mail to user. checkbox
            update_option( '_cqfs_mail_user', $mail_user );

            // received value of additional email notes
            if( isset( $this->values['_cqfs']['mail-additional-notes'] ) ){
                $email_notes = wp_kses( $this->values['_cqfs']['mail-additional-notes'], 'post' );
            }

            // update mail to user. checkbox
            update_option( '_cqfs_mail_notes', $email_notes );

            // received value of email footer
            if( isset( $this->values['_cqfs']['mail-footer'] ) ){
                $email_footer = wp_kses( $this->values['_cqfs']['mail-footer'], 'post' );
            }

            // update mail to user. checkbox
            update_option( '_cqfs_mail_footer', $email_footer );

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

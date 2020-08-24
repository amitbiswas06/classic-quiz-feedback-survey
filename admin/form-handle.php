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

    // cqfs-entry email to user nonce
    const ENTRY_EMAIL_NONCE = 'cqfs_entry_email_user_nonce';

    // admin settings form nonce
    const Mail_Settings_Nonce = 'cqfs_mail_settings';

    // admin settings recreate result nonce
    const RECREATE_RESULT = 'cqfs_recreate_resultpage';

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

    // failure url for cqfs_entry
    protected $failure_url_entry;

    // success url entry
    protected $success_url_entry;

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

        // failure url cqfs_entry
        $this->failure_url_entry = esc_url_raw(
            add_query_arg( array(
                'cqfs-email-to-user' => urlencode('failure'),
            ), isset($this->values['_wp_http_referer']) ? $this->values['_wp_http_referer'] : admin_url() )
        );

        // success url cqfs_entry
        $this->success_url_entry = esc_url_raw(
            add_query_arg( array(
                'cqfs-email-to-user' => urlencode('success'),
            ), isset($this->values['_wp_http_referer']) ? $this->values['_wp_http_referer'] : admin_url() )
        );

        //hooks goes here
        //Authenticated action for the CQFS form. action value `cqfs_response`
        add_action( 'admin_post_cqfs_mail_settings_action', [$this, 'cqfs_mail_settings_action'] );//php req
    
        // action for the cqfs entry edit page, send email to user
        add_action('admin_post_cqfs_entry_action', [$this, 'cqfs_entry_email_to_user']); // php fallback
        add_action('wp_ajax_cqfs_entry_action', [$this, 'cqfs_entry_email_to_user']); // ajax

        // admin settings recreate result page
        add_action('admin_post_cqfs-recreate-result-page', [$this, 'recreate_result_page']); // php fallback
        add_action('wp_ajax_cqfs-recreate-result-page', [$this, 'recreate_result_page']); // ajax call

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


    public function cqfs_entry_email_to_user(){
        // var_dump($this->values);
        // check nonce
		if ( !isset( $this->values['_cqfs_entry_nonce'] ) || !wp_verify_nonce( $this->values['_cqfs_entry_nonce'], self::ENTRY_EMAIL_NONCE )){
            
            if( isset($this->values['ajax_request']) && rest_sanitize_boolean( $this->values['ajax_request'] ) ){
                //failure return
                wp_send_json_error([
                    'message'   => esc_html__('Security check unsuccessful.','cqfs')
                ]);
            }else{
                //php fallback
                wp_safe_redirect( $this->failure_url_entry );
            }

            //exit immediately
			exit();
		}

		// Check the user's permissions.
        if ( ! current_user_can( 'manage_options' ) ) {

            if( isset($this->values['ajax_request']) && rest_sanitize_boolean( $this->values['ajax_request'] ) ){
                //failure return
                wp_send_json_error([
                    'message'   => esc_html__('Permission Denied.','cqfs')
                ]);
            }else{
                //php fallback
                wp_safe_redirect( $this->failure_url_entry );
            }
            
            //exit immediately
			exit();
        }

        /**************************************************************/
        // run if nonce and user permission is ok

        if( array_key_exists( 'cqfs_entry', $this->values ) ){

            $email_id = '';
            $build_id = '';
            $entry_id = '';

            if( isset($this->values['cqfs_entry']['email-id']) ){
                $email_id = sanitize_email( $this->values['cqfs_entry']['email-id'] );
            }
            
            if( isset($this->values['cqfs_entry']['build-id']) ){
                $build_id = sanitize_text_field( $this->values['cqfs_entry']['build-id'] );
            }
            
            if( isset($this->values['cqfs_entry']['entry-id']) ){
                $entry_id = sanitize_text_field( $this->values['cqfs_entry']['entry-id'] );
            }
            
        
            $title = Util::cqfs_build_obj($build_id)['title'];
            $title .= sprintf(
                __('[Duplicate #%s]','cqfs'),
                esc_attr($entry_id)
            );

            $body = Util::cqfs_mail_body($build_id, $entry_id);

            // fire email to user
            $send_email_user = Util::cqfs_mail( $email_id, esc_html($title), $body );

            if( $send_email_user ){

                if( isset($this->values['ajax_request']) && rest_sanitize_boolean( $this->values['ajax_request'] ) ){
                    //success return json for ajax
                    wp_send_json_success([
                        'message'   => sprintf(
                            __('<div class="cqfs-return-msg success"><p><span class="cqfs-icon success-icon"></span>%s</p></div>','cqfs'),
                            esc_html__('Mail successfully sent.','cqfs')
                        ),
                    ]);
                }else{
                    //php fallback
                    wp_safe_redirect( $this->success_url_entry );
                }
                
                exit();

            }else{

                if( isset($this->values['ajax_request']) && rest_sanitize_boolean( $this->values['ajax_request'] ) ){
                    //failure return json
                    wp_send_json_error([
                        'message'   => sprintf(
                            __('<div class="cqfs-return-msg failure"><p><span class="cqfs-icon failure-icon"></span>%s</p></div>','cqfs'),
                            esc_html__('Mail not send. Please try again.','cqfs')
                        ),
                    ]);
                    
                }else{
                    //php fallback
                    wp_safe_redirect( $this->failure_url_entry );
                }

            }
        
        }

        exit();

    }


    public function recreate_result_page(){
        // var_dump($this->values);
        // check nonce
		if ( !isset( $this->values['_cqfs_recreate_resultpage_nonce'] ) || !wp_verify_nonce( $this->values['_cqfs_recreate_resultpage_nonce'], self::RECREATE_RESULT )){
            
            if( isset($this->values['ajax_request']) && rest_sanitize_boolean( $this->values['ajax_request'] ) ){
                //failure return
                wp_send_json_error([
                    'message'   => esc_html__('Security check unsuccessful.','cqfs')
                ]);
            }else{
                //php fallback
                wp_safe_redirect( $this->failure_url );
            }

            //exit immediately
			exit();
		}

		// Check the user's permissions.
        if ( ! current_user_can( 'manage_options' ) ) {

            if( isset($this->values['ajax_request']) && rest_sanitize_boolean( $this->values['ajax_request'] ) ){
                //failure return
                wp_send_json_error([
                    'message'   => esc_html__('Permission Denied.','cqfs')
                ]);
            }else{
                //php fallback
                wp_safe_redirect( $this->failure_url );
            }
            
            //exit immediately
			exit();
        }

        /**************************************************************/
        // run if nonce and user permission is ok

        $create = false;
        if ( null === get_page_by_path(CQFS_RESULT) ) {
           
            $current_user = wp_get_current_user();
            
            // create post object
            $result_page = array(
                'post_title'  => esc_html__( 'Cqfs Result','cqfs' ),
                'post_name'   => esc_attr(CQFS_RESULT),
                'post_content'=> esc_html__('This page displays CQFS results. Please do not delete this page.','cqfs'),
                'post_status' => 'publish',
                'post_author' => esc_attr($current_user->ID),
                'post_type'   => 'page',
            );
            
            // insert the post into the database
            $create = wp_insert_post( $result_page );

        }

        if( $create ){
            if( isset($this->values['ajax_request']) && rest_sanitize_boolean( $this->values['ajax_request'] ) ){
                //success return json for ajax
                wp_send_json_success([
                    'message'   => sprintf(
                        __('<div class="cqfs-return-msg success"><p><span class="cqfs-icon success-icon"></span>%s</p></div>','cqfs'),
                        esc_html__('Result page created successfully.','cqfs')
                    ),
                ]);
            }else{
                //php fallback
                wp_safe_redirect( $this->success_url );
            }

            exit();
        }else{
            if( isset($this->values['ajax_request']) && rest_sanitize_boolean( $this->values['ajax_request'] ) ){
                //failure return json
                wp_send_json_error([
                    'message'   => sprintf(
                        __('<div class="cqfs-return-msg failure"><p><span class="cqfs-icon failure-icon"></span>%s</p></div>','cqfs'),
                        esc_html__('Cannot create result page. Please refresh and check.','cqfs')
                    ),
                ]);
                
            }else{
                //php fallback
                wp_safe_redirect( $this->failure_url );
            }
        }


        // exit immediately
        exit();

    }

}

// instanciate if admin
if( is_admin() ){
    $form_handle = new FormHandle;
}

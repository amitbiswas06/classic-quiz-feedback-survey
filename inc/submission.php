<?php
/**
 * Front end shortcode:
 * Form submission handler and response object
 * @since 1.0.0
 */

//define namespaces
namespace CQFS\INC\SUBMIT;

//use namespace
use CQFS\INC\UTIL\Utilities as Util;
use WP_Query; //for custom email check

class Cqfs_Submission {

    // this will store form submit mode
    protected $ajax;

    // this will store post variables
    protected $values;

    // failure url arguments
    protected $failure_args;

    // failure url
    protected $failure_url;

    public function __construct(){

        //sanitize the global POST var. XSS ok.
        //all form inputs and security inputs
		//hidden keys (_cqfs_id, action, _cqfs_nonce, _wp_http_referer)
        $this->values = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);

        // set the submit mode
        $this->ajax = false;
        if( isset( $this->values['_cqfs_ajax'] ) && $this->values['_cqfs_ajax'] ){
            $this->ajax = rest_sanitize_boolean( $this->values['_cqfs_ajax'] );
        }
        
        // prepare failure url args
        if( isset( $this->values['_cqfs_id'] ) && !empty( $this->values['_cqfs_id'] ) ){
            $this->failure_args = [
                '_cqfs_status'  => urlencode(sanitize_text_field('failure')),
                '_cqfs_id'      => urlencode(sanitize_text_field($this->values['_cqfs_id']))
            ];
        }else{
            $this->failure_args = '';
        }
        

        // check if we are at admin-post.php or not
        if( isset( $this->values['_wp_http_referer'] ) && !empty( $this->values['_wp_http_referer'] ) ){
            // set failure url
            $this->failure_url = esc_url_raw(
                add_query_arg( $this->failure_args, esc_url( $this->values['_wp_http_referer'] ) )
            );

        }else{
            $this->failure_url = "";
        }


        //Non authenticated action for CQFS form via. action value `cqfs_response`
        add_action( 'admin_post_nopriv_cqfs_response', [$this, 'cqfs_form_submission'] );//php req
        add_action( 'wp_ajax_nopriv_cqfs_response', [$this, 'cqfs_form_submission'] );//AJAX req

        //Authenticated action for the CQFS form. action value `cqfs_response`
        add_action( 'admin_post_cqfs_response', [$this, 'cqfs_form_submission'] );//php req
        add_action( 'wp_ajax_cqfs_response', [$this, 'cqfs_form_submission'] );//AJAX req
        
        // login function
        add_action( 'admin_post_nopriv_cqfs_login', [$this, 'cqfs_login'] );//php req fallback
        add_action( 'wp_ajax_nopriv_cqfs_login', [$this, 'cqfs_login'] );//AJAX req


    }

    public function cqfs_login(){
        
        //bail early if found suspecious with nonce verification.
		if ( !isset( $this->values['_cqfs_login_nonce'] ) || ! wp_verify_nonce( $this->values['_cqfs_login_nonce'], 'cqfs_login' ) ) {
        
            if( isset($this->values['ajax_request']) && rest_sanitize_boolean( $this->values['ajax_request'] ) ){
                wp_send_json_error([
                    'message'   => esc_html__('Security check unsuccessful.','cqfs')
                ]);
            }else{
                //php fallback
                wp_safe_redirect( $this->failure_url );
            }

            exit();
        
        }

        if( isset($this->values['cqfs_username']) && isset($this->values['cqfs_password']) ){

            $creds = array(
                'user_login'    => sanitize_user($this->values['cqfs_username']),
                'user_password' => $this->values['cqfs_password'],
                'remember'      => true
            );
         
            $user = wp_signon( $creds, false );
         
            if ( is_wp_error( $user ) ) {

                if( isset($this->values['ajax_request']) && rest_sanitize_boolean( $this->values['ajax_request'] ) ){
                    wp_send_json_error([
                        'message'   => wp_kses($user->get_error_message(), 'post')
                    ]);
                }else{
                    //php fallback
                    wp_safe_redirect( $this->failure_url );
                }

                exit();

            }else{
                wp_set_current_user($user->ID);

                if( isset($this->values['ajax_request']) && rest_sanitize_boolean( $this->values['ajax_request'] ) ){
                    wp_send_json_success([
                        'message'   => sprintf(
                            __('<div class="cqfs-return-msg success"><p><span class="cqfs-icon success-icon"></span>%s</p></div>','cqfs'),
                            esc_html__('Login Successful.','cqfs')
                        ),
                        'status'    => sprintf(
                            __('<div class="cqfs-return-msg success"><p><span class="cqfs-icon success-icon"></span>%s</p></div>','cqfs'),
                            esc_html__('You are now logged in.','cqfs')
                        ),
                        'nonce'     => wp_create_nonce('_cqfs_post_'),
                    ]);
                
                }else{
                    //php fallback
                    wp_safe_redirect( esc_url_raw(
                        add_query_arg( array(
                            'login' => urlencode('success'),
                        ), $this->values['_wp_http_referer'] )
                    ));

                }  

            }
            
        }

        exit();
    }


    /**
     * CQFS form handle
     * via `admin-post.php`
     */
	public function cqfs_form_submission(){

        // var_dump($this->values);//main $_post

        $cqfsID = '';
        if( isset($this->values['_cqfs_id']) ){
            $cqfsID = esc_attr($this->values['_cqfs_id']);
        }
        
        //unique nonce fields
        $nonce_action = esc_attr('_cqfs_post_');
        $nonce_name = esc_attr('_cqfs_nonce_') . $cqfsID;

		//bail early if found suspecious with nonce verification.
		if ( !isset( $this->values[$nonce_name] ) || ! wp_verify_nonce( $this->values[$nonce_name], $nonce_action ) ) {

            //send JSON response for ajax mode on failure
            if( $this->ajax ){
                wp_send_json_error( $this->failure_args );
            }else{
                //safe redirect for php mode on failure
                wp_safe_redirect( $this->failure_url );
                // var_dump($this->failure_args);
            }
            
			exit();

        }
        
        /****************************************
         * prepare post variables
         ****************************************/

        //main build object array
        $cqfs_build = Util::cqfs_build_obj( $cqfsID );

        $questionsArr = []; //prepare question entries as array
        $numCorrects = []; //holds the ture only
        $ansStatus = []; //holds all answers boolean as true false
        $notes = []; //notes for each question

        //prepare answers
        $userAnswers = [];
        if(array_key_exists('cqfs', $this->values)){
            $userAnswers = $this->values['cqfs'];
        }
        
        // var_dump($userAnswers);
        $answersArr = [];

        if($userAnswers){
            
            foreach( $cqfs_build['all_questions'] as $question ){

                foreach( $userAnswers as $k=>$v ){
                    if($k == $question['id']){

                        //push questions
                        $questionsArr[] = $question['question'];

                        //check answers and return boolean
                        $compare = Util::cqfs_array_equality_check( $question['answers'], $v );
                        //push answer status
                        $ansStatus[] = $compare ? esc_html__('Correct Answer.', 'cqfs') : esc_html__('Wrong Answer.','cqfs');
                        //push true values
                        if($compare){
                            $numCorrects[] = $compare;
                        }

                        //push answer string in array
                        $answers = [];
                        if($v != ""){
                            foreach($v as $ans){
                                $answers[] = $question['options'][$ans-1];
                            }
                        }else{
                            $answers[] = esc_html__('You have skipped this question.','cqfs');
                        }

                        //now implode to string and push
                        $answersArr[] = sanitize_text_field(implode(" | ", $answers));

                        //push the note for each question
                        $notes[] = $question['note'] ? sanitize_text_field($question['note']) : esc_html__('Not Available.','cqfs');
                    
                    }
                }
    
            }
    
        }
        // var_dump($numCorrects);

        
        /**
         * Final preparation
         */

        //1. convert `questionsArr` array to string
        $questionsArr = implode("\n", $questionsArr);
        //2. convert `answersArr` array to string
        $answersArr = implode("\n", $answersArr);
        //3. percentage obtained
        $percentage = round(count($numCorrects) * 100 / count($cqfs_build['all_questions']));
        //4. Result
        $result = $percentage >= $cqfs_build['pass_percent'] ? esc_attr('passed') : esc_attr('failed');
        //5. each answer status
        $ansStatus = implode("\n", $ansStatus);
        //6. note for each question
        $notes = implode("\n", $notes);
        //7. pass-fail message as remarks
        $remarks = $percentage >= $cqfs_build['pass_percent'] ? $cqfs_build['pass_msg'] : $cqfs_build['fail_msg'];
        
        // var_dump($ansStatus);
        //now insert into post array
        $post_array = array(
            'post_title'    => '',
            'post_status'   => 'publish',
            'post_type'     => 'cqfs_entry',
            'meta_input'    => array(
                'cqfs_entry_form_id'    => $cqfsID,
                'cqfs_entry_form_type'  => sanitize_text_field($cqfs_build['type']),
                'cqfs_entry_result'     => sanitize_text_field($result),
                'cqfs_entry_percentage' => sanitize_text_field($percentage),
                'cqfs_entry_questions'  => wp_kses($questionsArr, 'post'),
                'cqfs_entry_answers'    => wp_kses($answersArr, 'post'),
                'cqfs_entry_status'     => wp_kses($ansStatus, 'post'),
                'cqfs_entry_notes'      => wp_kses($notes, 'post'),
                'cqfs_entry_remarks'    => wp_kses($remarks, 'post'),
                'cqfs_entry_user_email' => '',
            ),
        );
        
        //update post title and user email for user
        //store email for validation use
        $user_emailID = '';

        if( is_user_logged_in() ){
            $current_user = wp_get_current_user();
            $post_array['post_title'] = sanitize_text_field( $current_user->display_name );
            $post_array['meta_input']['cqfs_entry_user_email'] = sanitize_email( $current_user->user_email );
            $user_emailID = sanitize_email( $current_user->user_email );
        }else{

            if( isset($this->values['_cqfs_uname']) && 
            isset($this->values['_cqfs_email']) ){

                $post_array['post_title'] = $this->values['_cqfs_uname'] ? sanitize_text_field($this->values['_cqfs_uname']) : esc_html__('Guest', 'cqfs');
                $post_array['meta_input']['cqfs_entry_user_email'] = sanitize_email($this->values['_cqfs_email']);
                $user_emailID = sanitize_email($this->values['_cqfs_email']);

            }else{
                $post_array['post_title'] = '';
                $post_array['meta_input']['cqfs_entry_user_email'] = '';
                $user_emailID = '';
            }
            
        }
        
        // Insert the post into the database and store the post ID
        $duplicate_email = false;
        $cqfs_entry_id = false;

        if( !empty($user_emailID) ){

            //check whether the email is new = restrict to 1 email 1 entry
            /* $cqfs_wp_query = new WP_Query( array(
                    'post_type'         => 'cqfs_entry',
                    'posts_per_page'    => -1,
                )
            );
            if( $cqfs_wp_query->have_posts() ){
                while ( $cqfs_wp_query->have_posts() ) : $cqfs_wp_query->the_post();

                    $cqfs_entry_email = Util::cqfs_entry_obj( get_the_ID() )['email'];
                    if( $user_emailID == $cqfs_entry_email ){

                        $duplicate_email = true;
                    }

                endwhile;
            }
            // Reset Post Data
            wp_reset_postdata();

            if( ! $duplicate_email ){
                $cqfs_entry_id = wp_insert_post( $post_array );
            } */
            
            $cqfs_entry_id = wp_insert_post( $post_array );

        }

        // var_dump($duplicate_email);

        /****************************************
         * prepare redirection array
         ****************************************/

        //prepare the redirection args on successful post creation
        $redirect_args = [];//main redirect args

        //on successful `cqfs_entry` post creation
        if( $cqfs_entry_id ){
            
            //email to admin "checkbox"
            $email_to_admin = esc_attr( get_option('_cqfs_mail_admin') );

            //email to admin "checkbox"
            $email_to_user = esc_attr( get_option('_cqfs_mail_user') );

            if($email_to_user){
                $body = Util::cqfs_mail_body($cqfsID, $cqfs_entry_id);
                // fire email to user
                $send_email_user = Util::cqfs_mail( sanitize_email($user_emailID), esc_html($cqfs_build['title']), $body );
            }
            
            if($email_to_admin){
                $body_admin = Util::cqfs_mail_body($cqfsID, $cqfs_entry_id, true);
                $admin_email_id = get_bloginfo('admin_email');
                // fire email to admin
                $send_email_admin = Util::cqfs_mail( sanitize_email($admin_email_id), esc_html($cqfs_build['title']), $body_admin );
            }
            

            //add form id
            $redirect_args['_cqfs_id'] = urlencode($cqfsID);

            //add a success field to the redirect args
            $redirect_args['_cqfs_status'] = urlencode(sanitize_text_field('success'));

            //add email and user display name
            if( is_user_logged_in() ){
                $current_user = wp_get_current_user();
                $redirect_args['_cqfs_uname'] = urlencode(sanitize_text_field( $current_user->display_name ));
                $redirect_args['_cqfs_email'] = urlencode(sanitize_email( $current_user->user_email ));
            }else{
                $redirect_args['_cqfs_uname'] = urlencode(sanitize_text_field( $this->values['_cqfs_uname'] ));
                $redirect_args['_cqfs_email'] = urlencode(sanitize_email( $this->values['_cqfs_email'] ));
            }

            //lastly add the post id that is just created
            $redirect_args['_cqfs_entry_id'] = urlencode(sanitize_text_field($cqfs_entry_id));

        }else{
            //on faliure
            $redirect_args['_cqfs_status'] = urlencode(sanitize_text_field('failure'));
            $redirect_args['_cqfs_id'] = urlencode($cqfsID);
            $redirect_args['_cqfs_duplicate'] = urlencode(rest_sanitize_boolean($duplicate_email));
        }
        
        // var_dump($redirect_args);//urlencode array

        //send JSON response for ajax mode
        if( !$cqfs_entry_id && $this->ajax ){
            
            wp_send_json_error( $redirect_args );
            exit();

        }else if( $this->ajax ){
            //prepare args
            // $redirect_args[] = $post_array;
            $entry = util::cqfs_entry_obj($cqfs_entry_id);
            wp_send_json_success( $entry );

            //exit immediately
            exit();
        }
        
        // result page url
        $result_page_url = Util::cqfs_result_page_url();
        if( !$this->ajax ){
            /**
             * Redirect to the result page and exit
             */
            wp_safe_redirect(
                esc_url_raw(
                    add_query_arg( $redirect_args, $result_page_url ? $result_page_url : home_url('/') )
                )
            );

            //exit immediately
            exit();	
        }
        

    }


}

$cqfs_submission = new Cqfs_Submission;
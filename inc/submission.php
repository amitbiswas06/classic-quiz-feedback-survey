<?php
/**
 * Form submission handler and response object
 * @since 1.0.0
 */

//define namespaces
namespace CQFS\INC\SUBMIT;

//use namespace
use CQFS\INC\UTIL\Utilities as Util;
use WP_Query;

class Cqfs_Submission {

    public function __construct(){

        //Non authenticated action for CQFS form via. action value `cqfs_response`
        add_action( 'admin_post_nopriv_cqfs_response', [$this, 'cqfs_form_submission'] );//php req
        add_action( 'wp_ajax_nopriv_cqfs_response', [$this, 'cqfs_form_submission'] );//AJAX req

        //Authenticated action for the CQFS form. action value `cqfs_response`
        add_action( 'admin_post_cqfs_response', [$this, 'cqfs_form_submission'] );//php req
        add_action( 'wp_ajax_cqfs_response', [$this, 'cqfs_form_submission'] );//AJAX req
        
    }


    /**
     * CQFS form handle
     * via `admin-post.php`
     */
	public function cqfs_form_submission(){

        //check form submit mode
        $submit_mode = sanitize_text_field( get_option('_cqfs_form_handle') );

        //sanitize the global POST var. XSS ok.
        //all form inputs and security inputs
		//hidden keys (_cqfs_id, action, _cqfs_nonce, _wp_http_referer)
        $values = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        // var_dump($values);//main post

		//get the nonce
        $nonce = sanitize_text_field( $values['_cqfs_nonce'] );

		//bail early if found suspecious with nonce verification.
		if ( ! wp_verify_nonce( $nonce, 'cqfs_post' ) ) {

			$cqfs_status = [
                '_cqfs_status'  => urlencode(sanitize_text_field('failure')),
                '_cqfs_id'      => urlencode(sanitize_text_field($values['_cqfs_id']))
            ];

            //send JSON response for ajax mode
            wp_send_json_error( $cqfs_status );

            //safe redirect for php mode
			wp_safe_redirect(
				esc_url_raw(
					add_query_arg( $cqfs_status, wp_unslash(esc_url(strtok($values['_wp_http_referer'], '?')) ) )
				)
            );
            
			exit();

        }
        
        /**
         * sanitize and prepare data for new post creation
         */
        $id = sanitize_text_field( $values['_cqfs_id'] );
        
        /****************************************
         * prepare post variables
         ****************************************/

        //main build object array
        $cqfs_build = Util::cqfs_build_obj( $id );

        $questionsArr = []; //prepare question entries as array
        $numCorrects = []; //holds the ture only
        $ansStatus = []; //holds all answers boolean as true false
        $notes = []; //notes for each question

        //prepare answers
        $remove_non_array_callback = function( $val ){
            return is_array($val);
        };
        $userAnswers = array_values(array_filter($values, $remove_non_array_callback));
        // var_dump($userAnswers);
        $answersArr = [];

        if($userAnswers){
            $i = 0;
            foreach( $cqfs_build['all_questions'] as $question ){
                //push questions
                $questionsArr[] = $question['question'];
    
                //check answers and return boolean
                $compare = Util::cqfs_array_equality_check( $question['answers'], $userAnswers[$i] );
                //push answer status
                $ansStatus[] = $compare ? esc_html__('Correct Answer.', 'cqfs') : esc_html__('Wrong Answer.','cqfs');
                //push true values
                if($compare){
                    $numCorrects[] = $compare;
                }
    
                //push answer string in array
                $answers = [];
                foreach($userAnswers[$i] as $ans){
                    $answers[] = $question['options'][$ans-1];
                }
                //now implode to string and push
                $answersArr[] = sanitize_text_field(implode(" | ", $answers));
    
                //push the note for each question
                $notes[] = $question['note'] ? sanitize_text_field($question['note']) : esc_html__('Not Available.','cqfs');
    
                $i++;
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
                'cqfs_entry_form_id'    => $id,
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
            $post_array['post_title'] = $values['_cqfs_uname'] ? sanitize_text_field($values['_cqfs_uname']) : esc_html__('Guest', 'cqfs');
            $post_array['meta_input']['cqfs_entry_user_email'] = sanitize_email($values['_cqfs_email']);
            $user_emailID = sanitize_email($values['_cqfs_email']);
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

        //on successful post creation
        if( $cqfs_entry_id ){

            //add form id
            $redirect_args['_cqfs_id'] = urlencode(sanitize_text_field($values['_cqfs_id']));

            //add a success field to the redirect args
            $redirect_args['_cqfs_status'] = urlencode(sanitize_text_field('success'));

            //add email and user display name
            if( is_user_logged_in() ){
                $current_user = wp_get_current_user();
                $redirect_args['_cqfs_uname'] = urlencode(sanitize_text_field( $current_user->display_name ));
                $redirect_args['_cqfs_email'] = urlencode(sanitize_email( $current_user->user_email ));
            }else{
                $redirect_args['_cqfs_uname'] = urlencode(sanitize_text_field( $values['_cqfs_uname'] ));
                $redirect_args['_cqfs_email'] = urlencode(sanitize_email( $values['_cqfs_email'] ));
            }

            //lastly add the post id that is just created
            $redirect_args['_cqfs_entry_id'] = urlencode(sanitize_text_field($cqfs_entry_id));

        }else{
            //on faliure
            $redirect_args['_cqfs_status'] = urlencode(sanitize_text_field('failure'));
            $redirect_args['_cqfs_id'] = urlencode(sanitize_text_field($values['_cqfs_id']));
            $redirect_args['_cqfs_duplicate'] = urlencode(rest_sanitize_boolean($duplicate_email));
        }
        
        // var_dump($redirect_args);//urlencode array

        //send JSON response for ajax mode
        if( !$cqfs_entry_id && $submit_mode === 'ajax_mode'){
            
            wp_send_json_error( $redirect_args );

        }else if( $submit_mode === 'ajax_mode' ){
            //prepare args
            // $redirect_args[] = $post_array;
            $entry = util::cqfs_entry_obj($cqfs_entry_id);
            wp_send_json_success( $entry );
        }
        

        /**
         * Redirect the page and exit
         */
        wp_safe_redirect(
            esc_url_raw(
                add_query_arg( $redirect_args, wp_unslash( esc_url( strtok( $values['_wp_http_referer'], '?' ) ) ) )
            )
        );

        //exit immediately
        exit();	

    }


}

$cqfs_submission = new Cqfs_Submission;
<?php
/**
 * Build the CQFS shortcode
 * @since 1.0.0
 */

//define namespaces
namespace CQFS\INC\SHORTCODE;

//use namespace
use CQFS\INC\UTIL\Utilities as Util;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class CqfsShortcode {

    public function __construct(){

        //shortcode function
        add_shortcode( 'cqfs', [$this, 'cqfs_shortcode'] );

        //Non authenticated action for CQFS form via
		add_action( 'admin_post_nopriv_cqfs_response', [$this, 'cqfs_form_submission'] );

		//Authenticated action for the CQFS form
		add_action( 'admin_post_cqfs_response', [$this, 'cqfs_form_submission'] );
    }

    /**
     * CQFS shortcode function
     * 
     * @param string
     * @return CQFS shortcode
     */
    public function cqfs_shortcode( $atts ) {
        
        $atts = shortcode_atts(
            array(
                'id'    => '',
                'title' => 1,
            ), $atts
        );

        //bail early if no id provided!
        if( ! $atts['id'] ){
            return;
        }
        
        //main build object array
        $cqfs_build = Util::cqfs_build_obj( $atts['id'] );
        // var_dump( $cqfs_build );

        //get parameters
        $param = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

        ob_start(); 

        //show this message if returns a failure
        if( isset($param['_cqfs_status']) && 
        isset($param['_cqfs_id']) && 
        $param['_cqfs_status'] === 'failure' && 
        $param['_cqfs_id'] === $cqfs_build['id'] ){

            $failure_msg = apply_filters('cqfs_failure_msg', esc_html__('Something went terribly wrong. Please try again.', 'cqfs') );
            printf(
                '<p class="cqfs-failure-msg">%s</p>',
                esc_html( $failure_msg )
            );

        }
        
        //check parameters and show form or result
        if ( !isset($param['_cqfs_status']) || 
        !isset($param['_cqfs_id']) || 
        $param['_cqfs_status'] !== 'success' || 
        $param['_cqfs_id'] !== $cqfs_build['id'] ) { 
            //display the form
        ?>
        <!-- cqfs start -->
        <div id="cqfs-<?php echo esc_attr($cqfs_build['id']); ?>" class="cqfs <?php echo esc_attr($cqfs_build['classname']) ?>" data-cqfs-layout = <?php echo esc_attr($cqfs_build['layout']); ?>>
            <?php 
            if( $atts['title'] !== 'false' ){
                printf(
                    '<h2 class="cqfs--title">%s</h2>',
                    esc_html( $cqfs_build['title'] )
                );
            }

            //do action `cqfs_before_nav`
            do_action('cqfs_after_title');
            ?>
            <form id="cqfs-form-<?php echo esc_attr($cqfs_build['id']); ?>" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
            <div class="cqfs--questions" >
                <?php 
                 
                    if( $cqfs_build['all_questions'] ){
                        $i = 1;
                        foreach( $cqfs_build['all_questions']  as $question ) :

                            $show_first = $i == 1 ? 'show' : 'hide';
                        ?>
                        <div class="question <?php echo $cqfs_build['layout'] === 'multi' ? esc_attr($show_first) : ''; ?>">
                            <?php
                                printf(
                                    '<h3 class="question--title">%s %s</h3>',
                                    esc_html($i) . '&#46; ',
                                    esc_html( $question['question'] )
                                );

                                //display featured image if there is any
                                if( $question['thumbnail'] ){
                                    echo wp_kses( get_the_post_thumbnail($question['id'], 'medium_large'), 'post');
                                }
                                
                            ?>
                            <div class="options">
                        
                                <?php if( $question['options'] ) {
                                    $j = 1;
                                    foreach( $question['options'] as $optn ) {
                                        
                                    ?>
                                <div class="input-wrap">
                                    <input name="option<?php echo $i; ?>[]" type="<?php echo esc_attr($question['input_type']); ?>" id="<?php echo Util::cqfs_slug($optn); ?>" value="<?php echo $j; ?>">
                                    <label for="<?php echo Util::cqfs_slug($optn); ?>"><?php echo esc_html($optn); ?></label>
                                </div>
                                <?php $j++; }} ?>
                                
                            </div>
                        </div>
                        <?php
                        $i++;
                        endforeach;

                        //if not logged in, display user info form
                        Util::cqfs_user_info_form( $cqfs_build['id'], $cqfs_build['layout'] );

                        //insert form ID in a hidden field
                        printf(
                            '<input type="hidden" name="_cqfs_id" value="%s">',
                            esc_attr( $cqfs_build['id'] )
                        );

                        //insert hidden action input
                        printf('<input type="hidden" name="action" value="cqfs_response">');
                        
                        //create nonce fields
                        wp_nonce_field('cqfs_post', '_cqfs_nonce');
                    }
                ?>
            </div><?php //var_dump(plugin_dir_url(__FILE__)); ?>
            
            <div class="cqfs--navigation">
                <?php 
                //show nex-prev nav if multi page layout
                $next_txt = apply_filters( 'cqfs_next_text', esc_html__('Next','cqfs') );
                $prev_txt = apply_filters( 'cqfs_prev_text', esc_html__('Prev','cqfs') );
                $submit_txt = apply_filters( 'cqfs_submit_text', esc_html__('Submit','cqfs') );

                if( $cqfs_build['layout'] === 'multi' ) { ?>
                    <button class="cqfs--next disabled" disabled><?php echo esc_html( $next_txt ); ?></button>
                    <button class="cqfs--prev disabled" disabled><?php echo esc_html( $prev_txt ); ?></button>
                    <button class="cqfs--submit disabled" type="submit" disabled><?php echo esc_html( $submit_txt ); ?></button>
                <?php }else{ ?>
                    <button class="cqfs--submit" type="submit"><?php echo esc_html( $submit_txt ); ?></button>
                <?php } ?>
            </div>
            </form>

            <?php
            //do action `cqfs_after_form`
            do_action('cqfs_after_form');
            ?>
            <div class="cqfs--processing hide"><?php esc_html_e('Processing...','cqfs'); ?></div>
            <div class="cqfs-error-msg hide"><?php esc_html_e('Please select an option.','cqfs'); ?></div>
        </div>
        <!-- cqfs end -->
        <?php } elseif( $cqfs_build['type'] === 'quiz' && 
            isset($param['_cqfs_status']) && 
            isset($param['_cqfs_id']) && 
            $param['_cqfs_status'] === 'success' && 
            $param['_cqfs_id'] === $cqfs_build['id'] ) {

            $entry_id = esc_attr($param['_cqfs_entry_id']);
            $entry_email = esc_attr($param['_cqfs_email']);
            
            echo "<div id='cqfs-results-{$entry_id}' class='cqfs-results'>";
            /**
             * Result display
             */
            if( isset($param['_cqfs_entry_id']) && isset($param['_cqfs_email']) && !empty($param['_cqfs_entry_id']) && !empty($param['_cqfs_email']) ){

                //get the entry array                
                $entry = util::cqfs_entry_obj( $entry_id );

                if( empty($entry) || is_null($entry) ){
                    return esc_html__('Invalid Result','cqfs');
                }

                if( $entry_email == $entry['email'] ){
                    
                    //username. escaped.
                    echo util::cqfs_display_uname( $entry['username'] );//escaped data
                    //result div. escaped.
                    echo util::cqfs_quiz_result( $cqfs_build['pass_percent'], $entry['percentage'], $cqfs_build['pass_msg'], $cqfs_build['fail_msg'] );//escaped data

                    foreach( $entry['all_questions'] as $ent ){

                        $you_ans = apply_filters('cqfs_result_you_answered', esc_html__('You answered&#58; ', 'cqfs'));
                        $status = apply_filters('cqfs_result_ans_status', esc_html__('Status&#58; ', 'cqfs'));
                        $note = apply_filters('cqfs_result_ans_note', esc_html__('Note&#58; ', 'cqfs'));

                        printf(
                            '<div class="cqfs-entry-qa">
                                <h4>%s</h4>
                                <p><label>%s</label>%s</p>
                                <p><label>%s</label>%s</p>
                                <p><label>%s</label>%s</p>
                            </div>',
                            esc_html( $ent['question'] ),
                            $you_ans,
                            esc_html( $ent['answer'] ),
                            $status,
                            esc_html( $ent['status'] ),
                            $note,
                            esc_html( $ent['note'] )
                        );
                    }


                }else{
                    return esc_html__('Invalid Result','cqfs');
                }


            }else{
                return esc_html__('Invalid Result','cqfs');
            }

            echo '</div>';


        }elseif( isset($param['_cqfs_status']) && 
        isset($param['_cqfs_id']) && 
        $param['_cqfs_status'] === 'success' && 
        $param['_cqfs_id'] === $cqfs_build['id'] ){
            /**
             * Message for non quiz cqfs
             */
            $cqfs_thank_msg = apply_filters('cqfs_thankyou_message', esc_html__('Thank you for participating.', 'cqfs'));
            printf(
                '<div class="cqfs-results"><h4>%s</h4></div>',
                esc_html( $cqfs_thank_msg )
            );
        } ?>
        <?php
        
        return ob_get_clean();
        
    }


    /**
     * CQFS form handle
     * via `admin-post.php`
     */
	public function cqfs_form_submission(){

        //sanitize the global POST var. XSS ok.
        //all form inputs and security inputs
		//hidden keys (_cqfs_id, action, _cqfs_nonce, _wp_http_referer)
        $values = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
        var_dump($values);//main post

		//get the nonce
        $nonce = sanitize_text_field( $values['_cqfs_nonce'] );

		//bail early if found suspecious with nonce verification.
		if ( ! wp_verify_nonce( $nonce, 'cqfs_post' ) ) {
			$cqfs_status = [
                '_cqfs_status'  => urlencode(sanitize_text_field('failure')),
                '_cqfs_id'      => urlencode(sanitize_text_field($values['_cqfs_id']))
            ];
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
        var_dump($userAnswers);
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
            // 'ID' => 200,
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
        $cqfs_entry_id = false;
        if( !empty($user_emailID) ){
            $cqfs_entry_id = wp_insert_post( $post_array );
        }

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
        }
        
        // var_dump($redirect_args);//urlencode array

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

$shortcode = new CqfsShortcode;
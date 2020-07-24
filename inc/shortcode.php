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
        // var_dump( $cqfs_build['all_questions'] );

        //get parameters
        $param = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

        ob_start(); 
        
        //check parameters
        if ( !isset($param['_cqfs_status']) || !isset($param['_cqfs_id']) || $param['_cqfs_status'] !== 'success' || $param['_cqfs_id'] !== $cqfs_build['id'] ) { 
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

                        //if logged in, insert a hidden field with user display name
                        if( is_user_logged_in() ){
                            $current_user = wp_get_current_user();
                            printf(
                                '<input type="hidden" name="_cqfs_uname" value="%s">',
                                esc_html( $current_user->display_name )
                            );
                        }

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
            
            <?php
            //do action `cqfs_before_nav`
            do_action('cqfs_before_nav');
            ?>
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
            //do action `cqfs_after_nav`
            do_action('cqfs_after_nav');
            ?>
            <div class="cqfs--processing hide"><?php esc_html_e('Processing...','cqfs'); ?></div>
        </div>
        <!-- cqfs end -->
        <?php } elseif( $cqfs_build['type'] === 'quiz' && isset($param['_cqfs_status']) && isset($param['_cqfs_id']) && $param['_cqfs_status'] === 'success' && $param['_cqfs_id'] === $cqfs_build['id'] ) {
            
            /**
             * Result display
             */

            $count = count($param);
            $userAnswers = array_values( array_slice($param, 0, ($count - 2), true ) );
            // var_dump($userAnswers);
            //empty array for correct ans
            $numCorrects = [];

            if( $cqfs_build['all_questions'] ){

                echo '<!-- cqfs result start --><div class="cqfs-results">';

                $i = 0;
                foreach( $cqfs_build['all_questions'] as $question ) :
                    
                    $user_ans = explode(",", $userAnswers[$i]);
                    // var_dump($user_ans);

                    //check answers and return boolean
                    $compare = Util::cqfs_array_equality_check( $question['answers'], $user_ans );
                    // var_dump($compare);
                    
                    //array push correct answers as boolean true
                    if($compare){
                        $numCorrects[] = $compare;
                    }
                ?>
                    <div class="cqfs-results--each">
                        <?php
                            printf(
                                '<h3 class="question--title">%s %s</h3>',
                                esc_html($i+1) . esc_html__('&#46; ','cqfs'),
                                esc_html( $question['question'] )
                            );
                        ?>
                        <p>
                            <label><?php echo esc_html__('You Answered ','cqfs'); ?></label>
                            <?php foreach( $user_ans as $ans ){ 
                            //echo and display the user ans from the options of question obj
                            ?><span><?php echo esc_html($question['options'][$ans-1]); ?></span><br>
                            <?php } ?>
                        </p>
                        <?php
                            printf(
                                '<p><label>%s</label><span>%s</span></p>',
                                esc_html__('Status: ','cqfs'),
                                $compare ? esc_html__('Correct Answer', 'cqfs') : esc_html__('Wrong Answer', 'cqfs')
                            );

                            if( !empty( $question['note'] )){
                                $additional_txt = apply_filters('cqfs_additional_notes', esc_html__('Additional Notes: ', 'cqfs'));
                                printf(
                                    '<p><b>%s</b>%s</p>',
                                    esc_html($additional_txt),
                                    esc_html($question['note'])
                                );
                            }
                            
                        ?>       
                        
                    </div>
                    <?php
                    // var_dump($compare);    
                
                $i++;
                endforeach;

                wp_reset_postdata();

                //display user display name
                if( isset($param['_cqfs_uname']) ){
                    Util::cqfs_display_uname( $param['_cqfs_uname'] );
                }

                //display the pass/fail result
                Util::cqfs_quiz_result( count($cqfs_build['all_questions']), count($numCorrects), $cqfs_build['pass_percent'], $cqfs_build['pass_msg'], $cqfs_build['fail_msg'] );

                //close the result div
                echo '</div><!-- cqfs result end -->';

            }


        }elseif( isset($param['_cqfs_status']) && isset($param['_cqfs_id']) && $param['_cqfs_status'] === 'success' && $param['_cqfs_id'] === $cqfs_build['id'] ){
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
			$cqfs_status = ['_cqfs_status' => urlencode(sanitize_text_field('failure'))];
			wp_safe_redirect(
				esc_url_raw(
					add_query_arg( $cqfs_status, wp_unslash(esc_url(strtok($values['_wp_http_referer'], '?')) ) )
				)
			);
			exit();

		}else{
			/**
             * sanitize and prepare data for new post creation
             */
            $id = sanitize_text_field( $values['_cqfs_id'] );
        
            //count total entries in POST arr
            $count = count($values);
            
            //filter out the nonce, referer, action and keep the remaining items
			$filter_out_nonce_action = array_slice($values, 0, ($count - 3), true );
			// var_dump($filter_out_nonce_action);

            //callback function for the array map url encoded
            $arrayMapCallback = function( $val ){
                if( is_array( $val ) ){
                    return urlencode(implode(",",$val));
                }else{
                    return urlencode( $val );
                }
			};

            //prepare the array for the redirection query url encoded
            $redirect_args = array_map( $arrayMapCallback, $filter_out_nonce_action );
            
            //add a success field to the redirect args
			$redirect_args['_cqfs_status'] = urlencode(sanitize_text_field('success'));
            // var_dump($redirect_args);//urlencode array

            /****************************************
             * prepare post variables
             ****************************************/

            //main build object array
            $cqfs_build = Util::cqfs_build_obj( $id );

            $questionsArr = []; //prepare question entries as array
            $numCorrects = []; //holds the ture only
            $ansStatus = []; //holds all answers boolean as true false


            //prepare answers
            $remove_non_array_callback = function( $val ){
                return is_array($val);
            };
            $userAnswers = array_values(array_filter($values, $remove_non_array_callback));
            // var_dump($userAnswers);
            $answersArr = [];

            $i = 0;
            foreach( $cqfs_build['all_questions'] as $question ){
                //push questions
                $questionsArr[] = $question['question'];

                //check answers and return boolean
                $compare = Util::cqfs_array_equality_check( $question['answers'], $userAnswers[$i] );
                //push answer status
                $ansStatus[] = $compare ? 'Correct Answer' : 'Wrong Answer';
                //push true values
                if($compare){
                    $numCorrects[] = $compare;
                }

                // $user_ans = explode(",", $userAnswers[$i]);
                //push answer string in array
                $answers = [];
                foreach($userAnswers[$i] as $ans){
                    $answers[] = $question['options'][$ans-1];
                }
                //now implode to string and push
                $answersArr[] = sanitize_text_field(implode(" | ", $answers));

                $i++;
            }

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
            $result = $percentage >= $cqfs_build['pass_percent'] ? 'Passed' : 'Failed';

            //5. each answer status
            $ansStatus = implode("\n", $ansStatus);
            
var_dump($ansStatus);



            $post_array = array(
                'post_title'    => sanitize_text_field('Cqfs Entry'),
                'post_status'   => 'publish',
                'post_type'     => 'cqfs_entries',
                'meta_input'    => array(
                    'cqfs_entry_form_id'    => $id,
                    'cqfs_entry_form_type'  => sanitize_text_field($cqfs_build['type']),
                    'cqfs_entry_result'     => sanitize_text_field($result),
                    'cqfs_entry_percentage' => sanitize_text_field($percentage),
                    'cqfs_entry_questions'  => wp_kses($questionsArr, 'post'),
                    'cqfs_entry_answers'    => wp_kses($answersArr, 'post'),
                    'cqfs_entry_status'     => wp_kses($ansStatus, 'post'),
                    'cqfs_entry_user_email' => '',
                ),
            );
            
            if( isset($values['_cqfs_uname']) && $values['_cqfs_uname'] != '' ){
                $post_array['post_title'] = sanitize_text_field($values['_cqfs_uname']);
            }
            
            if( is_user_logged_in() ){
                $current_user = wp_get_current_user();
                $post_array['meta_input']['cqfs_entry_user_email'] = sanitize_email( $current_user->user_email );
            }elseif( isset($values['_cqfs_email']) ){
                $post_array['meta_input']['cqfs_entry_user_email'] = sanitize_email($values['_cqfs_email']);
            }
            
            // Insert the post into the database
            wp_insert_post( $post_array );
    
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
    


}

$shortcode = new CqfsShortcode;
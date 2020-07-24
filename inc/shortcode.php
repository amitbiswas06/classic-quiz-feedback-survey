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
        
        var_dump( Util::cqfs_build_obj( $atts['id'] ) );

        $type = get_field('cqfs_build_type', $atts['id']);//select
        $category = get_field('cqfs_select_questions', $atts['id']);//taxonomy, returns ID
        $question_order = get_field('cqfs_question_order', $atts['id']);//ASC, DSC
        $layout = get_field('cqfs_layout_type', $atts['id']);//select, multi/single
        $pass_percent = get_field('cqfs_pass_percentage', $atts['id']);//pass percentage
        $pass_msg = get_field('cqfs_pass_message', $atts['id']);//pass message
        $fail_msg = get_field('cqfs_fail_message', $atts['id']);//fail message
           
        $questions = get_posts(
            array(
                'numberposts'   => -1,
                'post_type'     => 'cqfs_question',
                'category'      => esc_attr($category),
                'order'         => esc_attr($question_order)
            )
        );

        $class = esc_attr($type);
        $class .= ' ' . esc_attr($layout);

        //get parameters
        $param = filter_input_array(INPUT_GET, FILTER_SANITIZE_STRING);

        ob_start(); 
        
        //check parameters
        if ( !isset($param['_cqfs_status']) || !isset($param['_cqfs_id']) || $param['_cqfs_status'] !== 'success' || $param['_cqfs_id'] !== $atts['id'] ) { 
            //display the form
        ?>
        <!-- cqfs start -->
        <div id="cqfs-<?php echo esc_attr($atts['id']); ?>" class="cqfs <?php echo $class; ?>" data-cqfs-layout = <?php echo esc_attr($layout); ?>>
            <?php 
            if( $atts['title'] !== 'false' ){
                printf(
                    '<h2 class="cqfs--title">%s</h2>',
                    esc_html( get_the_title($atts['id']) )
                );
            }
            ?>
            <form id="cqfs-form-<?php echo esc_attr($atts['id']); ?>" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
            <div class="cqfs--questions" >
                <?php 
                // var_dump($questions); 
                    if($questions){
                        $i = 1;
                        foreach($questions as $post) :
                            setup_postdata( $post );

                            $answers = get_field('cqfs_answers', $post->ID);//textarea, create each line.
                            $ansArr = explode("\n", $answers); //converted to array

                            $ans_type = get_field('cqfs_answer_type', $post->ID);//select. radio, checkbox.
                            
                            $correct_ans = get_field('cqfs_correct_answer', $post->ID);//comma separated number.
                            $ansCorrect = explode(",", str_replace(' ', '', $correct_ans));//converted to array

                            $note = get_field('cqfs_additional_note', $post->ID);//textarea. show only for quiz.

                            $show_first = $i == 1 ? 'show' : 'hide';
                            
                        ?>
                        <div class="question <?php echo $layout === 'multi' ? esc_attr($show_first) : ''; ?>">
                            <?php
                                printf(
                                    '<h3 class="question--title">%s %s</h3>',
                                    esc_html($i) . '&#46; ',
                                    esc_html( get_the_title($post->ID) )
                                );

                                //display featured image if there is any
                                if( has_post_thumbnail( $post->ID )){
                                    echo wp_kses( get_the_post_thumbnail($post->ID, 'medium_large'), 'post');
                                }
                                
                            ?>
                            <div class="options">
                        
                                <?php if($ansArr) {
                                    $j = 1;
                                    foreach($ansArr as $ans) {
                                        
                                    ?>
                                <div class="input-wrap">
                                    <input name="option<?php echo $i; ?>[]" type="<?php echo esc_attr($ans_type); ?>" id="<?php echo Util::cqfs_slug($ans); ?>" value="<?php echo $j; ?>">
                                    <label for="<?php echo Util::cqfs_slug($ans); ?>"><?php echo esc_html($ans); ?></label>
                                </div>
                                <?php $j++; }} ?>
                                
                            </div>
                        </div>
                        <?php
                        $i++;
                        endforeach;
                        wp_reset_postdata();

                        //if not logged in, display user info form
                        Util::cqfs_user_info_form($atts['id'], '', $layout);

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
                            esc_attr( $atts['id'] )
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
                if($layout === 'multi') { ?>
                    <button class="cqfs--next disabled" disabled><?php echo apply_filters( 'cqfs_next_text', esc_html__('Next','cqfs') ); ?></button>
                    <button class="cqfs--prev disabled" disabled><?php echo apply_filters( 'cqfs_prev_text', esc_html__('Prev','cqfs') ); ?></button>
                    <button class="cqfs--submit disabled" type="submit" disabled><?php echo apply_filters( 'cqfs_submit_text', esc_html__('Submit','cqfs') ); ?></button>
                <?php }else{ ?>
                    <button class="cqfs--submit" type="submit"><?php echo apply_filters( 'cqfs_submit_text', esc_html__('Submit','cqfs') ); ?></button>
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
        <?php } elseif( $type === 'quiz' && isset($param['_cqfs_status']) && isset($param['_cqfs_id']) && $param['_cqfs_status'] === 'success' && $param['_cqfs_id'] === $atts['id'] ) {
            
            /**
             * Result display
             */

            $count = count($param);
            $userAnswers = array_values( array_slice($param, 0, ($count - 2), true ) );
            // var_dump($userAnswers);
            //empty array for correct ans
            $numCorrects = [];

            if($questions){

                echo '<!-- cqfs result start --><div class="cqfs-results">';

                $i = 0;
                foreach($questions as $post) :
                    setup_postdata( $post );
                    
                    $answers = get_field('cqfs_answers', $post->ID);//textarea, create each line.
                    $ansArr = explode("\n", $answers); //converted to array
                    $correct_ans = get_field('cqfs_correct_answer', $post->ID);//comma separated number.
                    $ansCorrect = explode(",", str_replace(' ', '', $correct_ans));//converted to array
                    $note = get_field('cqfs_additional_note', $post->ID);//textarea. show only for quiz.
                    $user_ans = explode(",", $userAnswers[$i]);

                    // var_dump($user_ans);
                    // var_dump($ansCorrect);

                    //check answers and return boolean
                    $compare = Util::cqfs_array_equality_check( $ansCorrect, $user_ans );
                    // var_dump($compare);
                    
                    //push to empty array for correct answers
                    if($compare){
                        $numCorrects[] = $compare;
                    }

                    ?>
                    <div class="cqfs-results--each">
                        <?php
                            printf(
                                '<h3 class="question--title">%s %s</h3>',
                                esc_html($i+1) . esc_html__('&#46; ','cqfs'),
                                esc_html( get_the_title($post->ID) )
                            );
                        ?>
                        <p>
                            <label><?php echo esc_html__('You Answered ','cqfs'); ?></label>
                            <?php foreach( $user_ans as $ans ){ ?>
                                <span><?php echo esc_html($ansArr[$ans-1]); ?></span><br>
                            <?php } ?>
                        </p>
                        <?php
                            printf(
                                '<p><label>%s</label><span>%s</span></p>',
                                esc_html__('Status: ','cqfs'),
                                $compare ? esc_html__('Correct Answer', 'cqfs') : esc_html__('Wrong Answer', 'cqfs')
                            );

                            if( !empty($note[$i] )){
                                printf(
                                    '<p><b>%s</b>%s</p>',
                                    apply_filters('cqfs_additional_notes', esc_html__('Additional Notes: ', 'cqfs')),
                                    esc_html($note)
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
                Util::cqfs_quiz_result( count($questions), count($numCorrects), $pass_percent, $pass_msg, $fail_msg );

                //close the result div
                echo '</div><!-- cqfs result end -->';

            }


        }elseif( isset($param['_cqfs_status']) && isset($param['_cqfs_id']) && $param['_cqfs_status'] === 'success' && $param['_cqfs_id'] === $atts['id'] ){
            /**
             * Message for non quiz cqfs
             */
            printf(
                '<div class="cqfs-results"><h4>%s</h4></div>',
                apply_filters('cqfs_thankyou_message', esc_html__('Thank you for participating.', 'cqfs'))
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

		//get the nonce
		$nonce = sanitize_text_field($values['_cqfs_nonce']);

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
             * Do the all validations and prepare data for new post creation
             */

            //count total entries in POST arr
            $count = count($values);
            
            //filter out the nonce, referer, action and keep the remaining items
			$filter_out_nonce_action = array_slice($values, 0, ($count - 3), true );
			// var_dump($userAnswers);

            //callback function for the array map
            $arrayMapCallback = function( $val ){
                if( is_array( $val ) ){
                    return urlencode(implode(",",$val));
                }else{
                    return urlencode( $val );
                }
			};

            //prepare the array for the redirection query
            $redirect_args = array_map( $arrayMapCallback, $filter_out_nonce_action );
            
            //add a success field to the redirect args
			$redirect_args['_cqfs_status'] = urlencode(sanitize_text_field('success'));
            // var_dump($redirect_args);

            /**
             * Create the post
             */
            $post_array = array(
                'post_title'    => sanitize_text_field('Cqfs Entry'),
                'post_status'   => 'publish',
                'post_type'     => 'cqfs_entries',
                'meta_input'    => array(
                    'cqfs_entry_form_id'    => sanitize_text_field($values['_cqfs_id']),
                    'cqfs_entry_form_type'  => '',
                    'cqfs_entry_result'     => '',
                    'cqfs_entry_percentage' => '',
                    'cqfs_entry_questions'  => '',
                    'cqfs_entry_answers'    => '',
                    'cqfs_entry_status'     => '',
                    'cqfs_entry_user_email' => sanitize_email($values['_cqfs_email']),
                ),
            );
            
            if( isset($values['_cqfs_uname']) && $values['_cqfs_uname'] != '' ){
                $post_array['post_title'] = sanitize_text_field($values['_cqfs_uname']);
            }
            
            if( is_user_logged_in() ){
                
            }
            
            // Insert the post into the database
            wp_insert_post( $post_array );
    
            /**
             * Redirect the page and exit
             */
			/* wp_safe_redirect(
				esc_url_raw(
					add_query_arg( $redirect_args, wp_unslash( esc_url( strtok( $values['_wp_http_referer'], '?' ) ) ) )
				)
			);
    
            //exit immediately
			exit(); */
		}

    }
    


}

$shortcode = new CqfsShortcode;
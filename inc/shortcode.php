<?php
/**
 * Build the CQFS shortcode
 * @since 1.0.0
 */
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
     * prepare the clean slug for use
     * 
     * @param string $text any text
     * @return slug
     */
    public static function cqfs_slug( $text ){
        $text = strtolower( $text );
        $text = preg_replace( '/[^a-z0-9 -]+/', '', $text );
        $text = str_replace( ' ', '-', $text );
        return trim( $text, '-' );
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
                                    <input name="option<?php echo $i; ?>[]" type="<?php echo esc_attr($ans_type); ?>" id="<?php echo self::cqfs_slug($ans); ?>" value="<?php echo $j; ?>">
                                    <label for="<?php echo self::cqfs_slug($ans); ?>"><?php echo esc_html($ans); ?></label>
                                </div>
                                <?php $j++; }} ?>
                                
                            </div>
                        </div>
                        <?php
                        $i++;
                        endforeach;
                        wp_reset_postdata();

                        self::cqfs_user_info_form($atts['id'], '', $layout);

                        //form ID
                        printf(
                            '<input type="hidden" name="_cqfs_id" value="%s">',
                            esc_attr( $atts['id'] )
                        );

                        //action input
                        printf('<input type="hidden" name="action" value="cqfs_response">');
                        
                        //create nonce
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
                    $compare = self::cqfs_array_equality_check( $ansCorrect, $user_ans );
                    var_dump($compare);
                    
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

                //display the pass/fail
                self::cqfs_quiz_result( count($questions), count($numCorrects), $pass_percent, $pass_msg, $fail_msg );

                //close the result div
                echo '</div><!-- cqfs result end -->';

            }


        }elseif( isset($param['_cqfs_status']) && isset($param['_cqfs_id']) && $param['_cqfs_status'] === 'success' && $param['_cqfs_id'] === $atts['id'] ){
                
            printf(
                '<div class="cqfs-results"><h4>%s</h4></div>',
                apply_filters('cqfs_thankyou_message', esc_html__('Thank you for participating.', 'cqfs'))
            );
        } ?>
        <?php
        
        return ob_get_clean();
        
    }


    /**
     * Evaluate quiz result
     * 
     * @param int     $totalQuestions     number of questions
     * @param int     $correctAnswers     number of correct answer
     * @param int     $passPercentage     pass percentage (default 50)
     * @param string  $passMsg            message if pass (optional)
     * @param string  $failMsg            message if failed (optional)
     * @return string printed
     */
    public static function cqfs_quiz_result( $totalQuestions, $correctAnswers, $passPercentage = 50, $passMsg = "", $failMsg = "" ){

        //percentage var
        $percentage = 0;

        if( $totalQuestions >= $correctAnswers ){
            $percentage = round($correctAnswers * 100 / $totalQuestions);
        }

        if( $percentage >= $passPercentage ){
            //pass message
            printf(
                __('<div class="cqfs-pass-msg"><p class="cqfs-percentage">%s correct.</p><p>%s</p></div>', 'cqfs'),
                esc_html($percentage) . esc_html__("&#37;", 'cqfs'),
                $passMsg != '' ? esc_html( $passMsg ) : apply_filters( 'cqfs_pass_msg', esc_html__('Congratulations! You have passed.', 'cqfs'))
            );

        }else{
            //fail message
            printf(
                __('<div class="cqfs-fail-msg"><p class="cqfs-percentage">%s correct.</p><p>%s</p></div>', 'cqfs'),
                esc_html($percentage) . esc_html__("&#37;", 'cqfs'),
                $failMsg != '' ? esc_html( $failMsg ) : apply_filters( 'cqfs_fail_msg', esc_html__('Sorry! You have failed.', 'cqfs'))
            );
        }

    }

    /**
     * Check two normal or multidimentional arrays
     * 
     * @param array $array1 required array
     * @param array $array2 required array
     * @return boolean true if same values are there regardless of key positions.
     */
    public static function cqfs_array_equality_check($array1, $array2){

        if( is_array($array1) && is_array($array2 ) ){
            array_multisort($array1);
            array_multisort($array2);
            return ( serialize($array1) === serialize($array2) );
        }else{
            return false;
        }
            
    }

    public static function cqfs_user_info_form( $id, $legal = "", $layout_type = "multi" ){
        if( !is_user_logged_in() ){

            if( $layout_type === 'multi' ){
                echo '<div class="cqfs-user-form hide">';
            }else{
                echo '<div class="cqfs-user-form">';
            }

            printf(
                '<p class="cqfs-user-form--msg">%s</p>',
                apply_filters( 'cqfs_user_form_msg', esc_html__('Please provide the following info.', 'cqfs'))
            );
            //display identity form
            //name field
            printf(
                '<label for="%s">%s</label><input id="%s" name="_cqfs_uname" type="text" placeholder="%s" required>',
                'uname_' . esc_attr( $id ),
                esc_html__('Your Name &#42;', 'cqfs'),
                'uname_' . esc_attr( $id ),
                esc_html__('please type your name.', 'cqfs')
            );

            //email field
            printf(
                '<label for="%s">%s</label><input id="%s" name="_cqfs_email" type="email" placeholder="%s" required>',
                'uemail_' . esc_attr( $id ),
                esc_html__('Your Email &#42;', 'cqfs'),
                'uemail_' . esc_attr( $id ),
                esc_html__('please type email.', 'cqfs')
            );

            printf(
                '<div class="cqfs-user-form--legal">%s</div>',
                $legal ? wp_kses($legal, 'post') : ''
            );

            echo '</div>';

        }

    }

    /**
	 * CQFS form handle
     * via `admin-post.php`
	 */
	public function cqfs_form_submission(){

		//sanitize the global POST var. XSS ok.
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
			
			$count = count($values);
			var_dump($values);

			$userAnswers = array_slice($values, 0, ($count - 3), true );
			// var_dump($userAnswers);

			$resultMap = function( $val ){
                if( is_array( $val ) ){
                    return urlencode(implode(",",$val));
                }else{
                    return urlencode( $val );
                }
			};

			$resultsArray = array_map($resultMap, $userAnswers);
			$resultsArray['_cqfs_status'] = urlencode(sanitize_text_field('success'));
            var_dump($resultsArray);
            
            $post_arr = array(
                'post_title'   => 'Test post',
                'post_content' => 'Test post content',
                'post_status'  => 'publish',
                'post_author'  => get_current_user_id(),
            );
            // Insert the post into the database
            // wp_insert_post( $post_arr );
	
			wp_safe_redirect(
				esc_url_raw(
					add_query_arg( $resultsArray, wp_unslash(esc_url(strtok($values['_wp_http_referer'], '?') ) ) )
				)
			);
	
			exit();
		}

    }
    


}

$shortcode = new CqfsShortcode;
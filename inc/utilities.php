<?php
/**
 * Main Utility class for CQFS
 */

//define namespace
namespace CQFS\INC\UTIL;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Utilities{

    public static $allowed_in_table = array(
        'br'    => array(),
        'a'     => array(
            'href'  => array(),
            'title' => array(),
            'style' => array(),
        ),
        'b'     => array(),
        'em'    => array(),
        'span'  => array(
            'style' => array(),
        ),
    );

    /**
     * prepare the clean slug for use
     * 
     * @param string $text any text
     * @return string slug
     */
    public static function cqfs_slug( $text ){
        $text = strtolower( $text );
        $text = preg_replace( '/[^a-z0-9 -]+/', '', $text );
        $text = str_replace( ' ', '-', $text );
        return trim( $text, '-' );
    }


    /**
     * Array of the main CQFS build post
     * 
     * @param int $cqfs_build_id    The cqfs_build post ID
     * @return array                Array of the build post
     */
    public static function cqfs_build_obj( $cqfs_build_id ){

        if( !$cqfs_build_id || is_null($cqfs_build_id) || get_post_status( $cqfs_build_id ) === false ){
            return null;
        }

        //the main build array
        $the_cqfs_build = [];

        $type = get_post_meta($cqfs_build_id, 'cqfs_build_type', true);//select
        $category = get_post_meta($cqfs_build_id, 'cqfs_select_questions', true);//taxonomy, returns ID
        $question_order = get_post_meta($cqfs_build_id, 'cqfs_question_order', true);//ASC, DSC
        $layout = get_post_meta($cqfs_build_id, 'cqfs_layout_type', true);//select, multi/single
        $pass_percent = get_post_meta($cqfs_build_id, 'cqfs_pass_percentage', true);//pass percentage
        $pass_msg = get_post_meta($cqfs_build_id, 'cqfs_pass_message', true);//pass message
        $fail_msg = get_post_meta($cqfs_build_id, 'cqfs_fail_message', true);//fail message

        $class = $type;
        $class .= ' ' . $layout;

        //insert build data
        $the_cqfs_build['title'] = get_the_title($cqfs_build_id);
        $the_cqfs_build['type'] = $type;
        $the_cqfs_build['qst_category'] = $category;
        $the_cqfs_build['qst_order'] = $question_order;
        $the_cqfs_build['layout'] = $layout;
        $the_cqfs_build['pass_percent'] = $pass_percent;
        $the_cqfs_build['pass_msg'] = $pass_msg;
        $the_cqfs_build['fail_msg'] = $fail_msg;
        $the_cqfs_build['classname'] = $class;
        $the_cqfs_build['id'] = $cqfs_build_id;

        //get questions by category
        $questions = get_posts(
            array(
                'numberposts'   => -1,
                'post_type'     => 'cqfs_question',
                'category'      => esc_attr($category),
                'order'         => esc_attr($question_order)
            )
        );

        //insert question array now
        if($questions){

            foreach($questions as $post) :
                setup_postdata( $post );

                $options = get_post_meta($post->ID, 'cqfs_answers', true);//textarea, create each line.
                $options_arr = explode("\n", $options); //converted to array

                $option_type = get_post_meta($post->ID, 'cqfs_answer_type', true);//select. radio, checkbox.
                
                $correct_ans = get_post_meta($post->ID, 'cqfs_correct_answer', true);//comma separated number.
                $correct_ans_arr = explode(",", str_replace(' ', '', $correct_ans));//converted to array

                $note = get_post_meta($post->ID, 'cqfs_additional_note', true);//textarea. show only for quiz.

                //prepare the question array
                $the_cqfs_build['all_questions'][] = array(
                    'question'      => get_the_title( $post->ID ),
                    'id'            => $post->ID,
                    'options'       => $options_arr,
                    'input_type'    => $option_type,
                    'answers'       => $correct_ans_arr,
                    'note'          => $note,
                    'thumbnail'     => has_post_thumbnail( $post->ID ),
                );
                
            endforeach;
            wp_reset_postdata();
        }

        //return final question array obj
        return $the_cqfs_build;

    }


    /**
     * Array of the main CQFS entry post
     * 
     * @param int $cqfs_entry_id the ID of the cpt cqfs_entry
     * @return array of the cqfs_entry post
     */
    public static function cqfs_entry_obj( $cqfs_entry_id ){

        if( !$cqfs_entry_id || is_null($cqfs_entry_id) || get_post_status( $cqfs_entry_id ) === false ){
            return null;
        }

        //the main entry array
        $the_cqfs_entry = [];

        $email = get_post_meta($cqfs_entry_id, 'cqfs_entry_user_email', true );//email
        $form_id = get_post_meta($cqfs_entry_id, 'cqfs_entry_form_id', true );//text
        $form_type = get_post_meta($cqfs_entry_id, 'cqfs_entry_form_type', true );//text
        $result = get_post_meta($cqfs_entry_id, 'cqfs_entry_result', true );//checkbox
        $percentage = get_post_meta($cqfs_entry_id, 'cqfs_entry_percentage', true );//text
        $remarks = get_post_meta($cqfs_entry_id, 'cqfs_entry_remarks', true);//text message for pass fail

        //handle arrays
        $questions = get_post_meta($cqfs_entry_id, 'cqfs_entry_questions', true );//textarea with line break
            //convert to array
            $questions = preg_split('/\r\n|\r|\n/', $questions);

        $answers = get_post_meta($cqfs_entry_id, 'cqfs_entry_answers', true );//textarea with line break
            //convert to array
            $answers = preg_split('/\r\n|\r|\n/', $answers);
            
        $status = get_post_meta($cqfs_entry_id, 'cqfs_entry_status', true );//textarea with line break
            //convert to array
            $status = preg_split('/\r\n|\r|\n/', $status);

        $notes = get_post_meta($cqfs_entry_id, 'cqfs_entry_notes', true );//textarea with line break
            //convert to array
            $notes = preg_split('/\r\n|\r|\n/', $notes);
        

        //insert into blank array

        $the_cqfs_entry['id'] = esc_attr( $cqfs_entry_id );//the id
        $the_cqfs_entry['user_title'] = esc_html( get_the_title( $cqfs_entry_id ) );//title as user_title
        $the_cqfs_entry['email'] = sanitize_email( $email );//email
        $the_cqfs_entry['form_id'] = esc_attr( $form_id );//form id
        $the_cqfs_entry['form_type'] = esc_html( $form_type );//form type
        $the_cqfs_entry['result'] = esc_html($result);//radio. passed/failed
        $the_cqfs_entry['percentage'] = esc_html( $percentage );//percentage
        $the_cqfs_entry['remarks']  = esc_html( $remarks );//pass-fail message as remarks

        //prepare and insert array items
        $counter = 0;
        foreach( $questions as $qst ){

            $the_cqfs_entry['all_questions'][] = array(

                'question'  => esc_html( $qst ),
                'answer'    => esc_html( str_replace( ' |',',' , $answers[$counter]) ),
                'status'    => esc_html( $status[$counter] ),
                'note'      => esc_html( $notes[$counter] ),

            );

            $counter++;
        }

        //return the array
        return $the_cqfs_entry;

    }


    /**
     * Evaluate quiz result
     * 
     * @param int     $passPercentage     pass percentage
     * @param int     $accPercentage      accumulated percentage
     * @param string  $passMsg            message if pass (optional)
     * @param string  $failMsg            message if failed (optional)
     * @return html                       string escaped.
     */
    public static function cqfs_quiz_result( $passPercentage, $accPercentage, $passMsg = "", $failMsg = "" ){

        if( $accPercentage >= $passPercentage ){
            //pass message
            $default_pass_msg = apply_filters( 'cqfs_pass_msg', esc_html__('Congratulations! You have passed.', 'cqfs'));
            return sprintf(
                __('<div class="cqfs-pass-msg"><p class="cqfs-percentage">%s correct.</p><p>%s</p></div>', 'cqfs'),
                esc_html($accPercentage) . esc_html__("&#37;", 'cqfs'),
                $passMsg != '' ? esc_html( $passMsg ) : esc_html( $default_pass_msg )
            );

        }else{
            //fail message
            $default_fail_msg = apply_filters( 'cqfs_fail_msg', esc_html__('Sorry! You have failed.', 'cqfs'));
            return sprintf(
                __('<div class="cqfs-fail-msg"><p class="cqfs-percentage">%s correct.</p><p>%s</p></div>', 'cqfs'),
                esc_html($accPercentage) . esc_html__("&#37;", 'cqfs'),
                $failMsg != '' ? esc_html( $failMsg ) : esc_html( $default_fail_msg )
            );
        }

    }


    /**
     * Displays the user name at result page
     * 
     * @param string $username  User name provided at the form submission
     * @return html             string of the title. Escaped.
     */
    public static function cqfs_display_uname( $username ){
        return sprintf(
            '<h3 class="cqfs-uname">%s</h3>',
            esc_html__('Hello ', 'cqfs') . esc_html($username)
        );
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


    /**
     * User info form for not logged in users as guest
     * 
     * @param int $id               The ID of the form shortcode
     * @param string $layout_type   The form layout type
     * @param string $legal         The custom text/html invoked. wp_kses filtered
     * @return html                 string escaped.
     */
    public static function cqfs_user_info_form( $id, $layout_type = "multi", $guest = false, $legal = "" ){

            if( $layout_type === 'multi' ){
                echo '<div class="cqfs-user-form hide">';
            }else{
                echo '<div class="cqfs-user-form">';
            }

            if( is_user_logged_in() ){
                printf(
                    __('<p class="logged-in-msg">%s</p>', 'cqfs'),
                    esc_html__('You are logged in.', 'cqfs')
                );
            }

            // display if guest mode is on
            if( !is_user_logged_in() ){

                printf(
                    __('<a class="cqfs-modal-link" href="#">%s</a>', 'cqfs'),
                    esc_html__('Login and submit', 'cqfs')
                );

                if( $guest ){

                    //guest user message
                    $guest_user_form_msg = apply_filters( 'cqfs_guest_user_form_msg', esc_html__('Or you may submit as a guest. Please provide the following info.', 'cqfs'));
                    printf(
                        '<p class="cqfs-user-form--msg">%s</p>',
                        esc_html( $guest_user_form_msg )
                    );
                    //display identity form
                    //name field
                    $error_uname = apply_filters('cqfs_error_uname_msg', esc_html__('Invalid Name. Min 3, max 24 characters allowed.','cqfs') );
                    printf(
                        '<label for="%s">%s</label>
                        <input id="%s" name="_cqfs_uname" type="text" placeholder="%s"><div class="error-msg error-uname hide">%s</div>',
                        'uname_' . esc_attr( $id ),
                        esc_html__('Your Name &#42;', 'cqfs'),
                        'uname_' . esc_attr( $id ),
                        esc_html__('please type your name.', 'cqfs'),
                        esc_html( $error_uname )
                    );

                    //email field
                    $error_email = apply_filters('cqfs_error_uname_msg', esc_html__('Invalid Email','cqfs') );
                    printf(
                        '<label for="%s">%s</label>
                        <input id="%s" name="_cqfs_email" type="email" placeholder="%s"><div class="error-msg error-email hide">%s</div>',
                        'uemail_' . esc_attr( $id ),
                        esc_html__('Your Email &#42;', 'cqfs'),
                        'uemail_' . esc_attr( $id ),
                        esc_html__('please type email.', 'cqfs'),
                        esc_html( $error_email )
                    );

                }

            }


            //consent message html
            $consent = apply_filters('cqfs_user_form_consent', $legal );
            printf(
                '<div class="cqfs-user-form--legal">%s</div>',
                wp_kses( $consent, array(
                    //permitted html tags 
                    'a' => array(
                        'href' => array(),
                        'data' => array(),
                        'class'=> array(),
                    ),
                    'p' => array(
                        'class' => array(),
                    ),
                    'div' => array(
                        'class' => array()
                    ),
                ) )
            );

            echo '</div>';

    }


    public static function cqfs_login_submit_form(){

        // if user is logged in, return immediately
        if( is_user_logged_in() ){
            return;
        }

        ?>
        <div id="cqfs-login" class="cqfs-modal display-none transition">

            <div class="cqfs-modal-content">
                <div class="cqfs-modal-header">
                    <span class="cqfs-close"><?php echo esc_html__('&times;','cqfs'); ?></span>
                    <h3><?php echo esc_html__('Please login to submit','cqfs'); ?></h3>
                </div>
                <div class="cqfs-modal-body">
                    <form name="cqfs-login-form" action="<?php echo esc_url( admin_url('admin-ajax.php') ); ?>" method="post">
                        <div class="hidden-fields">
                            <?php
                            // insert hidden action input
                            printf('<input type="hidden" name="action" value="%s">', esc_attr('cqfs_login'));
                            // create nonce field
                            wp_nonce_field( 'cqfs_login', '_cqfs_login_nonce' );
                            ?>
                        </div>
                        <fieldset>
                            <legend><?php echo esc_html__('Secure Login','cqfs'); ?></legend>
                            <div class="cqfs-login-input">
                                <label for="cqfs_login_username"><?php echo esc_html__('Username or email', 'cqfs'); ?></label>
                                <input type="text" name="cqfs_username" id="cqfs_login_username" autocapitalize="off" size="20" required>
                            </div>
                            <div class="cqfs-login-input">
                                <label for="cqfs_login_password"><?php echo esc_html__('Password', 'cqfs'); ?></label>
                                <input type="password" name="cqfs_password" id="cqfs_login_password" required>
                            </div>
                            <button type="submit"><?php echo esc_html__('Login','cqfs'); ?></button>
                        </fieldset>
                    </form>
                    <div class="cqfs-alert-message display-none transition"></div>
                </div>
                <div class="cqfs-modal-footer">
                    <p class="cqfs-forget-password">
                        <a href="<?php echo esc_url( wp_lostpassword_url() ); ?>"><?php echo esc_html__('Forgot password? Click here.','cqfs'); ?></a>
                    </p>
                </div>
            </div>
            
        </div>
        
        <?php

    }


    /**
     * Fires Email
     * 
     * @param string|array $to
     * @param string $subject
     * @param string $body
     * @return bool
     */
    public static function cqfs_mail( $to, $subject = '', $body = '' ){

        $blog_name = apply_filters( 'cqfs_site_name', esc_html(get_bloginfo('name')) );
        $admin_email = apply_filters( 'cqfs_admin_email', sanitize_email(get_bloginfo('admin_email')) );
        $status = false;

        if( $to ){

            if( !empty($subject) ){
                $subject = $subject;
            }else{
                $subject = apply_filters( 'cqfs_default_subject', esc_html__('Classic quiz feedback survey','cqfs'));
            }
            
            $headers = array();
            $headers[] = "From: " . esc_html($blog_name) . " <" . sanitize_email($admin_email) . ">";
            $headers[] = "Content-Type: text/html; charset=UTF-8";
 
            $status = wp_mail( sanitize_email($to), esc_html($subject), $body, $headers );

        }

        return $status;

    }


    public static function cqfs_mail_body( $cqfs_build_id, $cqfs_entry_id, $is_admin = false ){

        $build_obj = self::cqfs_build_obj( $cqfs_build_id );
        $entry_obj = self::cqfs_entry_obj( $cqfs_entry_id );

        // prepare email body

        $home_url = home_url('/');
        $result_page_obj = get_page_by_path(CQFS_RESULT);
        $result_page_url = "";

        if( !null == $result_page_obj ){
            
            $result_page_url = esc_url( $home_url ) . '?page_id=' . esc_attr( $result_page_obj->ID );
            $result_page_url .= "&cqfs_entry=";
            $result_page_url .= esc_attr($cqfs_entry_id);
            $result_page_url .= "&email=";
            $result_page_url .= urlencode( sanitize_email($entry_obj['email']) );

        }

        $logo_id = get_theme_mod( 'custom_logo' );
        $logo_url = wp_get_attachment_image_src( $logo_id , 'full' );//false if nothing
        $blog_name = apply_filters( 'cqfs_site_name', esc_html(get_bloginfo('name')) );
        $quiz_passed = apply_filters('cqfs_quiz_pass_msg_email', esc_html__('Congratulations! You have passed the quiz.','cqfs'));
        $quiz_failed = apply_filters('cqfs_quiz_fail_msg_email', esc_html__('Sorry! You did not passed the quiz.','cqfs'));
        $quiz_thanks = apply_filters('cqfs_quiz_thank_msg', esc_html__('Thank you for participating in the quiz. Here is your result page link below.','cqfs'));
        $feedback_msg = apply_filters('cqfs_feedback_msg_email', esc_html__('Thank you for your feedback.','cqfs'));
        $survey_msg = apply_filters('cqfs_survey_msg_email', esc_html__('Thank you for participating in the survey.','cqfs'));
        
        // admin message
        $admin_msgs = sprintf(
            __( wp_kses('Hello Admin! You just received a new "%s" entry.', self::$allowed_in_table),'cqfs'),
            esc_html($build_obj['type'])
        );
        $admin_msg = apply_filters('cqfs_admin_msg_email', $admin_msgs );

        //email additional notes
        $email_notes = wp_kses( get_option('_cqfs_mail_notes'), self::$allowed_in_table );

        //email footer content
        $email_footer = wp_kses( get_option('_cqfs_mail_footer'), self::$allowed_in_table );

        ob_start();
        ?>

    <table border="0" cellpadding="0" cellspacing="0" width="100%" 
    style="font-family: Arial, sans-serif; text-align: center; font-size: 16px;">	
		<tr>
			<td style="padding: 10px 0 30px 0;">
				<table id="cqfs-table" align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border: 1px solid #cccccc; border-collapse: collapse;">
                    <?php if($logo_url) :?>
                    <tr>
                        <td style="padding: 30px 30px 0; font-size: 28px; font-weight: 700;">
                            <img src="<?php echo esc_url($logo_url); ?>" alt="" height="100"/>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <td style="padding: 10px 10px 0;"><?php echo esc_html($blog_name); ?></td>
                    </tr>
					<tr>
						<td style="padding: 40px 30px 40px 30px;">
							<table border="0" cellpadding="0" cellspacing="0" width="100%">

                                <?php if( $is_admin ) :?>
                                <tr>
									<td style="font-size: 24px;">
										<b><?php esc_html($admin_msg); ?></b>
									</td>
								</tr>
                                <?php endif; ?>

                                <?php if( !$is_admin && $entry_obj['form_type'] != 'quiz' ) :?>
                                <tr>
									<td style="font-size: 24px;">
                                        <b><?php 
                                        switch( $entry_obj['form_type'] ){
                                            case 'feedback' : echo esc_html($feedback_msg);
                                            break;
                                            case 'survey' : echo esc_html($survey_msg);
                                            break;
                                        }
                                        ?></b>
									</td>
								</tr>
                                <?php endif; ?>

                                <?php if( !$is_admin && $entry_obj['form_type'] === 'quiz' ) :?>
								<tr>
									<td style="font-size: 24px;">
                                        <b><?php 
                                            switch( $entry_obj['result'] ){
                                                case 'passed' : echo esc_html($quiz_passed);
                                                break;
                                                case 'failed' : echo esc_html($quiz_failed);
                                                break;
                                            }
                                        ?></b>
									</td>
                                </tr>
								<tr>
									<td style="padding: 20px 0 30px 0; font-size: 16px;">
										<?php echo esc_html($quiz_thanks); ?>
									</td>
								</tr>
                                <tr>
									<td style="padding: 20px 0 30px 0; font-size: 16px;">
                                        <a href="<?php echo esc_url($result_page_url); ?>" 
                                        style="padding:12px 25px; color:#f8f8f8; background: #222222;"><?php 
                                        echo esc_html__('View Result','cqfs'); ?></a>
									</td>
                                </tr>
                                <?php endif; ?>

                                <?php if( !empty($email_notes) ) :?>
                                <tr>
                                    <td>
                                        <?php echo wp_kses( $email_notes, self::$allowed_in_table ); ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
							</table>
						</td>
					</tr>      
					<tr>
                        <td bgcolor="#dddddd" style="padding: 30px 30px 30px 30px; font-size: 14px;">
							<?php echo wp_kses( $email_footer, self::$allowed_in_table ); ?>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

        <?php
        return ob_get_clean();

    }


}
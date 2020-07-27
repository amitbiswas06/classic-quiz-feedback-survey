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

        $type = get_field('cqfs_build_type', $cqfs_build_id);//select
        $category = get_field('cqfs_select_questions', $cqfs_build_id);//taxonomy, returns ID
        $question_order = get_field('cqfs_question_order', $cqfs_build_id);//ASC, DSC
        $layout = get_field('cqfs_layout_type', $cqfs_build_id);//select, multi/single
        $pass_percent = get_field('cqfs_pass_percentage', $cqfs_build_id);//pass percentage
        $pass_msg = get_field('cqfs_pass_message', $cqfs_build_id);//pass message
        $fail_msg = get_field('cqfs_fail_message', $cqfs_build_id);//fail message

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

                $options = get_field('cqfs_answers', $post->ID);//textarea, create each line.
                $options_arr = explode("\n", $options); //converted to array

                $option_type = get_field('cqfs_answer_type', $post->ID);//select. radio, checkbox.
                
                $correct_ans = get_field('cqfs_correct_answer', $post->ID);//comma separated number.
                $correct_ans_arr = explode(",", str_replace(' ', '', $correct_ans));//converted to array

                $note = get_field('cqfs_additional_note', $post->ID);//textarea. show only for quiz.

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

        $email = get_field('cqfs_entry_user_email', $cqfs_entry_id );//email
        $form_id = get_field('cqfs_entry_form_id', $cqfs_entry_id );//text
        $form_type = get_field('cqfs_entry_form_type', $cqfs_entry_id );//text
        $result = get_field('cqfs_entry_result', $cqfs_entry_id );//checkbox
        $percentage = get_field('cqfs_entry_percentage', $cqfs_entry_id );//text
        $remarks = get_field('cqfs_entry_remarks', $cqfs_entry_id);//text message for pass fail

        //handle arrays
        $questions = get_field('cqfs_entry_questions', $cqfs_entry_id );//textarea with line break
            //convert to array
            $questions = preg_split('/\r\n|\r|\n/', $questions);

        $answers = get_field('cqfs_entry_answers', $cqfs_entry_id );//textarea with line break
            //convert to array
            $answers = preg_split('/\r\n|\r|\n/', $answers);
            
        $status = get_field('cqfs_entry_status', $cqfs_entry_id );//textarea with line break
            //convert to array
            $status = preg_split('/\r\n|\r|\n/', $status);

        $notes = get_field('cqfs_entry_notes', $cqfs_entry_id );//textarea with line break
            //convert to array
            $notes = preg_split('/\r\n|\r|\n/', $notes);
        

        //insert into blank array

        $the_cqfs_entry['id'] = esc_attr( $cqfs_entry_id );//the id
        $the_cqfs_entry['username'] = esc_html( get_the_title( $cqfs_entry_id ) );//title as username
        $the_cqfs_entry['email'] = esc_html( $email );//email
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
            $default_pass_msg = apply_filters( 'cqfs_default_pass_msg', esc_html__('Congratulations! You have passed.', 'cqfs'));
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
    public static function cqfs_user_info_form( $id, $layout_type = "multi", $legal = "" ){
        if( !is_user_logged_in() ){

            if( $layout_type === 'multi' ){
                echo '<div class="cqfs-user-form hide">';
            }else{
                echo '<div class="cqfs-user-form">';
            }

            //guest user message
            $guest_user_form_msg = apply_filters( 'cqfs_guest_user_form_msg', esc_html__('Hello Guest&#33; please provide the following info&#46;', 'cqfs'));
            printf(
                '<p class="cqfs-user-form--msg">%s</p>',
                esc_html( $guest_user_form_msg )
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

    }


}
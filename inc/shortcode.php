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

        //require the sublission handle
        require CQFS_PATH . 'inc/submission.php';
        
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

            /*****************************************/
            //check if duplicate email found! (restrict to 1 email 1 entry)
            /* if( isset($param['_cqfs_duplicate']) && $param['_cqfs_duplicate'] ){
                $duplicate_msg = apply_filters('cqfs_duplicate_entry', esc_html__('Sorry, you cannot submit with same email again.', 'cqfs'));
                printf(
                    '<p class="cqfs-duplicate-entry">%s</p>',
                    esc_html( $duplicate_msg )
                );
            
            } */
            /*****************************************/

        }
        

        $classes = $cqfs_build['classname'];
        $classes .= is_user_logged_in() ? ' ' . 'cqfs-logged-in' : '';
        ?>
        <!-- cqfs start -->
        <div id="cqfs-<?php echo esc_attr($cqfs_build['id']); ?>" class="cqfs <?php echo esc_attr($classes) ?>" data-cqfs-layout = <?php echo esc_attr($cqfs_build['layout']); ?>>
            <?php 
            if( $atts['title'] !== 'false' ){
                printf(
                    '<h2 class="cqfs-title">%s</h2>',
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
                                        //unique id for each build question input
                                        $inp_id = Util::cqfs_slug($optn) . '_' . $cqfs_build['id'] . '_' . $question['id'];
                                    ?>
                                <div class="input-wrap">
                                    <input name="cqfs[<?php echo esc_attr($question['id']); ?>][]" type="<?php echo esc_attr($question['input_type']); ?>" id="<?php echo esc_attr($inp_id); ?>" value="<?php echo $j; ?>">
                                    <label for="<?php echo esc_attr($inp_id); ?>"><?php echo esc_html($optn); ?></label>
                                </div>
                                <?php $j++; }} ?>
                                
                            </div>
                        </div>
                        <?php
                        $i++;
                        endforeach;

                        //allow guest checkbox value - boolean
                        $allow_guest = esc_attr( get_option('_cqfs_allow_all') );
                        //if not logged in, display user info form
                        Util::cqfs_user_info_form( $cqfs_build['id'], $cqfs_build['layout'], $allow_guest );

                        //insert form ID in a hidden field
                        printf(
                            '<input type="hidden" name="_cqfs_id" value="%s">',
                            esc_attr( $cqfs_build['id'] )
                        );

                        //insert hidden action input
                        printf('<input type="hidden" name="action" value="%s">', esc_attr('cqfs_response'));
                        
                        //create unique nonce fields for every form
                        $nonce_action = esc_attr('_cqfs_post_');
                        $nonce_name = esc_attr('_cqfs_nonce_') . esc_attr($cqfs_build['id']);
                        wp_nonce_field( $nonce_action, $nonce_name );

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
        <?php 
        
        return ob_get_clean();
        
    }


}

$shortcode = new CqfsShortcode;
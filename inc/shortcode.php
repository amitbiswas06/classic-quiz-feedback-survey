<?php
/**
 * Build the CQFS shortcode
 * @since 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

add_shortcode( 'cqfs', array( 'CqfsShortcode', 'cqfs_shortcode' ) );

class CqfsShortcode {

    //prepare the clean slug for use
    public static function cqfs_slug( $text ){
        $text = strtolower( $text );
        $text = preg_replace( '/[^a-z0-9 -]+/', '', $text );
        $text = str_replace( ' ', '-', $text );
        return trim( $text, '-' );
    }

    //main shortcode function
    public static function cqfs_shortcode( $atts ) {
        
        $atts = shortcode_atts(
            array(
                'id'    => '',
                'title' => 'true',
            ), $atts
        );

        //bail early if no id provided!
        if( ! $atts['id'] ){
            return;
        }

        $type = get_field('cqfs_build_type', $atts['id']);//select
        $category = get_field('cqfs_select_questions', $atts['id']);//taxonomy, returns ID
        $question_order = get_field('cqfs_question_order', $atts['id']);//ASC, DSC
        $layout = get_field('cqfs_layout_type', $atts['id']);//select
           
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

        ob_start(); ?>
        <!-- cqfs start -->
        <div id="cqfs-<?php echo esc_attr($atts['id']); ?>" class="cqfs <?php echo $class; ?>">
            <?php 
            if( $atts['title'] === 'true' ){
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
                            
                        ?>
                        <div class="question <?php echo $i == 1 ? 'show' : 'hide'; ?>">
                            <h3><?php echo esc_html( get_the_title($post->ID) ); ?></h3>
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
                <button class="cqfs--next disabled" disabled><?php echo apply_filters( 'cqfs_next', esc_html__('Next','cqfs') ); ?></button>
                <button class="cqfs--prev disabled" disabled><?php echo apply_filters( 'cqfs_prev', esc_html__('Prev','cqfs') ); ?></button>
                <button class="cqfs--submit disabled" disabled><?php echo apply_filters( 'cqfs_submit', esc_html__('Submit','cqfs') ); ?></button>
            </div>
            </form>

            <?php
            //do action `cqfs_after_nav`
            do_action('cqfs_after_nav');
            ?>
            <div class="cqfs--processing hide"><?php esc_html_e('Processing...','cqfs'); ?></div>
        </div>
        <!-- cqfs end -->
        <?php
        $trans = get_transient( "cqfs_{$atts['id']}" );
        var_dump($trans);
        return ob_get_clean();
        
    }

}
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
        $layout = get_field('cqfs_layout_type', $atts['id']);//select
           
        $questions = get_posts(
            array(
                'numberposts'   => -1,
                'post_type'     => 'cqfs_question',
                'category'      => esc_attr($category),
                'order'         => 'ASC'
            )
        );

        ob_start(); ?>
        <!-- cqfs start -->
        <div id="cqfs-<?php echo esc_attr($atts['id']); ?>" class="cqfs <?php echo esc_attr($type); ?>">
            <?php 
            if( $atts['title'] === 'true' ){
                printf(
                    '<h2 class="cqfs-title">%s</h2>',
                    esc_html( get_the_title($atts['id']) )
                );
            }
            ?>
            <div class="cqfs--questions <?php echo esc_attr($layout); ?>" >
                <?php 
                // var_dump($questions); 
                    if($questions){
                        $i = 1;
                        foreach($questions as $post) :
                            setup_postdata( $post );

                            $answers = get_field('cqfs_answers', $post->ID);//textarea, create each line.
                            $ansArr = explode("\n", $answers); //converted to array

                            $ans_type = get_field('cqfs_answer_type', $post->ID);//select. radio, checkbox.
                            //use when type is radio
                            $correct_ans = get_field('cqfs_correct_answer', $post->ID);//comma separated number.
                            //use when type is checkbox
                            $ansCorrect = explode(",", str_replace(' ', '', $correct_ans));//array

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
                                    <input name="option<?php echo $i; ?>" type="<?php echo esc_attr($ans_type); ?>" id="<?php echo self::cqfs_slug($ans); ?>" value="<?php echo $j; ?>">
                                    <label for="<?php echo self::cqfs_slug($ans); ?>"><?php echo esc_html($ans); ?></label>
                                </div>
                                <?php $j++; }} ?>
                                
                            </div>
                        </div>
                        <?php
                        $i++;
                        endforeach;
                        wp_reset_postdata();
                    }
                ?>
            </div>
        </div>
        <!-- cqfs end -->
        <?php
        return ob_get_clean();
        
    }

}
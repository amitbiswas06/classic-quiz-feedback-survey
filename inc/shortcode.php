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
        $perPage = get_field('cqfs_questions_per_page', $atts['id']);//number

        ob_start(); ?>
        <?php 
        if( $atts['title'] === 'true' ){
            echo '<h3>' . get_the_title($atts['id']) . '</h3>';
        }
        ?>
        <p>Type: <?php echo $type; ?></p>
        <p>Category ID: <?php echo $category; ?></p>
        <p>Per page: <?php echo $perPage; ?></p>
        <?php
        return ob_get_clean();
        
    }

}
<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**************************************************************
 * Custom Post Type Name/ID: "cqsf_quiz"
 *************************************************************/
function cqsf_quiz() {
		
    $cpt_id = 'cqsf_quiz';

    $labels = array(
        'name'                  => esc_html__( 'Quiz', 'cqfs' ),
        'singular_name'         => esc_html__( 'Quiz', 'cqfs' ),
        'menu_name'             => esc_html__( 'Quiz', 'cqfs' ),
        'parent_item_colon'     => esc_html__( 'Quiz&#58;', 'cqfs' ),
        'all_items'             => esc_html__( 'All Quiz', 'cqfs' ),
        'view_item'             => esc_html__( 'View Quiz', 'cqfs' ),
        'add_new_item'          => esc_html__( 'Add New Quiz', 'cqfs' ),
        'add_new'               => esc_html__( 'Add New', 'cqfs' ),
        'edit_item'             => esc_html__( 'Edit Quiz', 'cqfs' ),
        'update_item'           => esc_html__( 'Update Quiz', 'cqfs' ),
        'search_items'          => esc_html__( 'Search Quiz', 'cqfs' ),
        'not_found'             => esc_html__( 'Quiz Not found', 'cqfs' ),
        'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'cqfs' ),
    );
    $rewrite = array(
        'slug'                  => 'cqsf-quiz',
        'with_front'            => false,
        'pages'                 => false,
        'feeds'                 => false,
    );
    $args = array(
        'label'                 => esc_html__( 'Quiz', 'cqfs' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'author', 'revisions' ),
        //'taxonomies'            => array( 'xe_serv_cat' ),
        //'menu_icon'             => 'dashicons-Quiz',
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'show_in_nav_menus'     => true,
        'show_in_admin_bar'     => true,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'show_in_rest'          => true,
        'rewrite'               => $rewrite,
        //'capability_type'       => array('cqsf_quiz','cqsf_quizes'),
        'map_meta_cap'          => true
    );

    register_post_type( $cpt_id, $args );

}

// Hook into the 'init' action
add_action( 'init', 'cqsf_quiz', 0 );

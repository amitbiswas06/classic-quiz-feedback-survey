<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cqfs_Cpts{

    public function __construct(){

        add_action( 'init', array( $this, 'cpt_cqfs_question'), 5 );
        add_action( 'init', array( $this, 'cpt_cqfs_build'), 5 );

    }

    /**
     * Custom Post Type Name/ID: "cqfs_question"
     */
    public function cpt_cqfs_question() {
            
        $cpt_id = 'cqfs_question';

        $labels = array(
            'name'                  => esc_html__( 'Questions', 'cqfs' ),
            'singular_name'         => esc_html__( 'Question', 'cqfs' ),
            'menu_name'             => esc_html__( 'Questions', 'cqfs' ),
            'parent_item_colon'     => esc_html__( 'Question&#58;', 'cqfs' ),
            'all_items'             => esc_html__( 'All Questions', 'cqfs' ),
            'view_item'             => esc_html__( 'View Question', 'cqfs' ),
            'add_new_item'          => esc_html__( 'Add New Question', 'cqfs' ),
            'add_new'               => esc_html__( 'Add New', 'cqfs' ),
            'edit_item'             => esc_html__( 'Edit Question', 'cqfs' ),
            'update_item'           => esc_html__( 'Update Question', 'cqfs' ),
            'search_items'          => esc_html__( 'Search Question', 'cqfs' ),
            'not_found'             => esc_html__( 'Question Not found', 'cqfs' ),
            'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'cqfs' ),
        );
        $rewrite = array(
            'slug'                  => 'cqfs-question',
            'pages'                 => false,
            'feeds'                 => false,
        );
        $args = array(
            'label'                 => esc_html__( 'Questions', 'cqfs' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'author', 'revisions' ),
            'taxonomies'            => array( 'category' ),
            'show_ui'               => true,
            'show_in_menu'          => true,
            'show_in_nav_menus'     => true,
            'show_in_admin_bar'     => true,
            'can_export'            => true,
            'show_in_rest'          => true,
            'rewrite'               => $rewrite,
            'capability_type'       => array('cqfs_question','cqfs_questions'),
            'map_meta_cap'          => true
        );

        register_post_type( $cpt_id, $args );

    }


    /**
     * Custom Post Type Name/ID: "cqfs_build"
     */
    public function cpt_cqfs_build() {
            
        $cpt_id = 'cqfs_build';

        $labels = array(
            'name'                  => esc_html__( 'CQFS Build', 'cqfs' ),
            'singular_name'         => esc_html__( 'CQFS Build', 'cqfs' ),
            'menu_name'             => esc_html__( 'CQFS Build', 'cqfs' ),
            'parent_item_colon'     => esc_html__( 'CQFS Build&#58;', 'cqfs' ),
            'all_items'             => esc_html__( 'All CQFS Build', 'cqfs' ),
            'view_item'             => esc_html__( 'View CQFS Build', 'cqfs' ),
            'add_new_item'          => esc_html__( 'Add New CQFS Build', 'cqfs' ),
            'add_new'               => esc_html__( 'Add New', 'cqfs' ),
            'edit_item'             => esc_html__( 'Edit CQFS Build', 'cqfs' ),
            'update_item'           => esc_html__( 'Update CQFS Build', 'cqfs' ),
            'search_items'          => esc_html__( 'Search CQFS Build', 'cqfs' ),
            'not_found'             => esc_html__( 'CQFS Build Not found', 'cqfs' ),
            'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'cqfs' ),
        );
        $rewrite = array(
            'slug'                  => 'cqfs-build',
            'pages'                 => false,
            'feeds'                 => false,
        );
        $args = array(
            'label'                 => esc_html__( 'CQFS Build', 'cqfs' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'author', 'revisions' ),
            'show_ui'               => true,
            'show_in_menu'          => true,
            'show_in_nav_menus'     => true,
            'show_in_admin_bar'     => true,
            'can_export'            => true,
            'show_in_rest'          => true,
            'rewrite'               => $rewrite,
            'capability_type'       => array('cqfs_build','cqfs_builds'),
            'map_meta_cap'          => true
        );

        register_post_type( $cpt_id, $args );

    }


}

$cpts = new Cqfs_Cpts;
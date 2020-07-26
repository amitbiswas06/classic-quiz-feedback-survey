<?php
//define namespaces
namespace CQFS\INC\CPT;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Cqfs_Cpts{

    public function __construct(){

        add_action( 'init', array( $this, 'cpt_cqfs_question'), 5 );
        add_action( 'init', array( $this, 'cpt_cqfs_build'), 5 );
        add_action( 'init', array( $this, 'cpt_cqfs_entry'), 5 );

        //pre get posts
        add_action( 'pre_get_posts', [$this, 'cqfs_pre_get_posts'] );

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
            'supports'              => array( 'title', 'thumbnail', 'author', 'revisions' ),
            'taxonomies'            => array( 'category' ),
            'show_ui'               => true,
            'show_in_menu'          => true,
            'show_in_nav_menus'     => true,
            'show_in_admin_bar'     => true,
            'can_export'            => true,
            'show_in_rest'          => true,
            'has_archive'           => true,
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
            'has_archive'           => true,
            'rewrite'               => $rewrite,
            'capability_type'       => array('cqfs_build','cqfs_builds'),
            'map_meta_cap'          => true
        );

        register_post_type( $cpt_id, $args );

    }

    /**
     * Custom Post Type Name/ID: "cqfs_entry"
     */
    public function cpt_cqfs_entry() {
            
        $cpt_id = 'cqfs_entry';

        $labels = array(
            'name'                  => esc_html__( 'CQFS Entry', 'cqfs' ),
            'singular_name'         => esc_html__( 'CQFS Entry', 'cqfs' ),
            'menu_name'             => esc_html__( 'CQFS Entry', 'cqfs' ),
            'parent_item_colon'     => esc_html__( 'CQFS Entry&#58;', 'cqfs' ),
            'all_items'             => esc_html__( 'All CQFS Entries', 'cqfs' ),
            'view_item'             => esc_html__( 'View CQFS Entry', 'cqfs' ),
            'add_new_item'          => esc_html__( 'Add New CQFS Entry', 'cqfs' ),
            'add_new'               => esc_html__( 'Add New', 'cqfs' ),
            'edit_item'             => esc_html__( 'Edit CQFS Entry', 'cqfs' ),
            'update_item'           => esc_html__( 'Update CQFS Entry', 'cqfs' ),
            'search_items'          => esc_html__( 'Search CQFS Entry', 'cqfs' ),
            'not_found'             => esc_html__( 'CQFS Entry Not found', 'cqfs' ),
            'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'cqfs' ),
        );
        $rewrite = array(
            'slug'                  => 'cqfs-entry',
            'pages'                 => false,
            'feeds'                 => false,
        );
        $args = array(
            'label'                 => esc_html__( 'CQFS Entry', 'cqfs' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'author', 'revisions' ),
            'show_ui'               => true,
            'show_in_menu'          => true,
            'show_in_nav_menus'     => true,
            'show_in_admin_bar'     => true,
            'can_export'            => true,
            'show_in_rest'          => true,
            'has_archive'           => true,
            'rewrite'               => $rewrite,
            'capability_type'       => array('cqfs_entry','cqfs_entries'),
            'map_meta_cap'          => true
        );

        register_post_type( $cpt_id, $args );

    }

    public function cqfs_pre_get_posts( $query ){

        // bail early
        if( ! is_admin() || ! $query->is_main_query() ) {
            return;
        }

        /**
         * CPT cqfs_question
         */
        if ( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'cqfs_question' ) {

            $query->set( 'meta_key', 'cqfs_answer_type' );

            if( $query->query_vars['orderby'] == 'cqfs_answer_type'){
                $query->set( 'orderby', 'meta_value' );
            }

        }


        /**
         * CPT cqfs_build
         */
        if ( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'cqfs_build' ) {

            $build_type = filter_input(INPUT_GET, 'cqfs_build_type', FILTER_SANITIZE_STRING);

            $query->set( 'meta_key', 'cqfs_build_type' );

            if( $query->query_vars['orderby'] == 'cqfs_build_type'){
                $query->set( 'orderby', 'meta_value' );
            }
            
            if(isset($build_type) && !empty($build_type)){
                $query->set( 'meta_value', $build_type );
            }

        }

        /**
         * CPT cqfs_entry
         */
        if ( isset($query->query_vars['post_type']) && $query->query_vars['post_type'] == 'cqfs_entry' ) {

            $form_id = filter_input(INPUT_GET, 'cqfs_entry_form_id', FILTER_SANITIZE_STRING);
            $form_type = filter_input(INPUT_GET, 'cqfs_entry_form_type', FILTER_SANITIZE_STRING);
            $result = filter_input(INPUT_GET, 'cqfs_entry_result', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_GET, 'cqfs_entry_user_email', FILTER_SANITIZE_STRING);

            $query->set( 'meta_key', ['cqfs_entry_form_id', 'cqfs_entry_form_type', 'cqfs_entry_result', 'cqfs_entry_user_email'] );
            
            if( $query->query_vars['orderby'] == 'cqfs_entry_form_type' || $query->query_vars['orderby'] == 'cqfs_entry_result' ){
                $query->set( 'orderby', 'meta_value' );
            }

            if(isset($form_id) && !empty($form_id)){
                $query->set( 'meta_value', $form_id);
            }

            if(isset($form_type) && !empty($form_type)){
                $query->set( 'meta_value', $form_type);
            }

            if(isset($result) && !empty($result)){
                $query->set( 'meta_value', $result);
            }

            if(isset($email) && !empty($email)){
                $query->set( 'meta_value', $email);
            }


        }
        
        return;
    }

}

$cpts = new Cqfs_Cpts;
<?php
/**
 * Create custom post types for CQFS
 * @since 1.0.0
 */

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
            'all_items'             => esc_html__( 'Questions', 'cqfs' ),
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
            'menu_icon'             => 'dashicons-lightbulb',
            'label'                 => esc_html__( 'Questions', 'cqfs' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'thumbnail', 'author', 'revisions' ),
            'taxonomies'            => array( 'category' ),
            'show_ui'               => true,
            'show_in_menu'          => 'cqfs-post-types',
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
            'name'                  => esc_html__( 'Build', 'cqfs' ),
            'singular_name'         => esc_html__( 'Build', 'cqfs' ),
            'menu_name'             => esc_html__( 'Build', 'cqfs' ),
            'parent_item_colon'     => esc_html__( 'Build&#58;', 'cqfs' ),
            'all_items'             => esc_html__( 'Build', 'cqfs' ),
            'view_item'             => esc_html__( 'View Build', 'cqfs' ),
            'add_new_item'          => esc_html__( 'Add New Build', 'cqfs' ),
            'add_new'               => esc_html__( 'Add New', 'cqfs' ),
            'edit_item'             => esc_html__( 'Edit Build', 'cqfs' ),
            'update_item'           => esc_html__( 'Update Build', 'cqfs' ),
            'search_items'          => esc_html__( 'Search Build', 'cqfs' ),
            'not_found'             => esc_html__( 'Build Not found', 'cqfs' ),
            'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'cqfs' ),
        );
        $rewrite = array(
            'slug'                  => 'cqfs-build',
            'pages'                 => false,
            'feeds'                 => false,
        );
        $args = array(
            'menu_icon'             => 'dashicons-lightbulb',
            'label'                 => esc_html__( 'Build', 'cqfs' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'author', 'revisions' ),
            'taxonomies'            => array( 'category' ),
            'show_ui'               => true,
            'show_in_menu'          => 'cqfs-post-types',
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

    /**
     * Custom Post Type Name/ID: "cqfs_entry"
     */
    public function cpt_cqfs_entry() {
            
        $cpt_id = 'cqfs_entry';

        $labels = array(
            'name'                  => esc_html__( 'Entry', 'cqfs' ),
            'singular_name'         => esc_html__( 'Entry', 'cqfs' ),
            'menu_name'             => esc_html__( 'Entry', 'cqfs' ),
            'parent_item_colon'     => esc_html__( 'Entry&#58;', 'cqfs' ),
            'all_items'             => esc_html__( 'Entries', 'cqfs' ),
            'view_item'             => esc_html__( 'View Entry', 'cqfs' ),
            'add_new_item'          => esc_html__( 'Add New Entry', 'cqfs' ),
            'add_new'               => esc_html__( 'Add New', 'cqfs' ),
            'edit_item'             => esc_html__( 'Edit Entry', 'cqfs' ),
            'update_item'           => esc_html__( 'Update Entry', 'cqfs' ),
            'search_items'          => esc_html__( 'Search Entry', 'cqfs' ),
            'not_found'             => esc_html__( 'Entry Not found', 'cqfs' ),
            'not_found_in_trash'    => esc_html__( 'Not found in Trash', 'cqfs' ),
        );
        $rewrite = array(
            'slug'                  => 'cqfs-entry',
            'pages'                 => false,
            'feeds'                 => false,
        );
        $args = array(
            'menu_icon'             => 'dashicons-lightbulb',
            'label'                 => esc_html__( 'Entry', 'cqfs' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'author', 'revisions' ),
            'show_ui'               => true,
            'show_in_menu'          => 'cqfs-post-types',
            'show_in_nav_menus'     => true,
            'show_in_admin_bar'     => true,
            'can_export'            => true,
            'show_in_rest'          => true,
            'rewrite'               => $rewrite,
            'capability_type'       => array('cqfs_entry','cqfs_entries'),
            'map_meta_cap'          => true
        );

        register_post_type( $cpt_id, $args );

    }


    /**
     * Admin coulmns custom queries for filter by meta key
     * 
     * @param query $query for the admin.
     * returns custom query based on custom meta key value pair
     */
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

            //declare blank array first
            $meta_query = array();

            if(isset($form_id) && !empty($form_id)){
                $meta_query[] = array(
                    array(
                        'key' 		=> 'cqfs_entry_form_id', // cqfs field
                        'value'		=> $form_id,
                        'compare'	=> '=',
                        
                    )
                );
            }

            if(isset($form_type) && !empty($form_type)){
                $meta_query[] = array(
                    array(
                        'key' 		=> 'cqfs_entry_form_type', // cqfs field
                        'value'		=> $form_type,
                        'compare'	=> '=',
                        
                    )
                );
            }

            if(isset($result) && !empty($result)){
                $meta_query[] = array(
                    array(
                        'key' 		=> 'cqfs_entry_result', // cqfs field
                        'value'		=> $result,
                        'compare'	=> '=',
                        
                    )
                );
            }

            if(isset($email) && !empty($email)){
                $meta_query[] = array(
                    array(
                        'key' 		=> 'cqfs_entry_user_email', // cqfs field
                        'value'		=> $email,
                        'compare'	=> '=',
                        
                    )
                );
            }

            $query->set( 'meta_query', $meta_query );

        }
        
        return;
    }

}

$cpts = new Cqfs_Cpts;
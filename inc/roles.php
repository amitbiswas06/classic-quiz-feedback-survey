<?php
/**
 * Rolls for all CPTs for ADMINISTRATOR
 */

class Cqfs_Roles {

    /**
     * Custom capabilities of cqfs post types
     */
    private static $customCaps = array(
        [ 'singular' => 'cqfs_question', 'plural' => 'cqfs_questions' ],
        [ 'singular' => 'cqfs_build', 'plural' => 'cqfs_builds' ],
        [ 'singular' => 'cqfs_entry', 'plural' => 'cqfs_entries' ]
    );

    /**
     * Add custom CQFS capabilities from admin on deactivation
     */
    public static function add_caps_admin() {

        $role = get_role( 'administrator' );

        foreach( self::$customCaps as $cap ){
            
            $singular = $cap['singular'];
            $plural = $cap['plural'];

            $role->add_cap( "edit_{$singular}" ); 
            $role->add_cap( "edit_{$plural}" ); 
            $role->add_cap( "edit_others_{$plural}" ); 
            $role->add_cap( "publish_{$plural}" ); 
            $role->add_cap( "read_{$singular}" ); 
            $role->add_cap( "read_private_{$plural}" ); 
            $role->add_cap( "delete_{$singular}" ); 
            $role->add_cap( "delete_{$plural}" );
            $role->add_cap( "delete_private_{$plural}" );
            $role->add_cap( "delete_others_{$plural}" );
            $role->add_cap( "edit_published_{$plural}" );
            $role->add_cap( "edit_private_{$plural}" );
            $role->add_cap( "delete_published_{$plural}" );
            
        }

    }

    /**
     * Remove custom CQFS capabilities from admin on deactivation
     */
    public static function remove_caps_admin() {

        $role = get_role( 'administrator' );

        foreach( self::$customCaps as $cap ){
            
            $singular = $cap['singular'];
            $plural = $cap['plural'];

            $role->remove_cap( "edit_{$singular}" ); 
            $role->remove_cap( "edit_{$plural}" ); 
            $role->remove_cap( "edit_others_{$plural}" ); 
            $role->remove_cap( "publish_{$plural}" ); 
            $role->remove_cap( "read_{$singular}" ); 
            $role->remove_cap( "read_private_{$plural}" ); 
            $role->remove_cap( "delete_{$singular}" ); 
            $role->remove_cap( "delete_{$plural}" );
            $role->remove_cap( "delete_private_{$plural}" );
            $role->remove_cap( "delete_others_{$plural}" );
            $role->remove_cap( "edit_published_{$plural}" );
            $role->remove_cap( "edit_private_{$plural}" );
            $role->remove_cap( "delete_published_{$plural}" );
            
        }

    }


    /**
     * Create result page on plugin activation
     */
    public static function cqfs_result_page() {
  
        if ( ! current_user_can( 'activate_plugins' ) ) {
            return;
        }
        
        if ( null === get_page_by_path(CQFS_RESULT) ) {
           
            $current_user = wp_get_current_user();
            
            // create post object
            $result_page = array(
                'post_title'  => esc_html__( 'Cqfs Result','cqfs' ),
                'post_name'   => esc_attr(CQFS_RESULT),
                'post_content'=> esc_html__('This page displays CQFS results. Please do not delete this page.','cqfs'),
                'post_status' => 'publish',
                'post_author' => esc_attr($current_user->ID),
                'post_type'   => 'page',
            );
            
            // insert the post into the database
            wp_insert_post( $result_page );

        }

    }

    /**
     * custom page templates for defined pages/post
     * 
     * @param string $template
     */
    public static function cqfs_set_custom_templates( $template ){

        if(is_page(CQFS_RESULT)){

            $file = CQFS_PATH . 'cqfs-templates/template-results.php';
            if ( '' != $file ) {
                return $file ;
            }

        }

        return $template;

    }


}

?>
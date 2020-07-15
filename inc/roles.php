<?php
/**
 * Rolls for all CPTs for ADMINISTRATOR
 */

class Cqfs_Roles {

    public static $customCaps = array(
        [ 'singular' => 'cqfs_question', 'plural' => 'cqfs_questions' ],
        [ 'singular' => 'cqfs_build', 'plural' => 'cqfs_builds' ],
    );

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

}

?>
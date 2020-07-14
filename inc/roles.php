<?php
/**
 * Rolls for all CPTs for ADMINISTRATOR
 */

class Cqfs_Roles {

        public function __construct() {

            $this->add_caps_admin();
            $this->remove_caps_admin();
        }

        private function add_caps_admin() {
            $role = get_role( 'administrator' );

            /**
             * CPT - cqsf_quiz
             */
            $role->add_cap( 'edit_cqsf_quiz' ); 
            $role->add_cap( 'edit_cqsf_quizes' ); 
            $role->add_cap( 'edit_others_cqsf_quizes' ); 
            $role->add_cap( 'publish_cqsf_quizes' ); 
            $role->add_cap( 'read_cqsf_quiz' ); 
            $role->add_cap( 'read_private_cqsf_quizes' ); 
            $role->add_cap( 'delete_cqsf_quiz' ); 
            $role->add_cap( 'delete_cqsf_quizes' );
            $role->add_cap( 'delete_private_cqsf_quizes' );
            $role->add_cap( 'delete_others_cqsf_quizes' );
            $role->add_cap( 'edit_published_cqsf_quizes' );
            $role->add_cap( 'edit_private_cqsf_quizes' );
            $role->add_cap( 'delete_published_cqsf_quizes' );

        }

        private function remove_caps_admin() {
            $role = get_role( 'administrator' );

            /**
             * CPT - cqsf_quiz
             */
            $role->remove_cap( 'edit_cqsf_quiz' ); 
            $role->remove_cap( 'edit_cqsf_quizes' ); 
            $role->remove_cap( 'edit_others_cqsf_quizes' ); 
            $role->remove_cap( 'publish_cqsf_quizes' ); 
            $role->remove_cap( 'read_cqsf_quiz' ); 
            $role->remove_cap( 'read_private_cqsf_quizes' ); 
            $role->remove_cap( 'delete_cqsf_quiz' ); 
            $role->remove_cap( 'delete_cqsf_quizes' );
            $role->remove_cap( 'delete_private_cqsf_quizes' );
            $role->remove_cap( 'delete_others_cqsf_quizes' );
            $role->remove_cap( 'edit_published_cqsf_quizes' );
            $role->remove_cap( 'edit_private_cqsf_quizes' );
            $role->remove_cap( 'delete_published_cqsf_quizes' );

        }

}

new Cqfs_Roles();

?>
<?php

//define namespaces
namespace CQFS\INC\ADMINCOLUMNS;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Admin_Columns {

    //constructor
    public function __construct(){

        //cpt cqfs_question
        add_filter('manage_cqfs_question_posts_columns', [$this, 'cqfs_question_column_heads'], 10);
        add_action('manage_cqfs_question_posts_custom_column', [$this, 'cqfs_question_column_contents'], 10, 2);
        add_filter('manage_edit-cqfs_question_sortable_columns', [$this, 'cqfs_question_column_sortable'] );
        
        //cpt cqfs_build
        add_filter('manage_cqfs_build_posts_columns', [$this, 'cqfs_build_column_heads'], 10);
        add_action('manage_cqfs_build_posts_custom_column', [$this, 'cqfs_build_column_contents'], 10, 2);
        add_filter('manage_edit-cqfs_build_sortable_columns', [$this, 'cqfs_build_column_sortable'] );

        //cpt cqfs_entry
        add_filter('manage_cqfs_entry_posts_columns', [$this, 'cqfs_entry_column_heads'], 10);
        add_action('manage_cqfs_entry_posts_custom_column', [$this, 'cqfs_entry_column_contents'], 10, 2);
        add_filter('manage_edit-cqfs_entry_sortable_columns', [$this, 'cqfs_entry_column_sortable'] );
    
    }


    /*********************************************************************************/

    /**
     * cqfs_question column heads
     */
    public function cqfs_question_column_heads($defaults) {

        $new = array();
        foreach($defaults as $key => $title) {
    
            if ($key == 'date') {// Put the Name column before the date column
                $new['answer_type'] = __('Answer Type', 'cqfs');
            }
    
            $new[$key] = $title;
        }
        
        return $new;
        
    }


    /**
     * cqfs_question column contents
     */
    public function cqfs_question_column_contents($column, $post_ID) {
	
        // you could expand the switch to take care of other custom columns
        switch($column)
        {
            case 'answer_type':
                
                $answer_type = get_field('cqfs_answer_type'); // radio, checkbox
                echo esc_html( ucfirst($answer_type) );
    
            break;
                
        }
    }


    /**
     * cqfs_question column sortable
     */
    public function cqfs_question_column_sortable( $defaults ){
        $defaults['answer_type'] = 'cqfs_answer_type';
        return $defaults;
    }

    /*********************************************************************************/

    /**
     * cqfs_build column heads
     */
    public function cqfs_build_column_heads($defaults) {

        $new = array();
        foreach($defaults as $key => $title) {
    
            if ($key == 'date') {// Put the Name column before the date column
                $new['build_type'] = __('Build Type', 'cqfs');
                $new['build_category'] = __('Question Category', 'cqfs');
                $new['build_shortcode'] = __('Shortcode', 'cqfs');
            }
    
            $new[$key] = $title;
        }
        
        return $new;
        
    }


    /**
     * cqfs_build column contents
     */
    public function cqfs_build_column_contents($column, $post_ID) {
	
        // you could expand the switch to take care of other custom columns
        switch($column)
        {
            case 'build_type':
                
                $build_type = get_field('cqfs_build_type'); // quiz, feedback, survey
                $link = esc_url( add_query_arg( 'cqfs_build_type', urlencode(sanitize_text_field( $build_type ) ) ) );
                printf(
                    '<a href="%s">%s</a>',
                    $link,
                    esc_html( ucfirst($build_type) )
                );
    
            break;
    
            case 'build_category':
                
                $build_category = get_field('cqfs_select_questions');//taxonomy
                echo wp_kses( get_the_category_by_ID( $build_category ), 'post' );
    
            break;
    
            case 'build_shortcode':
                
                printf( '[cqfs id=%s]', esc_attr(get_the_ID()) );
    
            break;
                
        }

    }


    /**
     * cqfs_build column sortable
     */
    public function cqfs_build_column_sortable( $defaults ){
        $defaults['build_type'] = 'cqfs_build_type';
        return $defaults;
    }

    /*********************************************************************************/

    /**
     * cqfs_entry column heads
     */
    public function cqfs_entry_column_heads($defaults) {

        $new = array();
        foreach($defaults as $key => $title) {
    
            if ($key == 'date') {// Put the Name column before the date column
                $new['form_id'] = __('Form ID (cqfs build)', 'cqfs');
                $new['form_type'] = __('Form Type (cqfs build)', 'cqfs');
                $new['result'] = __('Result', 'cqfs');
                $new['email'] = __('Email', 'cqfs');
            }
    
            $new[$key] = $title;
        }
        
        return $new;
        
    }


    /**
     * cqfs_entry column contents
     */
    public function cqfs_entry_column_contents($column, $post_ID) {
	
        // you could expand the switch to take care of other custom columns
        switch($column)
        {
            case 'form_id':
                
                $form_id = get_field('cqfs_entry_form_id'); // form built with cqfs_build
                $link = esc_url( add_query_arg( 'cqfs_entry_form_id', urlencode(sanitize_text_field( $form_id ) ) ) );
                printf(
                    '<a href="%s">%s</a>',
                    $link,
                    esc_html( $form_id )
                );
    
            break;
    
            case 'form_type':
                
                $form_type = get_field('cqfs_entry_form_type');//select
                $link = esc_url( add_query_arg( 'cqfs_entry_form_type', urlencode(sanitize_text_field( $form_type ) ) ) );
                printf(
                    '<a href="%s">%s</a>',
                    $link,
                    esc_html( ucfirst($form_type) )
                );
    
            break;
    
            case 'result':
                
                $result = get_field('cqfs_entry_result');//radio

                $link = esc_url( add_query_arg( 'cqfs_entry_result', urlencode(sanitize_text_field( $result ) ) ) );
                printf(
                    '<a href="%s">%s</a>',
                    $link,
                    esc_attr($result)
                );
    
            break;

            case 'email':
                
                $email = get_field('cqfs_entry_user_email');//email
                $link = esc_url( add_query_arg( 'cqfs_entry_user_email', urlencode(sanitize_email( $email ) ) ) );
                printf(
                    '<a href="%s">%s</a>',
                    $link,
                    sanitize_email( $email )
                );
    
            break;
                
        }

    }


    /**
     * cqfs_entry column sortable
     */
    public function cqfs_entry_column_sortable( $defaults ){
        $defaults['form_type'] = 'cqfs_entry_form_type';
        return $defaults;
    }


}

$admin_cols = new Admin_Columns;

?>
<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*-------------------------------------------------------------------------------
	Custom Columns [cqfs_build]
-------------------------------------------------------------------------------*/

// ONLY cqfs_build CUSTOM TYPE POSTS
add_filter('manage_cqfs_build_posts_columns', 'cqfs_columns_head_only_cqfs_build', 10);
add_action('manage_cqfs_build_posts_custom_column', 'cqfs_columns_content_only_cqfs_build', 10, 2);
 
// CREATE TWO FUNCTIONS TO HANDLE THE COLUMN
function cqfs_columns_head_only_cqfs_build($defaults) {

	$new = array();
	foreach($defaults as $key => $title) {

        if ($key == 'author') {// Put the Name column before the Tags column
            $new['build_type'] = __('Build Type', 'cqfs');
            $new['build_category'] = __('Question Category', 'cqfs');
            $new['build_shortcode'] = __('Shortcode', 'cqfs');
        }

        $new[$key] = $title;
    }
    
	return $new;
	return $defaults;
	
}
function cqfs_columns_content_only_cqfs_build($column, $post_ID) {
	
    // you could expand the switch to take care of other custom columns
    switch($column)
    {
        case 'build_type':
			
            $build_type = get_field('cqfs_build_type'); // quiz, feedback, survey
            echo esc_html( ucfirst($build_type) );

        break;

        case 'build_category':
            
            $build_category = get_field('cqfs_select_questions');//taxonomy
            echo esc_attr( get_the_category_by_ID( $build_category ) );

        break;

        case 'build_shortcode':
            
            printf( '[cqfs id=%s]', esc_attr(get_the_ID()) );

        break;
        
			
    }
}

?>
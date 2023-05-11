<?php
/**
 * Custom template for cqfs result display page
 * @slug `cqfs-result`
 * @since 1.0.0
 */

// get theme header
get_header();
use CQFS\INC\UTIL\Utilities as Util;
?>
<main id="site-content" role="main" class="cqfs-page">
<div class="entry-content">
    <?php
    // before result content
    do_action('cqfs_before_result');
    ?>

    <div class="cqfs-result">
    <?php
    //get parameters
    $param = filter_input_array(INPUT_GET, FILTER_DEFAULT);

    if( isset($param['_cqfs_email']) && 
    isset($param['_cqfs_entry_id']) &&
    !empty($param['_cqfs_email']) &&
    !empty($param['_cqfs_entry_id']) ){
            
        $entry_obj = Util::cqfs_entry_obj( esc_attr($param['_cqfs_entry_id']) );

        if( $entry_obj ){

            $build_title = Util::cqfs_build_obj($entry_obj['form_id'])['title'];

            $proceed = true;
            // bail early if sequence mismatch
            if( $param['_cqfs_email'] != $entry_obj['email'] ){
                printf(
                    '<h2 class="cqfs-title">%s</h2>',
                    esc_html__('Invalid Result.','cqfs')
                );
                $proceed = false;
            }

            if( $proceed && $entry_obj['form_type'] === 'quiz' ){
                // show quiz result
                ?>
                <h2 class="cqfs-title"><?php echo esc_html($build_title); ?></h2>
                <div class="cqfs-entry-remarks">
                    <h3 class="cqfs-uname"><?php 
                    printf(
                        __( wp_kses( 'Hello %s', Util::$allowed_in_table ), 'cqfs' ),
                        esc_html($entry_obj['user_title'])
                    ); 
                    ?></h3>
                    
                    <?php
                    //pass message
                    $default_pass_msg = apply_filters( 'cqfs_pass_msg', esc_html__('Congratulations! You have passed.', 'cqfs'));
                    //fail message
                    $default_fail_msg = apply_filters( 'cqfs_fail_msg', esc_html__('Sorry! You have failed.', 'cqfs'));
                    ?>
                    <div class="cqfs-msg <?php echo esc_attr($entry_obj['result']); ?>">
                        <p class="cqfs-percentage"><?php 
                        printf(
                            __( wp_kses('%s&#37; correct.', Util::$allowed_in_table), 'cqfs'),
                            esc_html($entry_obj['percentage'])
                        );
                        ?></p>
                        <p class="cqfs-remark"><?php 
                        if( $entry_obj['result'] === 'passed'){
                            if( $entry_obj['remarks'] == ""){
                                echo esc_html($default_pass_msg);
                            }else{
                                echo esc_html($entry_obj['remarks']);
                            }
                        }else{
                            if( $entry_obj['remarks'] == ""){
                                echo esc_html($default_fail_msg);
                            }else{
                                echo esc_html($entry_obj['remarks']);
                            }
                        }
                        ?></p>
                    </div>
                    
                </div>
                <div class="cqfs-entry-qa">
                    <?php if( $entry_obj['all_questions'] ) {
                        
                        foreach( $entry_obj['all_questions'] as $ent ){

                            $you_ans = apply_filters('cqfs_result_you_answered', esc_html__('You answered&#58; ', 'cqfs'));
                            $status = apply_filters('cqfs_result_ans_status', esc_html__('Status&#58; ', 'cqfs'));
                            $note = apply_filters('cqfs_result_ans_note', esc_html__('Note&#58; ', 'cqfs'));

                            printf(
                                '<div class="cqfs-entry-qa__single">
                                    <h4>%s</h4>
                                    <p><label>%s</label>%s</p>
                                    <p><label>%s</label>%s</p>
                                    <details><summary>%s</summary><p>%s</p></details>
                                </div>',
                                esc_html( $ent['question'] ),
                                $you_ans,
                                esc_html( $ent['answer'] ),
                                $status,
                                esc_html( $ent['status'] ),
                                $note,
                                esc_html( $ent['note'] )
                            );
                        }
                    }?>
                </div>
                <?php
            }

            if( $proceed && $entry_obj['form_type'] === 'feedback' ){
                ?>
                <h2 class="cqfs-title"><?php echo esc_html($build_title); ?></h2>
                <div class="cqfs-entry-thanks">
                    <h3 class="cqfs-uname"><?php 
                    printf(
                        __( wp_kses( 'Hello %s', Util::$allowed_in_table ), 'cqfs' ),
                        esc_html($entry_obj['user_title'])
                    ); 
                    ?></h3>
                    <p><?php 
                    $cqfs_thank_msg_feedback = apply_filters('cqfs_thankyou_msg_feedback', esc_html__('Thank you for your feedback.', 'cqfs'));
                    echo esc_html($cqfs_thank_msg_feedback);
                    ?></p>
                </div>
                <?php
            }

            if( $proceed && $entry_obj['form_type'] === 'survey' ){
                ?>
                <h2 class="cqfs-title"><?php echo esc_html($build_title); ?></h2>
                <div class="cqfs-entry-thanks">
                    <h3 class="cqfs-uname"><?php 
                    printf(
                        __( wp_kses( 'Hello %s', Util::$allowed_in_table ), 'cqfs' ),
                        esc_html($entry_obj['user_title'])
                    ); 
                    ?></h3>
                    <p><?php 
                    $cqfs_thank_msg_survey = apply_filters('cqfs_thankyou_msg_survey', esc_html__('Thank you for your participation in the survey.', 'cqfs'));
                    echo esc_html($cqfs_thank_msg_survey);
                    ?></p>
                </div>
                <?php
            }


        }else{
            printf(
                '<h2 class="cqfs-title">%s</h2>',
                esc_html__('Invalid Result.','cqfs')
            );
        }
            
    }elseif( isset($param['_cqfs_status']) && $param['_cqfs_status'] === 'failure' ){

        printf(
            '<p class="cqfs-msg">%s</p>',
            esc_html__('Sorry, something went terribly wrong. Please try again.','cqfs')
        );
    }else{
        printf(
            '<h2 class="cqfs-title">%s</h2>',
            esc_html__('No results found.','cqfs')
        );
    }
    ?>
    </div>

    <?php
    // action after result content
    do_action('cqfs_after_result');
    ?>

</div>
</main>
<?php
// get theme footer
get_footer();

?>
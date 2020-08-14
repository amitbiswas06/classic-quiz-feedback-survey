<?php
/**
 * Custom template for cqfs result display page
 * @slug `cqfs-result`
 * @since 1.0.0
 */

// get theme header
get_header();

?>
<main id="site-content" role="main" class="cqfs-page">
    <?php
    // before result content
    do_action('cqfs_before_result');
    ?>

    <div class="cqfs-result">
        
    </div>

    <?php
    // action after result content
    do_action('cqfs_after_result');
    ?>
</main>
<?php
// get theme footer
get_footer();

?>
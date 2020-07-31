<?php
/**
 * Custom menu pages for wp admin
 * @since 1.0.0
 */

//define namespaces
namespace CQFS\ADMIN\MENUPAGES;
use CQFS\ROOT\CQFS as CQFS;

class MenuPages {

    public function __construct() {
        
        //construct the page
        add_action( 'admin_menu', [$this, 'cqfs_admin_main_page'] );

        //call register settings function
        add_action( 'admin_init', [$this, 'cqfs_register_settings'] );

        //admin form submission handle
        require __DIR__ . '/form-handle.php';

        //enqueue the scripts
        add_action('admin_enqueue_scripts', [$this, 'cqfs_settings_enqueue_scripts']);
    }

    public function cqfs_register_settings() {
        //register our settings
        register_setting( 'cqfs-settings-group', '_cqfs_form_handle' );
        // unregister_setting( 'cqfs-settings-group', '_cqfs_form_handle' );
        // delete_option( '_cqfs_form_handle' );
        
    }

    public function cqfs_admin_main_page(){
        
        add_menu_page(
            esc_html__( 'CQFS Settings', 'cqfs' ),
            esc_html__('CQFS', 'cqfs'),
            'manage_options',
            sanitize_key('cqfs-settings'),
            [$this, 'cqfs_settings_page'],
            'dashicons-lightbulb',
            25
        );
    }

    public function cqfs_settings_page(){

        //get options

        //form mode value
        $form_mode = esc_attr( get_option('_cqfs_form_handle') );

        ?>
<div class="cqfs-admin">

    <div class="content">
        <h1 class="page-title"><?php echo esc_html__('Welcome to CQFS Settings', 'cqfs'); ?></h1>
        
        <!-- <div class="form-wrap">
            <h2><?php //echo esc_html__('CQFS License','cqfs'); ?></h2>
            <form id="cqfs-license-form">
                <div class="form-input-wrap">
                    <label for="cqfs-customer-email"><?php //echo esc_html__('Your email ID: ','cqfs'); ?></label>
                    <input name="cqfs-customer-email" id="cqfs-customer-email" type="email">
                </div>
                <div class="form-input-wrap">
                    <label for="cqfs-auth-code"><?php //echo esc_html__('Authorization Code: ','cqfs'); ?></label>
                    <input name="cqfs-auth-code" id="cqfs-auth-code" type="password">
                </div>
                <button type="submit"><?php //echo esc_html__('Verify License', 'cqfs') ?></button>
            </form>
        </div> -->

        <div class="form-wrap">
            <h2 class="all-settings-title"><?php echo esc_html__('All Settings'); ?></h2>
            <form id="cqfs-general-settings" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">

                <div class="form-input-wrap">

                    <h2><?php echo esc_html__('Global form handle','cqfs'); ?></h2>

                    <label for="cqfs-form-handle-php"><?php echo esc_html__('Default','cqfs'); ?></label>
                    <input name="_cqfs_form_handle" 
                        id="cqfs-form-handle-php" 
                        type="radio" 
                        value="<?php echo esc_attr( 'php_mode' ); ?>" 
                        <?php echo $form_mode === 'php_mode' || $form_mode === '' ? 'checked' : ''; ?>>
                    
                    <label for="cqfs-form-handle-ajax"><?php echo esc_html__('Ajax','cqfs'); ?></label>
                    <input name="_cqfs_form_handle" 
                        id="cqfs-form-handle-ajax" 
                        type="radio" 
                        value="<?php echo esc_attr( 'ajax_mode' ); ?>" 
                        <?php echo $form_mode === 'ajax_mode' ? 'checked' : ''; ?>>
                </div>

                <?php 
                //insert hidden action input
                printf('<input type="hidden" name="action" value="%s">', esc_attr('cqfs_admin_response'));
                    
                //create nonce fields
                wp_nonce_field('cqfs_admin_post', '_cqfs_admin_nonce');

                //submit button
                submit_button(); 
                var_dump($form_mode);
                // var_dump(plugin_dir_url(__FILE__) . 'css/cqfs-admin-style.css');

                ?>

            </form>
        </div>
     </div><!-- .content end -->

    <div class="sidebar">

    </div><!-- .sidebar end -->

            
</div>
        <?php
    }


    public function cqfs_settings_enqueue_scripts(){

        if( get_current_screen()->base === 'toplevel_page_cqfs-settings' ){

            // enqueue script
            // wp_enqueue_script('cqfs_admin_script', plugin_dir_url(__FILE__) . 'js/cqfs-admin.js', NULL, '1.0.0', true);
        
            // enqueue styles
            wp_enqueue_style(
                'cqfs-admin-style', 
                plugin_dir_url(__FILE__) . 'css/cqfs-admin-style.css', 
                '', 
                CQFS::CQFS_VERSION,
                'all'
            );
        
        }


    }

}

if( is_admin() ){
    $menu_pages = new MenuPages;
}




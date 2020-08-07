<?php
/**
 * Custom menu pages for wp admin
 * @since 1.0.0
 */

//define namespaces
namespace CQFS\ADMIN\MENUPAGES;

class MenuPages {

    public function __construct() {
        
        //construct the page
        add_action( 'admin_menu', [$this, 'cqfs_admin_main_page'] );

        //call register settings function
        add_action( 'admin_init', [$this, 'cqfs_register_settings'] );

        //admin form submission handle done with `admin-post.php`
        require CQFS_PATH . 'admin/form-handle.php';

    }

    /**
     * Register settings for the CQFS admin settings page
     */
    public function cqfs_register_settings() {
        //register cqfs settings

        // form handle mode
        register_setting( 'cqfs-settings-group', '_cqfs_form_handle' );

        // enable non logged in users to submit on front end forms
        register_setting( 'cqfs-settings-group', '_cqfs_allow_all' );
    }

    /**
     * Add a menu page for the settings etc pages.
     */
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


    /**
     * Settings page HTML form
     */
    public function cqfs_settings_page(){

        //get options

        //form mode value
        $form_mode = esc_attr( get_option('_cqfs_form_handle') );

        //allow all checkbox value
        $allow_all = esc_attr( get_option('_cqfs_allow_all') );

        ?>
        <div class="cqfs-admin">

            <div class="content">
                <h1 class="page-title"><?php echo esc_html__('Welcome to CQFS Settings', 'cqfs'); ?></h1>

                <div class="form-wrap">
                    <h2 class="cqfs-form-title"><?php echo esc_html__('All Settings'); ?></h2>
                    <form name="cqfs-general-settings" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">

                        <div class="cqfs-hidden"><?php 
                            wp_nonce_field( 'cqfs_admin_settings', '_cqfs_admin_nonce');
                            //insert hidden action input
                            printf('<input type="hidden" name="action" value="%s">', esc_attr('cqfs_admin_response'));
                        ?></div>

                        <div class="cqfs-fields">

                            <div class="cqfs-field">
                                <div class="cqfs-label">
                                    <label for="cqfs-form-handle-mode"><?php echo esc_html__('Form handle mode','cqfs'); ?></label>
                                    <p class="description"><?php echo esc_html__('Set the mode for front end form submission.','cqfs'); ?></p>
                                </div>
                                <div class="cqfs-input">
                                    <ul id="cqfs-form-handle-mode" class="cqfs-radio-list horizontal">
                                        <?php
                                        $list = array(
                                            'php_mode'  => esc_html__('Default', 'cqfs'),
                                            'ajax_mode' => esc_html__('Ajax', 'cqfs'),
                                        );
                                        foreach( $list as $key => $val ){
                                            printf(
                                                '<li><label><input name="_cqfs[form-handle]" type="radio" value="%s" %s>%s</label></li>',
                                                esc_attr($key),
                                                $key == $form_mode ? esc_attr('checked') : '',
                                                esc_html($val)
                                            );
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>

                            <div class="cqfs-field">
                                <div class="cqfs-label">
                                    <label for="cqfs-allow-all"><?php echo esc_html__('Enable all users.','cqfs'); ?></label>
                                    <p class="description"><?php 
                                    echo esc_html__('Also allow non logged in users to participate. Email ID will be required.','cqfs'); 
                                    ?></p>
                                </div>
                                <div class="cqfs-input">
                                    <input type="checkbox" 
                                    name="_cqfs[allow-all]" 
                                    id="cqfs-allow-all" <?php 
                                    if($allow_all) { echo esc_attr('checked'); } ?>>
                                </div>
                            </div>

                        </div>

                        <?php 

                        //submit button
                        submit_button(); 
                        var_dump($allow_all);

                        ?>

                    </form>
                </div>
            </div><!-- .content end -->

            <div class="sidebar">

            </div><!-- .sidebar end -->

                    
        </div>
        <?php
    }


}

// instanciate if admin
if( is_admin() ){
    $menu_pages = new MenuPages;
}




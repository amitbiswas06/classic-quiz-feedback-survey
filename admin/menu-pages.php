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

        //construct the page
        add_action( 'admin_menu', [$this, 'cqfs_cpt_menu_pages'] );

        // submenu items
        add_action( 'admin_menu', [$this, 'cqfs_submenu_items'] );

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
    public function cqfs_cpt_menu_pages(){
        
        add_menu_page(
            esc_html__( 'CQFS Post Types', 'cqfs' ),
            esc_html__('CQFS', 'cqfs'),
            'edit_cqfs_questions',
            sanitize_key('cqfs-post-types'),
            '',
            'dashicons-lightbulb',
            24
        );
    }

    /**
     * Add a menu page for the settings etc pages.
     */
    public function cqfs_admin_main_page(){
        
        add_menu_page(
            esc_html__( 'CQFS Settings', 'cqfs' ),
            esc_html__('CQFS Settings', 'cqfs'),
            'manage_options',
            sanitize_key('cqfs-settings'),
            [$this, 'cqfs_settings_page'],
            'dashicons-lightbulb',
            24
        );
    }


    /**
     * Settings page HTML form
     */
    public function cqfs_settings_page(){

        //get options

        //form mode value
        $form_mode = esc_attr( get_option('_cqfs_form_handle') );

        //allow guest checkbox value
        $allow_guest = esc_attr( get_option('_cqfs_allow_all') );

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
                                    <label for="cqfs-allow-all"><?php echo esc_html__('Allow guest users.','cqfs'); ?></label>
                                    <p class="description"><?php 
                                    echo esc_html__('Allow guest users to participate. Email ID will be required.','cqfs'); 
                                    ?></p>
                                </div>
                                <div class="cqfs-input">
                                    <input type="checkbox" 
                                    name="_cqfs[allow-all]" 
                                    id="cqfs-allow-all" <?php 
                                    if($allow_guest) { echo esc_attr('checked'); } ?>>
                                </div>
                            </div>

                        </div>

                        <?php 

                        //submit button
                        submit_button();

                        ?>

                    </form>
                </div>
            </div><!-- .content end -->

            <div class="sidebar">

            </div><!-- .sidebar end -->

                    
        </div>
        <?php
    }


    public function cqfs_submenu_items(){

        add_submenu_page(
            'cqfs-post-types', 
            esc_html__('Add Question', 'cqfs'), 
            esc_html__('Add Question', 'cqfs'),
            'edit_cqfs_questions', 
            'post-new.php?post_type=cqfs_question',
            '',
            1
        );

        add_submenu_page(
            'cqfs-post-types', 
            esc_html__('Add Build', 'cqfs'), 
            esc_html__('Add Build', 'cqfs'),
            'edit_cqfs_builds', 
            'post-new.php?post_type=cqfs_build',
            '',
            3
        );

        add_submenu_page(
            'cqfs-post-types', 
            esc_html__('Add Entry', 'cqfs'), 
            esc_html__('Add Entry', 'cqfs'),
            'edit_cqfs_entries', 
            'post-new.php?post_type=cqfs_entry',
            '',
            5
        );

    }


}

// instanciate if admin
if( is_admin() ){
    $menu_pages = new MenuPages;
}




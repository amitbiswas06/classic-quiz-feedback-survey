<?php
/**
 * Custom menu pages for wp admin
 * @since 1.0.0
 */

//define namespaces
namespace CQFS\ADMIN\MENUPAGES;

use CQFS\INC\UTIL\Utilities as Util;

class MenuPages {

    public function __construct() {
        
        //construct the page
        add_action( 'admin_menu', [$this, 'cqfs_admin_main_page'] );

        //construct the page for all cpt
        add_action( 'admin_menu', [$this, 'cqfs_cpt_menu_pages'] );

        // submenu items as cpt
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
        register_setting( 'cqfs-settings-group', '_cqfs_form_handle', array(
            'type'      => 'array',
            'default'   => 'php_mode',
        ) );

        // enable non logged in users to submit on front end forms
        register_setting( 'cqfs-settings-group', '_cqfs_allow_all', array(
            'type'      => 'boolean',
            'default'   => false,
        ) );

        // send email to admin when a user submits a form
        register_setting( 'cqfs-settings-group', '_cqfs_mail_admin', array(
            'type'      => 'boolean',
            'default'   => false,
        ) );

        // send email to user when a user submits a form
        register_setting( 'cqfs-settings-group', '_cqfs_mail_user', array(
            'type'      => 'boolean',
            'default'   => true,
        ) );

        // additional notes in email
        register_setting( 'cqfs-settings-group', '_cqfs_mail_notes', array(
            'type'      => 'string',
        ) );

        $footer = sprintf(
            __('This email was sent from <a href="%s">%s</a> &copy; %s','cqfs'),
            esc_url_raw( home_url('/') ),
            esc_html( get_bloginfo('name') ),
            esc_html(date('Y'))
        );
        // additional notes in email
        register_setting( 'cqfs-settings-group', '_cqfs_mail_footer', array(
            'type'      => 'string',
            'default'   => wp_kses( $footer, Util::$allowed_in_table ),
        ) );

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
            23
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
            [$this, 'cqfs_settings_page_content'],
            'dashicons-lightbulb',
            24
        );
    }


    /**
     * Settings page HTML
     */
    public function cqfs_settings_page_content(){

        ?>
        <div class="cqfs-admin">

            <div class="content">
                <h1 class="page-title"><?php echo esc_html__('Welcome to CQFS Settings', 'cqfs'); ?></h1>
                <?php
                    self::form_all_settings();
                    self::form_mail_settings();
                ?>
            </div><!-- .content end -->

            <div class="sidebar">

            </div><!-- .sidebar end -->
                    
        </div>
        <?php
    }


    /**
     * Settings page "all settings" form
     */
    private static function form_all_settings(){
        //get options

        //form mode value
        $form_mode = esc_attr( get_option('_cqfs_form_handle') );

        //allow guest checkbox value
        $allow_guest = esc_attr( get_option('_cqfs_allow_all') );

        ?>
        <div class="form-wrap">
            <h2 class="cqfs-form-title"><?php echo esc_html__('All Settings', 'cqfs'); ?></h2>
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
                            <p class="description"><?php echo esc_html__('Set default mode for front end form submission.','cqfs'); ?></p>
                        </div>
                        <div class="cqfs-input">
                            <ul id="cqfs-form-handle-mode" class="cqfs-radio-list horizontal">
                                <?php
                                $list = array(
                                    'php_mode'  => esc_html__('PHP', 'cqfs'),
                                    'ajax_mode' => esc_html__('AJAX', 'cqfs'),
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

                    <div class="cqfs-field checkbox">
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
        <?php
    }


    /**
     * Settings page "mail settings" form
     */
    private static function form_mail_settings(){
        // get options

        //email to admin "checkbox"
        $email_admin = esc_attr( get_option('_cqfs_mail_admin') );

        //email to admin "checkbox"
        $email_user = esc_attr( get_option('_cqfs_mail_user') );

        //email additional notes
        $email_notes = wp_kses( get_option('_cqfs_mail_notes'), Util::$allowed_in_table );

        //email footer content
        $email_footer = wp_kses( get_option('_cqfs_mail_footer'), Util::$allowed_in_table );

        ?>
        <div class="form-wrap">
            <h2 class="cqfs-form-title"><?php echo esc_html__('Mail Settings','cqfs'); ?></h2>
            <form name="cqfs-mail-settings" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <div class="cqfs-hidden"><?php 
                    wp_nonce_field( 'cqfs_mail_settings', '_cqfs_mail_settings_nonce');
                    //insert hidden action input
                    printf('<input type="hidden" name="action" value="%s">', esc_attr('cqfs_mail_settings_response'));
                ?></div>
                <div class="cqfs-fields">

                    <div class="cqfs-field checkbox">
                        <div class="cqfs-label">
                            <label for="cqfs-mail-admin"><?php echo esc_html__('Email to admin.','cqfs'); ?></label>
                            <p class="description"><?php 
                            echo esc_html__('Send email to admin when a form is submitted by a user.','cqfs'); 
                            ?></p>
                        </div>
                        <div class="cqfs-input">
                            <input type="checkbox" 
                            name="_cqfs[mail-admin]" 
                            id="cqfs-mail-admin" <?php 
                            if($email_admin) { echo esc_attr('checked'); } ?>>
                        </div>
                    </div>

                    <div class="cqfs-field checkbox">
                        <div class="cqfs-label">
                            <label for="cqfs-mail-user"><?php echo esc_html__('Email to user.','cqfs'); ?></label>
                            <p class="description"><?php 
                            echo esc_html__('Send email to the user when a form is submitted by that user.','cqfs'); 
                            ?></p>
                        </div>
                        <div class="cqfs-input">
                            <input type="checkbox" 
                            name="_cqfs[mail-user]" 
                            id="cqfs-mail-user" <?php 
                            if($email_user) { echo esc_attr('checked'); } ?>>
                        </div>
                    </div>

                    <div class="cqfs-field">
                        <div class="cqfs-label">
                            <label for="cqfs-mail-additional"><?php echo esc_html__('Additional Notes','cqfs'); ?></label>
                            <p class="description"><?php 
                            echo esc_html__('Add any additional notes in the email. It will appear above the footer. Allowed Html br, b, em, a, span. Inline style allowed.','cqfs'); 
                            ?></p>
                        </div>
                        <div class="cqfs-input">
                            <textarea name="_cqfs[mail-additional-notes]" id="cqfs-mail-additional" rows="6"><?php 
                            echo $email_notes;
                            ?></textarea>
                        </div>
                    </div>

                    <div class="cqfs-field">
                        <div class="cqfs-label">
                            <label for="cqfs-mail-footer"><?php echo esc_html__('Email Footer','cqfs'); ?></label>
                            <p class="description"><?php 
                            echo esc_html__('Add custom footer content for the email. Allowed Html br, b, em, a, span. Inline style allowed.','cqfs'); 
                            ?></p>
                        </div>
                        <div class="cqfs-input">
                            <textarea name="_cqfs[mail-footer]" id="cqfs-mail-footer" rows="6"><?php 
                            echo $email_footer;
                            ?></textarea>
                        </div>
                    </div>

                </div>
                <?php 

                //submit button
                submit_button();
                $custom_logo_id = get_theme_mod( 'custom_logo' );
                $image = wp_get_attachment_image_src( $custom_logo_id , 'full' );
                var_dump(Util::$allowed_in_table);
                ?>
            </form>
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




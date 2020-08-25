<?php
/**
 * Custom menu pages for wp admin
 * @since 1.0.0
 */

//define namespaces
namespace CQFS\ADMIN\MENUPAGES;
use CQFS\ROOT\CQFS as CQFS;
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

        // sender email id for form submission, input type - email
        register_setting( 'cqfs-settings-group', '_cqfs_sender_email', array(
            'type'      => 'string',
            'default'   => sanitize_email(get_bloginfo('admin_email')),
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
            'default'   => wp_kses( $footer, 'post' ),
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
                    self::result_page_check();
                    self::form_mail_settings();
                ?>
            </div><!-- .content end -->

            <div class="sidebar">
                <div class="sidebar-content">
                    <h2 class="cqfs-title"><?php echo esc_html__('Classic Quiz Feedback Survey','cqfs'); ?></h2>
                    <span class="cqfs-version"><?php echo esc_html(CQFS::CQFS_VERSION); ?></span>
                    <div class="cqfs-donate">
                        <p><?php echo esc_html__('This plugin is a free open source project. If this plugin is useful to you, please support me and keep this alive.','cqfs'); ?></p>
                        <?php
                            printf(
                                '<a href="%s" target="_blank" class="paypal-me">%s</a>',
                                esc_url('https://paypal.me/amitbiswas06?locale.x=en_GB'),
                                esc_html__('Donate via PayPal','cqfs')
                            ); 
                        ?>
                    </div>
                    <div class="cqfs-help">
                        <h2 class="cqfs-title"><?php echo esc_html__('Full Documentation', 'cqfs'); ?></h2>
                        <ul>
                            <li>
                            <?php
                                printf(
                                    '<a href="%s" target="_blank">%s</a>',
                                    esc_url('https://templateartist.com/cqfs/'),
                                    esc_html__('Overview','cqfs')
                                ); 
                            ?>
                            </li>
                            <li>
                            <?php
                                printf(
                                    '<a href="%s" target="_blank">%s</a>',
                                    esc_url('https://templateartist.com/cqfs/getting-started/'),
                                    esc_html__('Getting Started','cqfs')
                                ); 
                            ?>
                            </li>
                            <li>
                            <?php
                                printf(
                                    '<a href="%s" target="_blank">%s</a>',
                                    esc_url('https://templateartist.com/cqfs/shortcode/'),
                                    esc_html__('CQFS shortcode','cqfs')
                                ); 
                            ?>
                            </li>
                            <li>
                            <?php
                                printf(
                                    '<a href="%s" target="_blank">%s</a>',
                                    esc_url('https://templateartist.com/cqfs/action-hooks/'),
                                    esc_html__('Action Hooks','cqfs')
                                ); 
                            ?>
                            </li>
                            <li>
                            <?php
                                printf(
                                    '<a href="%s" target="_blank">%s</a>',
                                    esc_url('https://templateartist.com/cqfs/filter-hooks/'),
                                    esc_html__('Filter Hooks','cqfs')
                                ); 
                            ?>
                            </li>
                            <li>
                            <?php
                                printf(
                                    '<a href="%s" target="_blank">%s</a>',
                                    esc_url('https://templateartist.com/cqfs/demos/'),
                                    esc_html__('Live Demos','cqfs')
                                ); 
                            ?>
                            </li>
                        </ul>
                    </div>
                    <h2 class="cqfs-title"><?php echo esc_html__('Github Repositiroy', 'cqfs'); ?></h2>
                    <?php
                        printf(
                            '<a href="%s" target="_blank">%s</a>',
                            esc_url('https://github.com/amitbiswas06/classic-quiz-feedback-survey'),
                            esc_html__('classic quiz feedback survey','cqfs')
                        ); 
                    ?>
                </div>
            </div><!-- .sidebar end -->
                    
        </div>
        <?php
    }

    /**
     * Result page existence check
     */
    private static function result_page_check(){
        ?>
        <div class="form-wrap result-page-status">
        <?php 
        if( null === get_page_by_path(CQFS_RESULT) ){
            printf(
                '<div class="cqfs-return-msg failure"><p><span class="cqfs-icon failure-icon"></span>%s</p></div>',
                esc_html__('Result page is missing.','cqfs')
            );

            ?>
            <form name="recreate-result-page-form" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
                <div class="cqfs-hidded">
                    <input type="hidden" name="action" value="cqfs-recreate-result-page">
                    <?php wp_nonce_field( 'cqfs_recreate_resultpage', '_cqfs_recreate_resultpage_nonce'); ?>
                </div>
                <div class="cqfs-submission">
                    <?php submit_button( esc_html__('Create Result Page','cqfs'),'primary','submit',false ); ?>
                    <span class="cqfs-loader inline-block display-none transition"></span>
                </div>
            </form>
            <?php

        }else{
            printf(
                '<div class="cqfs-return-msg success"><p><span class="cqfs-icon success-icon"></span>%s</p></div>',
                esc_html__('Great! Result page exists.','cqfs')
            );
        } ?>
        </div>
        <?php
    }

    /**
     * Settings page "mail settings" form
     */
    private static function form_mail_settings(){
        // get options

        //sender email id
        $sender_email = sanitize_email( get_option('_cqfs_sender_email') );

        //email to admin "checkbox"
        $email_to_admin = esc_attr( get_option('_cqfs_mail_admin') );

        //email to admin "checkbox"
        $email_to_user = esc_attr( get_option('_cqfs_mail_user') );

        //email additional notes
        $email_notes = wp_kses( get_option('_cqfs_mail_notes'), 'post' );

        //email footer content
        $email_footer = wp_kses( get_option('_cqfs_mail_footer'), 'post' );

        ?>
        <div class="form-wrap">
            <h2 class="cqfs-form-title"><?php echo esc_html__('Mail Settings','cqfs'); ?></h2>
            <form name="cqfs-mail-settings" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <div class="cqfs-hidden"><?php 
                    wp_nonce_field( 'cqfs_mail_settings', '_cqfs_mail_settings_nonce');
                    //insert hidden action input
                    printf('<input type="hidden" name="action" value="%s">', esc_attr('cqfs_mail_settings_action'));
                ?></div>
                <div class="cqfs-fields">

                    <div class="cqfs-field">
                        <div class="cqfs-label">
                            <label for="cqfs-sender-email"><?php echo esc_html__('Sender Email ID (from)','cqfs'); ?></label>
                            <p class="description"><?php 
                            echo esc_html__('If not set, administrator email will be used. Try to use email id same as domain.','cqfs'); 
                            ?></p>
                        </div>
                        <div class="cqfs-input">
                            <input type="email" name="_cqfs[mail-sender-email]" id="cqfs-sender-email" value="<?php echo $sender_email; ?>">
                        </div>
                    </div>

                    <div class="cqfs-field checkbox">
                        <div class="cqfs-label">
                            <label for="cqfs-mail-admin"><?php echo esc_html__('Email to admin.','cqfs'); ?></label>
                            <p class="description"><?php 
                            echo esc_html__('Send email to admin when a form is submitted by a user. Administrator email will be used.','cqfs'); 
                            ?></p>
                        </div>
                        <div class="cqfs-input">
                            <input type="checkbox" 
                            name="_cqfs[mail-admin]" 
                            id="cqfs-mail-admin" <?php 
                            if($email_to_admin) { echo esc_attr('checked'); } ?>>
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
                            if($email_to_user) { echo esc_attr('checked'); } ?>>
                        </div>
                    </div>

                    <div class="cqfs-field">
                        <div class="cqfs-label">
                            <label for="cqfs-mail-additional"><?php echo esc_html__('Additional Notes','cqfs'); ?></label>
                            <p class="description"><?php 
                            echo esc_html__('Add any additional notes in the email. It will appear above the footer. HTML allowed as post.','cqfs'); 
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
                            echo esc_html__('Add custom footer content for the email. HTML allowed as post.','cqfs'); 
                            ?></p>
                        </div>
                        <div class="cqfs-input">
                            <textarea name="_cqfs[mail-footer]" id="cqfs-mail-footer" rows="6"><?php 
                            echo $email_footer;
                            ?></textarea>
                        </div>
                    </div>

                </div>
                
                <?php submit_button(); ?>

            </form>
        </div>
        <?php
    }


    /**
     * Insert the 'add new' links to the submenu
     */
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
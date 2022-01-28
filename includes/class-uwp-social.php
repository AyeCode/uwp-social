<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class UsersWP_Social {

    private static $instance;

    public static function get_instance() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof UsersWP_Social ) ) {
            self::$instance = new UsersWP_Social;
            self::$instance->includes();
            self::$instance->setup_actions();
        }

        return self::$instance;
    }

    private function __construct() {
        self::$instance = $this;
    }

    private function setup_actions() {
      
        add_action('login_form_middle', array($this, 'login_form_button'));
        add_action('uwp_social_fields', array($this, 'social_login_buttons_on_templates'), 30, 2);
	    add_action('wpmu_delete_user', array($this, 'delete_user_row'), 30, 1);
        add_action('delete_user', array($this, 'delete_user_row'), 30, 1);
	    add_action('uwp_get_widgets', array($this, 'register_widgets'));
	    add_action( 'login_enqueue_scripts', array( $this,'login_styles' ) );
	    add_action( 'init', array($this, 'load_textdomain') );
        add_action('uwp_social_after_wp_insert_user', array($this, 'admin_notification'), 10, 2);
        add_action('login_form', array($this, 'admin_login_form'));
        add_action('register_form', array($this, 'admin_register_form'));

        add_action('uwp_clear_user_php_session', 'uwp_social_destroy_session_data');
        add_action('wp_logout', 'uwp_social_destroy_session_data');

        if(is_admin()){
            add_action( 'admin_init', array( $this, 'activation_redirect' ) );
            add_action('admin_init', array($this, 'automatic_upgrade'));
            add_filter( 'uwp_get_settings_pages', array( $this, 'get_settings_pages' ), 10, 1 );
        }

	    do_action( 'uwp_social_setup_actions' );
    }

    /**
     * Add required styles to the WP login page.
     */
    public function login_styles() {

        // add font awesome
        if(class_exists('WP_Font_Awesome_Settings')){
            $wpfa = WP_Font_Awesome_Settings::instance();
            $wpfa->enqueue_style();
        }
        
        // add AUI
        if(class_exists('AyeCode_UI_Settings')){
            $aui = AyeCode_UI_Settings::instance();
            $aui->enqueue_style();
        }

    }

    /**
     * Load the textdomain.
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'uwp-social', false, basename( UWP_SOCIAL_PATH ) . '/languages' );
    }

    private function includes() {

        require_once UWP_SOCIAL_PATH . '/includes/helpers.php';
        require_once UWP_SOCIAL_PATH . '/includes/social.php';
	    require_once UWP_SOCIAL_PATH . '/widgets/social.php';

        do_action( 'uwp_social_include_files' );

        if ( ! is_admin() )
            return;

        do_action( 'uwp_social_include_admin_files' );

    }

    public function automatic_upgrade(){
        $uwp_social_version = get_option( 'uwp_social_db_version' );

        if ( empty($uwp_social_version) || ($uwp_social_version && version_compare( $uwp_social_version, '1.0.9', '<' )) ) {

            flush_rewrite_rules();

            update_option( 'uwp_social_db_version', UWP_SOCIAL_VERSION );
        }

        if( version_compare( $uwp_social_version, '1.0.9', '<=' ) && empty( get_option( 'uwp-social-authuri-notice-dismissed' ) ) ) {
            add_action('admin_notices', array($this, 'admin_notices'));
            add_action('admin_footer', array($this, 'admin_footer_js'));
            add_action('wp_ajax_nopriv_uwp_social_dismiss_authuri_notice', array($this, 'dismiss_notice'));
            add_action('wp_ajax_uwp_social_dismiss_authuri_notice', array($this, 'dismiss_notice'));
        }
    }

    public function register_widgets($widgets){
	    $widgets[] = 'UWP_Social_Login_Widget';
	    return $widgets;
    }

    public function get_settings_pages($settings){
        $settings[] = include( UWP_SOCIAL_PATH . '/admin/class-uwp-settings-social.php' );
        return $settings;
    }

    /**
     * Redirect to the social settings page on activation.
     *
     * @since 1.0.0
     */
    public function activation_redirect() {
        // Bail if no activation redirect
        if ( !get_transient( '_uwp_social_activation_redirect' ) ) {
            return;
        }

        // Delete the redirect transient
        delete_transient( '_uwp_social_activation_redirect' );

        // Bail if activating from network, or bulk
        if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
            return;
        }

        wp_safe_redirect( admin_url( 'admin.php?page=userswp&tab=uwp-social' ) );
        exit;
    }

    public function login_form_button($content){
        return $content.uwp_social_login_buttons('login', false);
    }

    public function social_login_buttons_on_templates($type, $args) {
	    if ($type == 'register') {
		    $data = array();
		    $data['uwp_register_form_id'] = ! empty( $args['id'] ) ? $args['id'] : 1;
		    $redirect_page_id = uwp_get_option( 'register_redirect_to' );
		    if ( !isset( $_REQUEST['redirect_to'] ) && isset( $redirect_page_id ) && (int) $redirect_page_id == - 1 && wp_get_referer() ) {
			    $redirect_to = esc_url( wp_get_referer() );
		    } else {
			    $uwp_forms = new UsersWP_Forms();
			    $redirect_to = $uwp_forms->get_register_redirect_url( $data, false );
		    }

		    ob_start();
		    echo do_shortcode('[uwp_social type="register" redirect_to="'.$redirect_to.'"]');
		    echo ob_get_clean();
	    } else {
		    $data = array();
		    $redirect_page_id = uwp_get_option( 'login_redirect_to' );
		    if ( !isset( $_REQUEST['redirect_to'] ) && isset( $redirect_page_id ) && (int) $redirect_page_id == - 1 && wp_get_referer() ) {
			    $redirect_to = esc_url( wp_get_referer() );
		    } else {
			    $uwp_forms = new UsersWP_Forms();
			    $redirect_to = $uwp_forms->get_login_redirect_url( $data, false );
            }

		    ob_start();
		    echo do_shortcode('[uwp_social type="" redirect_to="'.$redirect_to.'"]');
		    echo ob_get_clean();
        }
    }

    public function delete_user_row($user_id) {
        if (!$user_id) {
            return;
        }

        global $wpdb;
        $social_table = $wpdb->base_prefix . 'uwp_social_profiles';
        $wpdb->query($wpdb->prepare("DELETE FROM {$social_table} WHERE user_id = %d", $user_id));
    }

    public function admin_notification( $user_id, $provider ) {
        //Get the user details
        $user = new WP_User($user_id);
        $user_login = stripslashes( $user->user_login );
        $profile_url = uwp_build_profile_tab_url($user_id);

        // The blogname option is escaped with esc_html on the way into the database
        // in sanitize_option we want to reverse this for the plain text arena of emails.
        $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

        $message  = sprintf(__('New user registration on your site: %s', 'uwp-social'), $blogname        ) . "\r\n\r\n";
        $message .= sprintf(__('Username: %s'                          , 'uwp-social'), $user_login      ) . "\r\n";
        $message .= sprintf(__('Provider: %s'                          , 'uwp-social'), $provider        ) . "\r\n";
        $message .= sprintf(__('Profile: %s'                           , 'uwp-social'), $profile_url  ) . "\r\n";
        $message .= sprintf(__('Email: %s'                             , 'uwp-social'), $user->user_email) . "\r\n";

        $message = apply_filters('uwp_social_admin_notification_content', $message, $user_id, $provider);

        wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration', 'uwp-social'), $blogname), $message);
    }

    public function admin_notices(){
        ?>
        <div class="notice error uwp-social-authuri-notice is-dismissible">
            <p><?php echo sprintf(__( '<strong>Breaking change: </strong> Authorized Redirect URI for all social login providers needs to be updated on apps. Go to %ssettings%s to get the new URI.', 'uwp-social' ), '<a href="'.admin_url( 'admin.php?page=userswp&tab=uwp-social' ).'">', '</a>'); ?></p>
        </div>
        <?php
    }

    public function admin_footer_js(){
        ?>
        <script type="text/javascript">
        jQuery(document).on( 'click', '.uwp-social-authuri-notice .notice-dismiss', function() {

            jQuery.ajax({
                url: ajaxurl,
                    data: {
                    action: 'uwp_social_dismiss_authuri_notice'
                }
            });

        });
        </script>
        <?php
    }

    public function dismiss_notice(){
        update_option( 'uwp-social-authuri-notice-dismissed', 1 );
        wp_die(1);
    }

    public function admin_login_form(){
	    if(1 != uwp_get_option('disable_admin_social_login')) {
		    uwp_social_login_buttons('admin_login');
	    }
    }

	public function admin_register_form(){
		if(1 != uwp_get_option('disable_admin_register_social_login')) {
			uwp_social_login_buttons('admin_register');
		}
	}

}
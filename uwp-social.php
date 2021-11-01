<?php
/*
Plugin Name: UsersWP - Social Login
Plugin URI: https://userswp.io/
Description: Social login add-on for UsersWP.
Version: 1.3.18
Author: AyeCode Ltd
Author URI: https://userswp.io
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: uwp-social
Domain Path: /languages
*/
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

define( 'UWP_SOCIAL_VERSION', '1.3.18' );

define( 'UWP_SOCIAL_PATH', plugin_dir_path( __FILE__ ) );

define( 'UWP_SOCIAL_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

define( 'UWP_SOCIAL_HYBRIDAUTH_ENDPOINT', UWP_SOCIAL_PLUGIN_URL . 'vendor/hybridauth/' );

if ( is_admin() ) {

    if ( !function_exists( 'deactivate_plugins' ) ) {
        include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }

    // Check UsersWP class exists or not.
    if ( !is_plugin_active( 'userswp/userswp.php' ) ) {

        deactivate_plugins( plugin_basename( __FILE__ ) );
        function uwp_social_requires_userswp_plugin() {
            echo '<div class="notice notice-warning is-dismissible"><p><strong>' . sprintf( __( '%s requires %sUsersWP%s plugin to be installed and active.', 'uwp-social' ), 'UsersWP - Social', '<a href="https://wordpress.org/plugins/userswp/" target="_blank">', '</a>' ) . '</strong></p></div>';
        }
        add_action( 'admin_notices', 'uwp_social_requires_userswp_plugin' );
        return;

    }
}

require plugin_dir_path(__FILE__) . 'includes/class-uwp-social.php';

function activate_uwp_social($network_wide) {
    if (is_multisite()) {
        if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
            require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
        }

        // Network active.
        if ( is_plugin_active_for_network( 'userswp/userswp.php' ) ) {
            $network_wide = true;
        }
        if ($network_wide) {
            $main_blog_id = (int) get_network()->site_id;
            // Switch to the new blog.
            switch_to_blog( $main_blog_id );

            require_once('includes/activator.php');
            UWP_Social_Activator::activate();

            // Restore original blog.
            restore_current_blog();
        } else {
            require_once('includes/activator.php');
            UWP_Social_Activator::activate();
        }
    } else {
        require_once('includes/activator.php');
        UWP_Social_Activator::activate();
    }
}
register_activation_hook( __FILE__, 'activate_uwp_social' );


function init_uwp_social() {

    UsersWP_Social::get_instance();

}
add_action( 'plugins_loaded', 'init_uwp_social', apply_filters( 'uwp_social_action_priority', 10 ) );
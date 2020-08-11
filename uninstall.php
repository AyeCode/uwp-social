<?php
/**
 * Uninstall UsersWP - Social Login
 *
 * Uninstalling UsersWP - Social Login deletes the plugin options.
 *
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$settings = get_option('uwp_settings', array());
if ( isset($settings[ 'uwp_uninstall_social_data' ]) && 1 == $settings[ 'uwp_uninstall_social_data' ] ) {
    global $wpdb;
    $wpdb->hide_errors();
    
    $options = array(
        'enable_uwp_social_google',
        'uwp_social_google_id',
        'uwp_social_google_secret',
        'uwp_social_google_callback',
        'uwp_social_google_pick_username',
        'uwp_social_google_pick_email',
        'enable_uwp_social_facebook',
        'uwp_social_facebook_id',
        'uwp_social_facebook_secret',
        'uwp_social_facebook_callback',
        'uwp_social_facebook_pick_username',
        'uwp_social_facebook_pick_email',
        'enable_uwp_social_twitter',
        'uwp_social_twitter_key',
        'uwp_social_twitter_secret',
        'uwp_social_twitter_callback',
        'uwp_social_twitter_pick_username',
        'uwp_social_twitter_pick_email',
        'enable_uwp_social_linkedin',
        'uwp_social_linkedin_key',
        'uwp_social_linkedin_secret',
        'uwp_social_linkedin_callback',
        'uwp_social_linkedin_pick_username',
        'uwp_social_linkedin_pick_email',
        'enable_uwp_social_instagram',
        'uwp_social_instagram_secret',
        'uwp_social_instagram_callback',
        'uwp_social_instagram_pick_username',
        'uwp_social_instagram_pick_email',
        'enable_uwp_social_yahoo',
        'uwp_social_yahoo_id',
        'uwp_social_yahoo_secret',
        'uwp_social_yahoo_callback',
        'uwp_social_yahoo_pick_username',
        'uwp_social_yahoo_pick_email',
        'enable_uwp_social_wordpress',
        'uwp_social_wordpress_id',
        'uwp_social_wordpress_secret',
        'uwp_social_wordpress_callback',
        'uwp_social_wordpress_pick_username',
        'uwp_social_wordpress_pick_email',
        'enable_uwp_social_vkontakte',
        'uwp_social_vkontakte_id',
        'uwp_social_vkontakte_secret',
        'uwp_social_vkontakte_callback',
        'uwp_social_vkontakte_pick_username',
        'uwp_social_vkontakte_pick_email',
        'uwp_uninstall_social_data',
    );

    $options = apply_filters('uwp_social_uninstall_data', $options);
    
    if ( !empty( $options ) ) {
        foreach ( $options as $option ) {
            unset( $settings[$option] );
        }
    }

    update_option('uwp_settings', $settings);

    $tbl_social = $wpdb->prefix . "uwp_social_profiles";
    $wpdb->query( "DROP TABLE IF EXISTS ".$tbl_social );
}
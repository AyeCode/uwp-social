<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    uwp_social
 * @subpackage uwp_social/includes
 * @author     GeoDirectory Team <info@wpgeodirectory.com>
 */
class UWP_Social_Activator
{

    /**
     * @since    1.0.0
     */
    public static function activate()
    {
        self::create_tables();
        flush_rewrite_rules();

        // Set activation redirect flag
        set_transient( '_uwp_social_activation_redirect', true, 30 );
    }

    public static function create_tables()
    {
        global $wpdb;

        $table_name = $wpdb->base_prefix . 'uwp_social_profiles';

        $wpdb->hide_errors();

        $collate = '';
        if ($wpdb->has_cap('collation')) {
            if (!empty($wpdb->charset)) $collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if (!empty($wpdb->collate)) $collate .= " COLLATE $wpdb->collate";
        }


        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $social_profiles = "CREATE TABLE " . $table_name . " (
                            id int(11) NOT NULL AUTO_INCREMENT,
                            user_id int(11) NOT NULL,
                            provider varchar(50) NOT NULL,
                            object_sha varchar(45) NOT NULL,
                            identifier varchar(255) NOT NULL,
                            profileurl varchar(255) NOT NULL,
                            websiteurl varchar(255) NOT NULL,
                            photourl varchar(255) NOT NULL,
                            displayname varchar(150) NOT NULL,
                            description varchar(255) NOT NULL,
                            firstname varchar(150) NOT NULL,
                            lastname varchar(150) NOT NULL,
                            gender varchar(10) NOT NULL,
                            language varchar(20) NOT NULL,
                            age varchar(10) NOT NULL,
                            birthday int(11) NOT NULL,
                            birthmonth int(11) NOT NULL,
                            birthyear int(11) NOT NULL,
                            email varchar(255) NOT NULL,
                            emailverified varchar(255) NOT NULL,
                            phone varchar(75) NOT NULL,
                            address varchar(255) NOT NULL,
                            country varchar(75) NOT NULL,
                            region varchar(50) NOT NULL,
                            city varchar(50) NOT NULL,
                            zip varchar(25) NOT NULL,
                            UNIQUE KEY id (id),
                            KEY user_id (user_id),
                            KEY provider (provider)
                          ) $collate";

        $social_profiles = apply_filters('uwp_social_profiles_before_table_create', $social_profiles);

        dbDelta($social_profiles);

        update_option( 'uwp_social_db_version', UWP_SOCIAL_VERSION );
    }
}
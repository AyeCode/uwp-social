<?php
/**
 * UsersWP import export Settings
 *
 * @author      AyeCode
 * @category    Admin
 * @package     userswp/Admin
 * @version     1.0.24
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!class_exists('UsersWP_Settings_Social', false)) :

    /**
     * UsersWP_Settings_Social.
     */
    class UsersWP_Settings_Social extends UsersWP_Settings_Page
    {

        public function __construct()
        {

            $this->id = 'uwp-social';
            $this->label = __('Social', 'uwp-social');

            add_filter('uwp_settings_tabs_array', array($this, 'add_settings_page'), 20);
            add_action('uwp_settings_' . $this->id, array($this, 'output'));
            add_action('uwp_sections_' . $this->id, array($this, 'output_toggle_advanced'));
            add_action('uwp_settings_save_' . $this->id, array($this, 'save'));
            add_action('uwp_sections_' . $this->id, array($this, 'output_sections'));
            add_action('uwp_get_settings_uninstall', array($this, 'uninstall_options'));

        }

        /**
         * Output the settings.
         */
        public function output()
        {
            global $current_section;

            $settings = $this->get_settings($current_section);
            UsersWP_Admin_Settings::output_fields($settings);
        }

        /**
         * Save settings.
         */
        public function save()
        {
            global $current_section;

            $settings = $this->get_settings($current_section);
            UsersWP_Admin_Settings::save_fields($settings);
        }

        /**
         * Get sections.
         *
         * @return array
         */
        public function get_sections()
        {

            $sections = array(
                '' => __( 'General', 'uwp-social' ),
                'google' => __( 'Google', 'uwp-social' ),
                'facebook' => __( 'Facebook', 'uwp-social' ),
                'twitter' => __( 'Twitter', 'uwp-social' ),
                'linkedin' => __( 'LinkedIn', 'uwp-social' ),
                'instagram' => __( 'Instagram', 'uwp-social' ),
                'yahoo' => __( 'Yahoo', 'uwp-social' ),
                'wordpress' => __( 'WordPress', 'uwp-social' ),
                'vkontakte' => __( 'VKontakte', 'uwp-social' ),
            );

            return apply_filters('uwp_get_sections_' . $this->id, $sections);
        }

        public function get_settings($current_section = '')
        {

            if ( !empty( $current_section ) && 'google' === $current_section ) {
                $callback = uwp_get_callback_url('google');
                $settings = apply_filters('uwp_social_google_options', array(
                    array(
                        'title' => __('Google Settings', 'uwp-social'),
                        'type' => 'title',
                        'id' => 'social_google_settings_options',
                        'desc' => sprintf(__('<b>Note:</b> Create API key and secret from %sDeveloper site%s and enter below. Use %s for Authorized redirect URI.','uwp-social'), '<a href="https://console.developers.google.com" target="_blank">', '</a>', '<code class="social_setting_title">'.$callback.'</code>'),
                        'desc_tip' => false,
                    ),
                    array(
                        'id'   => 'enable_uwp_social_google',
                        'name' => 'Enable Google',
                        'desc' => '',
                        'type' => 'checkbox',
                        'default'  => '1',
                    ),
                    array(
                        'id' => 'uwp_social_google_id',
                        'name' => __( 'Google APP ID', 'uwp-social' ),
                        'desc' => "",
                        'type' => 'text',
                        'placeholder' => __( 'Enter Google APP ID', 'uwp-social' )
                    ),
                    array(
                        'id' => 'uwp_social_google_secret',
                        'name' => __( 'Google APP Secret', 'uwp-social' ),
                        'desc' => "",
                        'type' => 'text',
                        'placeholder' => __( 'Enter Google APP Secret', 'uwp-social' )
                    ),
                    array(
                        'id'   => 'uwp_social_google_pick_username',
                        'name' => 'Let the user enter username?',
                        'desc' => 'By default, the username is auto generated. If this option enabled then we would ask the user to pick the username by displaying a form.',
                        'type' => 'checkbox',
                        'default'  => '0',
                        'class' => 'uwp_label_inline',
                    ),
                    array(
                        'id'   => 'uwp_social_google_pick_email',
                        'name' => 'Let the user enter email?',
                        'desc' => 'By default, the email returned by the provider is used. If this option enabled then we would ask the user to enter the email by displaying a form.',
                        'type' => 'checkbox',
                        'default'  => '0',
                    ),
                ));

            } elseif ( !empty( $current_section ) && 'facebook' === $current_section ) {
                $callback = uwp_get_callback_url('facebook');
                $settings = apply_filters('uwp_social_facebook_options', array(
                    array(
                        'title' => __('Facebook Settings', 'uwp-social'),
                        'type' => 'title',
                        'id' => 'social_facebook_settings_options',
                        'desc' => sprintf(__('<b>Note:</b> Create API key and secret from %sDeveloper site%s and enter below. Use <code>%s</code> for Authorized redirect URI.','uwp-social'), '<a href="https://developers.facebook.com/apps" target="_blank">', '</a>', $callback),
                        'desc_tip' => false,
                    ),
                    array(
                        'id'   => 'enable_uwp_social_facebook',
                        'name' => 'Enable Facebook',
                        'desc' => '',
                        'type' => 'checkbox',
                        'default'  => '1',
                        'class' => 'uwp_label_inline',
                    ),
                    array(
                        'id' => 'uwp_social_facebook_id',
                        'name' => __( 'Facebook API ID', 'uwp-social' ),
                        'desc' => "",
                        'type' => 'text',
                        'placeholder' => __( 'Enter Facebook API ID', 'uwp-social' )
                    ),
                    array(
                        'id' => 'uwp_social_facebook_secret',
                        'name' => __( 'Facebook API Secret', 'uwp-social' ),
                        'desc' => "",
                        'type' => 'text',
                        'placeholder' => __( 'Enter Facebook API Secret', 'uwp-social' )
                    ),
                    array(
                        'id'   => 'uwp_social_facebook_pick_username',
                        'name' => 'Let the user enter username?',
                        'desc' => 'By default, the username is auto generated. If this option enabled then we would ask the user to pick the username by displaying a form.',
                        'type' => 'checkbox',
                        'default'  => '0',
                        'class' => 'uwp_label_inline',
                    ),
                    array(
                        'id'   => 'uwp_social_facebook_pick_email',
                        'name' => 'Let the user enter email?',
                        'desc' => 'By default, the email returned by the provider is used. If this option enabled then we would ask the user to enter the email by displaying a form.',
                        'type' => 'checkbox',
                        'default'  => '0',
                        'class' => 'uwp_label_inline',
                    ),
                ));

            } elseif ( !empty( $current_section ) && 'twitter' === $current_section ) {
                $callback = uwp_get_callback_url('twitter');
                $settings = apply_filters('uwp_social_twitter_options', array(
                    array(
                        'title' => __('Twitter Settings', 'uwp-social'),
                        'type' => 'title',
                        'id' => 'social_twitter_settings_options',
                        'desc' => sprintf(__('<b>Note:</b> Create API key and secret from %sDeveloper site%s and enter below. Use <code>%s</code> for Authorized redirect URI.','uwp-social'), '<a href="https://dev.twitter.com/apps" target="_blank">', '</a>', $callback),
                        'desc_tip' => false,
                    ),
                    array(
                        'id'   => 'enable_uwp_social_twitter',
                        'name' => 'Enable Twitter',
                        'desc' => '',
                        'type' => 'checkbox',
                        'default'  => '1',
                        'class' => 'uwp_label_inline',
                    ),
                    array(
                        'id' => 'uwp_social_twitter_key',
                        'name' => __( 'Twitter API Key', 'uwp-social' ),
                        'desc' => "",
                        'type' => 'text',
                        'placeholder' => __( 'Enter Twitter API Key', 'uwp-social' )
                    ),
                    array(
                        'id' => 'uwp_social_twitter_secret',
                        'name' => __( 'Twitter API Secret', 'uwp-social' ),
                        'desc' => "",
                        'type' => 'text',
                        'placeholder' => __( 'Enter Twitter API Secret', 'uwp-social' )
                    ),
                    array(
                        'id'   => 'uwp_social_twitter_pick_username',
                        'name' => 'Let the user enter username?',
                        'desc' => 'By default, the username is auto generated. If this option enabled then we would ask the user to pick the username by displaying a form.',
                        'type' => 'checkbox',
                        'default'  => '0',
                        'class' => 'uwp_label_inline',
                    ),
                    array(
                        'id'   => 'uwp_social_twitter_pick_email',
                        'name' => 'Let the user enter email?',
                        'desc' => 'By default, the email returned by the provider is used. If this option enabled then we would ask the user to enter the email by displaying a form.',
                        'type' => 'checkbox',
                        'default'  => '0',
                        'class' => 'uwp_label_inline',
                    ),
                ));

            } elseif ( !empty( $current_section ) && 'linkedin' === $current_section ) {
                $callback = uwp_get_callback_url('linkedin');
                $settings = apply_filters('uwp_social_linkedin_options', array(
                    array(
                        'title' => __('Linkedin Settings', 'uwp-social'),
                        'type' => 'title',
                        'id' => 'social_linkedin_settings_options',
                        'desc' => sprintf(__('<b>Note:</b> Create API key and secret from %sDeveloper site%s and enter below. Use <code>%s</code> for Authorized redirect URI.','uwp-social'), '<a href="https://www.linkedin.com/developer/apps" target="_blank">', '</a>', $callback),
                        'desc_tip' => false,
                    ),
                    array(
                        'id'   => 'enable_uwp_social_linkedin',
                        'name' => 'Enable LinkedIn',
                        'desc' => '',
                        'type' => 'checkbox',
                        'default'  => '1',
                        'class' => 'uwp_label_inline',
                    ),
                    array(
                        'id' => 'uwp_social_linkedin_key',
                        'name' => __( 'LinkedIn Client ID', 'uwp-social' ),
                        'desc' => "",
                        'type' => 'text',
                        'placeholder' => __( 'Enter LinkedIn Client ID', 'uwp-social' )
                    ),
                    array(
                        'id' => 'uwp_social_linkedin_secret',
                        'name' => __( 'LinkedIn Client Secret', 'uwp-social' ),
                        'desc' => "",
                        'type' => 'text',
                        'placeholder' => __( 'Enter LinkedIn Client Secret', 'uwp-social' )
                    ),
                    array(
                        'id'   => 'uwp_social_linkedin_pick_username',
                        'name' => 'Let the user enter username?',
                        'desc' => 'By default, the username is auto generated. If this option enabled then we would ask the user to pick the username by displaying a form.',
                        'type' => 'checkbox',
                        'default'  => '0',
                        'class' => 'uwp_label_inline',
                    ),
                    array(
                        'id'   => 'uwp_social_linkedin_pick_email',
                        'name' => 'Let the user enter email?',
                        'desc' => 'By default, the email returned by the provider is used. If this option enabled then we would ask the user to enter the email by displaying a form.',
                        'type' => 'checkbox',
                        'default'  => '0',
                        'class' => 'uwp_label_inline',
                    ),
                ));

            } elseif ( !empty( $current_section ) && 'instagram' === $current_section ) {
                $callback = uwp_get_callback_url('instagram');
                $settings = apply_filters('uwp_social_instagram_options', array(
                    array(
                        'title' => __('Instagram Settings', 'uwp-social'),
                        'type' => 'title',
                        'id' => 'social_instagram_settings_options',
                        'desc' => sprintf(__('<b>Note:</b> Create API key and secret from %sDeveloper site%s and enter below. Use <code>%s</code> for Authorized redirect URI.','uwp-social'), '<a href="http://instagram.com/developer/clients/manage/" target="_blank">', '</a>', $callback),
                        'desc_tip' => false,
                    ),
                    array(
                        'id'   => 'enable_uwp_social_instagram',
                        'name' => 'Enable Instagram',
                        'desc' => '',
                        'type' => 'checkbox',
                        'default'  => '1',
                        'class' => 'uwp_label_inline',
                    ),
                    array(
                        'id' => 'uwp_social_instagram_id',
                        'name' => __( 'Instagram APP ID', 'uwp-social' ),
                        'desc' => "",
                        'type' => 'text',
                        'placeholder' => __( 'Enter Instagram APP ID', 'uwp-social' )
                    ),
                    array(
                        'id' => 'uwp_social_instagram_secret',
                        'name' => __( 'Instagram APP Secret', 'uwp-social' ),
                        'desc' => "",
                        'type' => 'text',
                        'placeholder' => __( 'Enter Instagram APP Secret', 'uwp-social' )
                    ),
                    array(
                        'id'   => 'uwp_social_instagram_pick_username',
                        'name' => 'Let the user enter username?',
                        'desc' => 'By default, the username is auto generated. If this option enabled then we would ask the user to pick the username by displaying a form.',
                        'type' => 'checkbox',
                        'default'  => '0',
                        'class' => 'uwp_label_inline',
                    ),
                    array(
                        'id'   => 'uwp_social_instagram_pick_email',
                        'name' => 'Let the user enter email?',
                        'desc' => 'By default, the email returned by the provider is used. If this option enabled then we would ask the user to enter the email by displaying a form.',
                        'type' => 'checkbox',
                        'default'  => '0',
                        'class' => 'uwp_label_inline',
                    ),
                ));

            } elseif ( !empty( $current_section ) && 'yahoo' === $current_section ) {
                $callback = uwp_get_callback_url('yahoo');
                $settings = apply_filters('uwp_social_yahoo_options', array(
                    array(
                        'title' => __('Social Settings', 'uwp-social'),
                        'type' => 'title',
                        'id' => 'social_yahoo_settings_options',
                        'desc' => sprintf(__('<b>Note:</b> Create API key and secret from %sDeveloper site%s and enter below. Use <code>%s</code> for Authorized redirect URI.','uwp-social'), '<a href="https://developer.yahoo.com/apps" target="_blank">', '</a>', $callback),
                        'desc_tip' => false,
                    ),
                    array(
                        'id'   => 'enable_uwp_social_yahoo',
                        'name' => 'Enable Yahoo',
                        'desc' => '',
                        'type' => 'checkbox',
                        'default'  => '1',
                        'class' => 'uwp_label_inline',
                    ),
                    array(
                        'id' => 'uwp_social_yahoo_id',
                        'name' => __( 'Yahoo Client ID', 'uwp-social' ),
                        'desc' => "",
                        'type' => 'text',
                        'placeholder' => __( 'Enter Yahoo Client ID', 'uwp-social' )
                    ),
                    array(
                        'id' => 'uwp_social_yahoo_secret',
                        'name' => __( 'Yahoo Client Secret', 'uwp-social' ),
                        'desc' => "",
                        'type' => 'text',
                        'placeholder' => __( 'Enter Yahoo Client Secret', 'uwp-social' )
                    ),
                    array(
                        'id'   => 'uwp_social_yahoo_pick_username',
                        'name' => 'Let the user enter username?',
                        'desc' => 'By default, the username is auto generated. If this option enabled then we would ask the user to pick the username by displaying a form.',
                        'type' => 'checkbox',
                        'default'  => '0',
                        'class' => 'uwp_label_inline',
                    ),
                    array(
                        'id'   => 'uwp_social_yahoo_pick_email',
                        'name' => 'Let the user enter email?',
                        'desc' => 'By default, the email returned by the provider is used. If this option enabled then we would ask the user to enter the email by displaying a form.',
                        'type' => 'checkbox',
                        'default'  => '0',
                        'class' => 'uwp_label_inline',
                    ),
                ));

            } elseif ( !empty( $current_section ) && 'wordpress' === $current_section ) {
                $callback = uwp_get_callback_url('wordpress');
                $settings = apply_filters('uwp_social_wordpress_options', array(
                    array(
                        'title' => __('WordPress Settings', 'uwp-social'),
                        'type' => 'title',
                        'id' => 'social_wordpress_settings_options',
                        'desc' => sprintf(__('<b>Note:</b> Create API key and secret from %sDeveloper site%s and enter below. Use <code>%s</code> for Authorized redirect URI.','uwp-social'), '<a href="https://developer.wordpress.com/apps/" target="_blank">', '</a>', $callback),
                        'desc_tip' => false,
                    ),
                    array(
                        'id'   => 'enable_uwp_social_wordpress',
                        'name' => 'Enable WordPress',
                        'desc' => '',
                        'type' => 'checkbox',
                        'default'  => '1',
                        'class' => 'uwp_label_inline',
                    ),
                    array(
                        'id' => 'uwp_social_wordpress_id',
                        'name' => __( 'WordPress APP ID', 'uwp-social' ),
                        'desc' => "",
                        'type' => 'text',
                        'placeholder' => __( 'Enter WordPress APP ID', 'uwp-social' )
                    ),
                    array(
                        'id' => 'uwp_social_wordpress_secret',
                        'name' => __( 'WordPress APP Secret', 'uwp-social' ),
                        'desc' => "",
                        'type' => 'text',
                        'placeholder' => __( 'Enter WordPress APP Secret', 'uwp-social' )
                    ),
                    array(
                        'id'   => 'uwp_social_wordpress_pick_username',
                        'name' => 'Let the user enter username?',
                        'desc' => 'By default, the username is auto generated. If this option enabled then we would ask the user to pick the username by displaying a form.',
                        'type' => 'checkbox',
                        'default'  => '0',
                        'class' => 'uwp_label_inline',
                    ),
                    array(
                        'id'   => 'uwp_social_wordpress_pick_email',
                        'name' => 'Let the user enter email?',
                        'desc' => 'By default, the email returned by the provider is used. If this option enabled then we would ask the user to enter the email by displaying a form.',
                        'type' => 'checkbox',
                        'default'  => '0',
                        'class' => 'uwp_label_inline',
                    ),
                ));

            } elseif ( !empty( $current_section ) && 'vkontakte' === $current_section ) {
                $callback = uwp_get_callback_url('vkontakte');
                $settings = apply_filters('uwp_social_vkontakte_options', array(
                    array(
                        'title' => __('Vkontakte Settings', 'uwp-social'),
                        'type' => 'title',
                        'id' => 'social_vkontakte_settings_options',
                        'desc' => sprintf(__('<b>Note:</b> Create API key and secret from %sDeveloper site%s and enter below. Use <code>%s</code> for Authorized redirect URI.','uwp-social'), '<a href="https://vk.com/apps?act=manage" target="_blank">', '</a>', $callback),
                        'desc_tip' => false,
                    ),
                    array(
                        'id'   => 'enable_uwp_social_vkontakte',
                        'name' => 'Enable Vkontakte',
                        'desc' => '',
                        'type' => 'checkbox',
                        'default'  => '1',
                        'class' => 'uwp_label_inline',
                    ),
                    array(
                        'id' => 'uwp_social_vkontakte_id',
                        'name' => __( 'Vkontakte APP ID', 'uwp-social' ),
                        'desc' => "",
                        'type' => 'text',
                        'placeholder' => __( 'Enter Vkontakte APP ID', 'uwp-social' )
                    ),
                    array(
                        'id' => 'uwp_social_vkontakte_secret',
                        'name' => __( 'Vkontakte APP Secret', 'uwp-social' ),
                        'desc' => "",
                        'type' => 'text',
                        'placeholder' => __( 'Enter Vkontakte APP Secret', 'uwp-social' )
                    ),
                    array(
                        'id'   => 'uwp_social_vkontakte_pick_username',
                        'name' => 'Let the user enter username?',
                        'desc' => 'By default, the username is auto generated. If this option enabled then we would ask the user to pick the username by displaying a form.',
                        'type' => 'checkbox',
                        'default'  => '0',
                        'class' => 'uwp_label_inline',
                    ),
                    array(
                        'id'   => 'uwp_social_vkontakte_pick_email',
                        'name' => 'Let the user enter email?',
                        'desc' => 'By default, the email returned by the provider is used. If this option enabled then we would ask the user to enter the email by displaying a form.',
                        'type' => 'checkbox',
                        'default'  => '0',
                        'class' => 'uwp_label_inline',
                    ),
                ));

            } else{

                global $hide_save_button;
                $hide_save_button = true;

                $settings = apply_filters('uwp_social_options', array(
                    array(
                        'title' => __('Social Settings', 'uwp-social'),
                        'type' => 'title',
                        'desc' => sprintf(__('You can allow users to login via several social networks, once enabled the login icons will appear on most login forms and you can also use the UWP widget to add a social login buttons to widget areas. See %s Documentation %s','uwp-social'), '<a href="https://userswp.io/docs/social-login/" target="_blank">', '</a>'),
                        'id' => 'social_general_settings_options',
                        'desc_tip' => false,
                    ),
                ));

            }

            $settings = apply_filters('uwp_get_settings_' . $this->id, $settings, $current_section);

            $settings[] = array('type' => 'sectionend', 'id' => 'social_general_settings_options');

            return $settings;
        }

        public function uninstall_options( $settings ){

            $settings[] = array(
                'name'     => __( 'UsersWP - Social', 'uwp-social' ),
                'desc'     => __( 'Remove all data when deleted?', 'uwp-social' ),
                'id'       => 'uwp_uninstall_social_data',
                'type'     => 'checkbox',
            );

            return $settings;
        }

    }

endif;


return new UsersWP_Settings_Social();
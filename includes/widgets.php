<?php

class UWP_Social_Login_Widget extends WP_Super_Duper {

    public function __construct() {

        $options = array(
            'textdomain'    => 'uwp-social',
            'block-icon'    => 'admin-site',
            'block-category'=> 'widgets',
            'block-keywords'=> "['userswp','social']",
            'class_name'     => __CLASS__,
            'base_id'       => 'uwp_social',
            'name'          => __('UWP > Social Login','uwp-social'),
            'widget_ops'    => array(
                'classname'   => 'uwp-social-class', // widget class
                'description' => esc_html__('Displays Social Login.','uwp-social'), // widget description
            ),
            'arguments'     => array(
                'title'  => array(
                    'title'       => __( 'Social widget title', 'uwp-social' ),
                    'desc'        => __( 'Enter your UWP social Login widget title.', 'uwp-social' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    'default'     => '',
                    'advanced'    => false
                ),
            )

        );

        parent::__construct( $options );
    }

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        ob_start();

        if (is_user_logged_in()) {
            return;
        }

        echo '<div class="uwp_widgets uwp_widget_social_login">';

        echo uwp_social_login_buttons_display();

        echo '</div>';

        $output = ob_get_contents();

        ob_end_clean();

        return trim($output);

    }
}
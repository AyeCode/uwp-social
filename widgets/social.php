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
                'type'  => array(
	                'title' => __('Form Type', 'uwp-social'),
	                'desc' => __('Select form type where displaying the social login.', 'uwp-social'),
	                'type' => 'select',
	                'options'   =>  array(
		                ""        =>  __('Login', 'uwp-social'),
		                "register" =>  __('Register', 'uwp-social'),
		                "admin_login" =>  __('Admin Login', 'uwp-social'),
		                "admin_register" =>  __('Admin Register', 'uwp-social'),
	                ),
	                'default'  => '',
	                'desc_tip' => true,
	                'advanced' => true
                ),
                'redirect_to' => array(
	                'type' => 'text',
	                'title' => __('Redirect to:', 'uwp-social'),
	                'desc' => __('Enter the url you want to redirect after login.', 'uwp-social'),
	                'placeholder' => '',
	                'default' => '',
	                'desc_tip' => true,
	                'advanced' => true
                ),
            )

        );

        parent::__construct( $options );
    }

    public function output( $args = array(), $widget_args = array(), $content = '' ) {

        if (is_user_logged_in()) {
            return;
        }

	    ob_start();

        echo '<div class="uwp_widgets uwp_widget_social_login">';

	    $design_style = !empty($args['design_style']) ? esc_attr($args['design_style']) : uwp_get_option("design_style",'bootstrap');
	    $template = $design_style ? $design_style."/social.php" : "social.php";

	    uwp_get_template($template, $args, '', UWP_SOCIAL_PATH.'templates');

        echo '</div>';

        $output = ob_get_clean();

        return trim($output);

    }
}
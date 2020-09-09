<?php
function uwp_email_exists( $email )
{
    if( function_exists('email_exists') )
    {
        return email_exists( $email );
    }

    if( $user = get_user_by( 'email', $email ) )
    {
        return $user->ID;
    }

    return false;
}

function uwp_get_social_profile( $provider, $provider_uid )
{
    global $wpdb;

    $sql = "SELECT user_id FROM `{$wpdb->base_prefix}uwp_social_profiles` WHERE provider = %s AND identifier = %s";

    return $wpdb->get_var( $wpdb->prepare( $sql, $provider, $provider_uid ) );
}

function uwp_get_social_profile_by_email_verified( $email_verified )
{
    global $wpdb;

    $sql = "SELECT user_id FROM `{$wpdb->base_prefix}uwp_social_profiles` WHERE emailverified = %s";

    return $wpdb->get_var( $wpdb->prepare( $sql, $email_verified ) );
}

function uwp_social_store_user_profile( $user_id, $provider, $profile )
{
    
    global $wpdb;
    
    $wpdb->show_errors();

    $sql = "SELECT id, object_sha FROM `{$wpdb->base_prefix}uwp_social_profiles` where user_id = %d and provider = %s and identifier = %s";

    $rs  = $wpdb->get_results( $wpdb->prepare( $sql, $user_id, $provider, $profile->identifier ) );

    // we only sotre the user profile if it has changed since last login.
    $object_sha = sha1( serialize( $profile ) );

    // checksum
    if( ! empty( $rs ) && $rs[0]->object_sha == $object_sha )
    {
        return false;
    }

    $table_data = array(
        "id"         => 'null',
        "user_id"    => $user_id,
        "provider"   => $provider,
        "object_sha" => $object_sha
    );

    if(  ! empty( $rs ) )
    {
        $table_data['id'] = $rs[0]->id;
    }

    $fields = array(
        'identifier',
        'profileurl',
        'websiteurl',
        'photourl',
        'displayname',
        'description',
        'firstname',
        'lastname',
        'gender',
        'language',
        'age',
        'birthday',
        'birthmonth',
        'birthyear',
        'email',
        'emailverified',
        'phone',
        'address',
        'country',
        'region',
        'city',
        'zip'
    );
    
    foreach( $profile as $key => $value )
    {
        $key = strtolower($key);

        if( in_array( $key, $fields ) )
        {
            $table_data[ $key ] = (string) $value;
        }
    }

    $wpdb->replace( "{$wpdb->base_prefix}uwp_social_profiles", $table_data );

    return $wpdb->insert_id;
}

function uwp_social_login_buttons() {
    $providers = uwp_get_available_social_providers();
    $is_bootstrap = uwp_get_option("design_style",'bootstrap') ?  true : false;
	$title = uwp_get_option('label_for_social_login',__('Login via Social','uwp-social'));
	$title = apply_filters('uwp_social_login_buttons_label', $title);

	ob_start();
    foreach ($providers as $array_key => $provider) {
        $btn_output = '';
        $provider_id   = isset( $provider["provider_id"]   ) ? $provider["provider_id"]   : '';
        $provider_name = isset( $provider["provider_name"] ) ? $provider["provider_name"] : '';
        $url = '';
        $enable = uwp_get_option('enable_uwp_social_'.$array_key, "0");
        if ($enable == "1") {
            if (isset($provider["require_client_id"]) && $provider["require_client_id"]) {
                $key = uwp_get_option('uwp_social_'.$array_key.'_id', "");
            } else {
                $key = uwp_get_option('uwp_social_'.$array_key.'_key', "");
            }
            $secret = uwp_get_option('uwp_social_'.$array_key.'_secret', "");
            $url = home_url() . "/?action=uwp_social_authenticate&provider=".$provider_id;


            //General |Facebook |Twitter |LinkedIn |Instagram |Yahoo |WordPress |VKontakte
            $icons = array(
                'facebook'  => 'fab fa-facebook-f',
                'twitter'  => 'fab fa-twitter',
                'instagram'  => 'fab fa-instagram',
                'linkedin'  => 'fab fa-linkedin-in',
                'wordpress'  => 'fab fa-wordpress-simple',
                'vkontakte'  => 'fab fa-vk',

            );

            $social_name_class = strtolower($provider_id);
            $social_icon_class = isset($icons[$social_name_class]) ? $icons[$social_name_class] : "fab fa-". $social_name_class;

            if (!empty($key) && !empty($secret)) {
                if( $is_bootstrap && class_exists("AUI") ){
	                if('google' == strtolower($provider_id)){

                        $btn_output .= "<br/>";
                        $btn_output .= "<a href=\"$url\">";
                        $btn_output .= '<img src="'.esc_url(UWP_SOCIAL_PLUGIN_URL . 'assets/images/btn_google_signin_dark_normal_web.png').'" alt="Sign in with Google" class="w-auto">';
                        $btn_output .= "</a>";

	                } else {
                        $btn_output .=  aui()->button( array(
			                'href'  => $url,
			                'class'     => 'ml-1 mb-1 border-0 btn  btn-'. $social_name_class.' btn-sm btn-circle',
			                'content' => '<i class="'. $social_icon_class.'  fa-fw fa-lg"></i>',
			                'data-toggle' => 'tooltip',
			                'title' => $provider_name,
		                ) );
                    }
                }else{
	                if('google' == strtolower($provider_id)){
                        $btn_output .= "<br/>";
                        $btn_output .= '<li class="uwp_social_login_icon">';
                        $btn_output .= "<a href=\"$url\">";
                        $btn_output .= '<img src="'.esc_url(UWP_SOCIAL_PLUGIN_URL . 'assets/images/btn_google_signin_dark_normal_web.png').'" alt="Sign in with Google" class="w-auto">';
                        $btn_output .= "</a>";
                        $btn_output .= "</li>";
	                } else {
                        $btn_output .= '<li class="uwp_social_login_icon">';
                        $btn_output .= "<a href=\"$url\">";
                        $btn_output .= '<i class="'.$social_icon_class.'  fa-fw fa-lg" title="'.$provider_name.'"></i>';
                        $btn_output .= "</a>";
                        $btn_output .= "</li>";
	                }
                }

            }
        }

        echo apply_filters("uwp_social_login_button_html",$btn_output,strtolower($provider_id),$url);
    }

	$output = ob_get_clean();
    if($output){
	    echo $is_bootstrap ? '<div class="bsui"><hr /><div class="text-muted h5 mt-n2 mb-2">'.$title.'</div>' : '<ul class="uwp_social_login_ul">';
        echo $output;
	    echo $is_bootstrap ? '</div>' : '</ul><style>.uwp_social_login_ul {
  margin: 0;
  list-style-type: none;
  padding: 0;
  overflow: hidden;
  clear: both; }
  .uwp_social_login_ul li {
    padding: 0;
    margin: 0 10px 10px 0;
    border: none !important;
    float: left; }
    .uwp_social_login_ul li a{
    background: #ccc;
    padding: 4px 2px;
    }
    .uwp_social_login_ul li a, .uwp_social_login_ul li a:hover, .uwp_social_login_ul li img {
      box-shadow: none !important;
      -moz-box-shadow: none !important; }</style>';
    }
}

function uwp_social_build_provider_config( $provider )
{

    if(!class_exists('Hybridauth')){
        require_once UWP_SOCIAL_PATH . '/vendor/hybridauth/autoload.php';
    }

    $config = array();
    $config["current_page"] = Hybridauth\HttpClient\Util::getCurrentUrl(true);
    $config["base_url"] = home_url();
    $config["callback"] = uwp_get_callback_url($provider);
    $config["providers"] = array();
    $config["providers"][$provider] = array();
    $config["providers"][$provider]["enabled"] = true;
    $config["providers"][$provider]["keys"] = array( 'id' => null, 'key' => null, 'secret' => null );

    $provider_key = strtolower($provider);
    // provider application id ?
    if( uwp_get_option('uwp_social_'.$provider_key.'_id', false) )
    {
        $config["providers"][$provider]["keys"]["id"] = uwp_get_option('uwp_social_'.$provider_key.'_id');
    }

    // provider application key ?
    if( uwp_get_option('uwp_social_'.$provider_key.'_key', false) )
    {
        $config["providers"][$provider]["keys"]["key"] = uwp_get_option('uwp_social_'.$provider_key.'_key');
    }

    // provider application secret ?
    if( uwp_get_option('uwp_social_'.$provider_key.'_secret', false) )
    {
        $config["providers"][$provider]["keys"]["secret"] = uwp_get_option('uwp_social_'.$provider_key.'_secret');
    }

    // set custom config for facebook
    if( $provider_key == "facebook" )
    {
        $config["providers"][$provider]["trustForwarded"] = true;
        $config["providers"][$provider]["display"] = "page";
	    $config["providers"][$provider]["scope"] = "email";

    }

    if( $provider_key == "linkedin" )
    {
        $config["providers"][$provider]["scope"] = "r_liteprofile r_emailaddress";
    }

    // set custom config for google
    if( $provider_key == "google" )
    {
        // set the default google scope
        $config["providers"][$provider]["scope"] = "https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email";
    }

    if( $provider_key == "instagram" )
    {
        // set the default google scope
        $config["providers"][$provider]["scope"] = "user_profile";
    }

	if( $provider_key == "twitter" )
	{
		$config["providers"][$provider]["includeEmail"] = true;
	}

    $provider_scope = isset( $config["providers"][$provider]["scope"] ) ? $config["providers"][$provider]["scope"] : '' ;

    // allow to overwrite scopes
    $config["providers"][$provider]["scope"] = apply_filters( 'uwp_social_provider_config_scope', $provider_scope, $provider );

    // allow to overwrite hybridauth config for the selected provider
    $config["providers"][$provider] = apply_filters( 'uwp_social_provider_config', $config["providers"][$provider], $provider );

    return $config;
}

function uwp_get_available_social_providers() {
    $providers =  array(
        "facebook" => array(
            "provider_id"       => "facebook",
            "provider_name"     => "Facebook",
            "require_client_id" => true,
        ),
        "twitter" => array(
            "provider_id"       => "twitter",
            "provider_name"     => "Twitter",
            "require_client_id" => false,
        ),
        "linkedin" => array(
            "provider_id"       => "linkedin",
            "provider_name"     => "LinkedIn",
            "require_client_id" => false,
        ),
        "instagram" => array(
            "provider_id"       => "Instagram",
            "provider_name"     => "Instagram",
            "require_client_id" => true,
        ),
        "yahoo" => array(
            "provider_id"       => "yahoo",
            "provider_name"     => "Yahoo!",
            "require_client_id" => true,
        ),
        "wordpress" => array(
            "provider_id"       => "wordpress",
            "provider_name"     => "WordPress",
            "require_client_id" => true,
        ),
        "vkontakte" => array(
            "provider_id"       => "Vkontakte",
            "provider_name"     => "ВКонтакте",
            "require_client_id" => true,
        ),
        "google" => array(
	        "provider_id"       => "google",
	        "provider_name"     => "Google",
	        "require_client_id" => true,
        ),
    );

    $providers = apply_filters('uwp_get_available_social_providers', $providers);
    return $providers;
}

function uwp_social_get_provider_name_by_id( $provider_id)
{
    $providers = uwp_get_available_social_providers();

    foreach( $providers as $provider ) {
        if ( $provider['provider_id'] == $provider_id ) {
            return $provider['provider_name'];
        }
    }

    return $provider_id;
}

function uwp_social_destroy_session_data() {
    if ( isset( $_SESSION['uwp_social'] ) ) {
        unset( $_SESSION['uwp_social']);
    }

    if ( isset( $_SESSION['HA::STORE'] ) ) {
        unset( $_SESSION['HA::STORE']);
    }

    if ( isset( $_SESSION['HA::CONFIG'] ) ) {
        unset( $_SESSION['HA::CONFIG']);
    }

}

function uwp_get_social_login_redirect_url(){
    $redirect_page_id = uwp_get_option('login_redirect_to', -1);
    if(isset( $_REQUEST['redirect_to'] )) {
        $redirect_to = esc_url($_REQUEST['redirect_to']);
    } elseif (isset($redirect_page_id) && (int)$redirect_page_id > 0) {
        $redirect_to = esc_url(get_permalink($redirect_page_id));
    } elseif(isset($redirect_page_id) && (int)$redirect_page_id == -1 && wp_get_referer()) {
        $redirect_to = esc_url(wp_get_referer());
    } else {
        $redirect_to = home_url('/');
    }
    return apply_filters('uwp_login_redirect', $redirect_to);
}

function uwp_get_callback_url($provider){
    $callback = '';
    $provider = strtolower($provider);

    if(isset($provider) & !empty($provider)){
        if('yahoo' == $provider){
            $callback = home_url() . '/uwphauth/yaho';
        } else {
            $callback = home_url() . '/uwphauth/' . $provider;
        }
    }

    return $callback;
}
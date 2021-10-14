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

function uwp_social_login_buttons($type = '', $echo = true) {
	ob_start();
	echo do_shortcode('[uwp_social type="'.$type.'"]');
	$output = ob_get_clean();

	if($echo){
		echo $output;
	} else {
		return $output;
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
    );

    $providers = apply_filters('uwp_get_available_social_providers', $providers);

	$providers['google'] = array(
		"provider_id"       => "google",
		"provider_name"     => "Google",
		"require_client_id" => true,
	);

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

function uwp_get_social_login_redirect_url($data = array(), $user = false){
	$data = array();
	$uwp_forms = new UsersWP_Forms();
	$redirect_to = $uwp_forms->get_login_redirect_url( $data, $user );
    return $redirect_to;
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
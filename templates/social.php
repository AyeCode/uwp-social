<?php
$providers = uwp_get_available_social_providers();

if(isset($providers) && count($providers) > 0) {

	$redirect_to = ! empty( $args['redirect_to'] ) ? esc_url( $args['redirect_to'] ) : '';
	$type = ! empty( $args['type'] ) ? esc_url( $args['type'] ) : '';

	echo '<ul class="uwp_social_login_ul">';

	foreach ( $providers as $array_key => $provider ) {
		$btn_output    = '';
		$provider_id   = isset( $provider["provider_id"] ) ? $provider["provider_id"] : '';
		$provider_name = isset( $provider["provider_name"] ) ? $provider["provider_name"] : '';
		$url           = '';
		$enable        = uwp_get_option( 'enable_uwp_social_' . $array_key, "0" );
		if ( $enable == "1" ) {
			if ( isset( $provider["require_client_id"] ) && $provider["require_client_id"] ) {
				$key = uwp_get_option( 'uwp_social_' . $array_key . '_id', "" );
			} else {
				$key = uwp_get_option( 'uwp_social_' . $array_key . '_key', "" );
			}
			$secret      = uwp_get_option( 'uwp_social_' . $array_key . '_secret', "" );
			$url         = home_url() . "/?action=uwp_social_authenticate&provider=" . $provider_id. '&type=' . $type;

			if ( isset( $redirect_to ) && ! empty( $redirect_to ) ) {
				$url .= '&redirect_to=' . $redirect_to;
			}

			//General |Facebook |Twitter |LinkedIn |Instagram |Yahoo |WordPress |VKontakte
			$icons = array(
				'facebook'  => 'fab fa-facebook-f',
				'twitter'   => 'fab fa-twitter',
				'instagram' => 'fab fa-instagram',
				'linkedin'  => 'fab fa-linkedin-in',
				'wordpress' => 'fab fa-wordpress-simple',
				'vkontakte' => 'fab fa-vk',

			);

			$social_name_class = strtolower( $provider_id );
			$social_icon_class = isset( $icons[ $social_name_class ] ) ? $icons[ $social_name_class ] : "fab fa-" . $social_name_class;

			if ( ! empty( $key ) && ! empty( $secret ) ) {
				if ( 'google' == strtolower( $provider_id ) ) {
					$btn_output .= "<br/>";
					$btn_output .= '<li class="uwp_social_login_icon uwp_social_login_icon_'.$social_name_class.'">';
					$btn_output .= "<a href=\"$url\">";
					$btn_output .= '<img src="' . esc_url( UWP_SOCIAL_PLUGIN_URL . 'assets/images/btn_google_signin_dark_normal_web.png' ) . '" alt="Sign in with Google" class="w-auto">';
					$btn_output .= "</a>";
					$btn_output .= "</li>";
				} else {
					$btn_output .= '<li class="uwp_social_login_icon uwp_social_login_icon_'.$social_name_class.'">';
					$btn_output .= "<a href=\"$url\">";
					$btn_output .= '<i class="' . $social_icon_class . '  fa-fw fa-lg" title="' . $provider_name . '"></i>';
					$btn_output .= "</a>";
					$btn_output .= "</li>";
				}

			}
		}

		echo apply_filters( "uwp_social_login_button_html", $btn_output, strtolower( $provider_id ), $url );
	}

	echo '</ul><style>.uwp_social_login_ul {
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
<?php
$providers = uwp_get_available_social_providers();

if(isset($providers) && count($providers) > 0) {

	$title = uwp_get_option('label_for_social_login',__('Login via Social','uwp-social'));
	$title = apply_filters('uwp_social_login_buttons_label', $title);

	if ( $title ) {
		echo '<div class="bsui"><hr /><div class="text-muted h5 mt-n2 mb-2">' . esc_attr($title) . '</div>';
	}

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
			$url         = home_url() . "/?action=uwp_social_authenticate&provider=" . $provider_id;
			$redirect_to = uwp_get_social_login_redirect_url();
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
					$btn_output .= "<a href=\"$url\" class='uwp_social_login_icon_google'>";
					$btn_output .= '<img src="' . esc_url( UWP_SOCIAL_PLUGIN_URL . 'assets/images/btn_google_signin_dark_normal_web.png' ) . '" alt="Sign in with Google" class="w-auto">';
					$btn_output .= "</a>";

				} else {
					$btn_output .= aui()->button( array(
						'href'        => $url,
						'class'       => 'ml-1 mb-1 border-0 btn  btn-' . $social_name_class . ' btn-sm btn-circle',
						'content'     => '<i class="' . $social_icon_class . '  fa-fw fa-lg"></i>',
						'data-toggle' => 'tooltip',
						'title'       => $provider_name,
					) );
				}
			}
		}

		echo apply_filters( "uwp_social_login_button_html", $btn_output, strtolower( $provider_id ), $url );
	}

	echo '</div>';
}
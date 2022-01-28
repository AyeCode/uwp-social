<?php
add_filter( 'query_vars', 'uwp_social_query_vars' );
function uwp_social_query_vars( $vars ) {
	$vars[] = "hauth_done";
	$vars[] = "provider";

	return $vars;
}

add_action( 'init', 'uwp_social_authenticate_init' );
function uwp_social_authenticate_init() {

	// check for uwp actions
	$action = isset( $_REQUEST['action'] ) ? sanitize_text_field( $_REQUEST['action'] ) : null;

	add_rewrite_rule( '^uwphauth/([^/]*)/?', 'index.php?hauth_done=1&provider=$matches[1]', 'top' );

	if ( ! in_array( $action, array(
		"uwp_social_authenticate",
		"uwp_social_account_linking",
		"uwp_social_authenticated"
	) ) ) {
		return;
	}

	do_action( "uwp_social_authenticate_start" );

	if ( $action == 'uwp_social_authenticate' ) {
		uwp_social_authenticate_process();
	}

	uwp_social_authenticated_process();
}

function uwp_social_authenticate_process() {

	if ( is_user_logged_in() ) {
		wp_redirect( home_url() );
		exit();
	}

	if ( isset( $_REQUEST['provider'] ) && ! empty( $_REQUEST['provider'] ) ) {
		$provider = sanitize_text_field( $_REQUEST['provider'] );
	} else {
		echo uwp_social_render_error_page( array('message' => __( 'Invalid social login provider.', 'uwp-social' )) );
		die();
	}

	if ( ! isset( $_REQUEST["uwp_redirect_to_provider"] ) ) {
		do_action( 'uwp_clear_user_php_session' );

		uwp_social_provider_redirect_loading_screen();
	}

	$config  = uwp_social_build_provider_config( $provider );
	$adapter = '';

	if ( ! class_exists( 'Hybridauth' ) ) {
		require_once UWP_SOCIAL_PATH . '/vendor/hybridauth/autoload.php';
	}

	try {
		// create an instance oh hybridauth with the config
		$hybridauth = new Hybridauth\Hybridauth( $config );

		uwp_set_provider_config_in_session_storage( $provider, $config );
		$adapter = $hybridauth->authenticate( $provider );
	} // if hybridauth fails to authenticate the user, then we display an error message
	catch ( Exception $e ) {
		echo uwp_social_render_error( $e, $config, $provider, $adapter );
		die();
	}

	$redirect_to = isset( $_REQUEST['redirect_to'] ) ? esc_url($_REQUEST['redirect_to']) : home_url();

	$authenticated_url = add_query_arg(
		array(
			'action'   => 'uwp_social_authenticated',
			'provider' => $provider
		),
		trailingslashit( home_url() )
	);

	// display a loading screen
	$args = array(
		'provider'          => $provider,
		'authenticated_url' => $authenticated_url,
		'redirect_to'       => $redirect_to,
	);

	uwp_social_provider_loading_screen( $args );
}

function uwp_social_authenticated_process() {

	$redirect_to = uwp_get_social_login_redirect_url( $_REQUEST );

	if ( isset( $_REQUEST['provider'] ) && ! empty( $_REQUEST['provider'] ) ) {
		$provider = sanitize_text_field( $_REQUEST['provider'] );
	} else {
		echo uwp_social_render_error_page( array('message' => __( 'Invalid social login provider.', 'uwp-social' )) );
		die();
	}

	// authentication mode
	$auth_mode = uwp_social_process_login_get_auth_mode();

	$is_new_user             = false; // is it a new or returning user
	$user_id                 = ''; // wp user id
	$adapter                 = ''; // hybriauth adapter for the selected provider
	$hybridauth_user_profile = ''; // hybriauth user profile

	// provider is enabled?
	$provider_key = strtolower( $provider );
	$enable       = uwp_get_option( 'enable_uwp_social_' . $provider_key, "0" );

	if ( $enable != "1" ) {
		$e = new Exception( __( "Unknown or disabled provider.", 'uwp-social' ), 3 );
		echo uwp_social_render_error( $e );
		die();
	}


	if ( $auth_mode == 'login' ) {

		$data = uwp_social_get_user_data( $provider, $redirect_to );

		// returns user data after he authenticate via hybridauth
		if ( is_string( $data ) ) {
			echo $data;
			die();
		} else {
			list
				(
				$user_id,
				$adapter,
				$hybridauth_user_profile,
				$requested_user_login,
				$requested_user_email,
				$wordpress_user_id
				)
				= $data;
		}


		// if no associated user were found in uwp social profiles, create new WordPress user
		if ( ! $wordpress_user_id ) {

			if ( is_string( $hybridauth_user_profile ) ) {
				// its an error. so echo the template content.
				echo $hybridauth_user_profile;
				die();
			} else {
				// some providers don't give us the correct email address. Ex: yahoo.
				// so we need to ask the user to give us the real email address.
				// We also need to ask the user to pick the username. If not we auto generate it.
				// Only Google and Facebook provides verified email address.
				// For other networks we may need email verification to make sure they are using the correct address.

				if ( ! $requested_user_email ) {
					echo uwp_social_render_notice( array('message' => __( "Couldn't fetch the email address of a user. Please try again or use alternate login method!", 'uwp-social' )) );
					die();
				}

				$user_id     = uwp_social_create_wp_user( $provider, $hybridauth_user_profile, $requested_user_login, $requested_user_email );
				$is_new_user = true;
			}
		} else {
			$user_id     = $wordpress_user_id;
			$is_new_user = false;
		}
	}

	if ( is_string( $user_id ) ) {
		echo $user_id;
		die();
	}

	$wp_user = get_userdata( $user_id );

	// store user profile
	uwp_social_update_user_data( $is_new_user, $user_id, $provider, $adapter, $hybridauth_user_profile, $wp_user );

	if ( empty( $_REQUEST['type'] ) ) {
		$redirect_to = uwp_get_social_login_redirect_url( $_REQUEST, $wp_user );
	}

	// finally create a wordpress session for the user
	uwp_social_authenticate_user( $user_id, $provider, $redirect_to, $adapter, $hybridauth_user_profile, $wp_user );
}

/**
 * Returns the selected auth mode from _REQUEST, default to login
 */
function uwp_social_process_login_get_auth_mode() {
	$auth_mode = isset( $_REQUEST["mode"] ) ? sanitize_text_field( $_REQUEST["mode"] ) : 'login';

	return apply_filters( 'uwp_social_process_login_get_auth_mode', $auth_mode );
}

function uwp_social_get_provider_adapter( $provider_id ) {

	$adapter = null;
	$config  = uwp_get_provider_config_from_session_storage( $provider_id );

	if ( ! $config ) {
		echo uwp_social_render_notice( array('message' => __( "Invalid session data. Please try again.", 'uwp-social' )) );
		die();
	}

	if ( ! class_exists( 'Hybridauth' ) ) {
		require_once UWP_SOCIAL_PATH . '/vendor/hybridauth/autoload.php';
	}

	try {
		$hybridauth = new Hybridauth\Hybridauth( $config );
		$adapter    = $hybridauth->getAdapter( $provider_id );
	} catch ( Exception $e ) {
		echo uwp_social_render_error( $e, $config, $provider_id, $adapter );
		die();
	}

	return $adapter;
}

function uwp_social_get_user_data( $provider, $redirect_to ) {
	do_action( "uwp_social_get_user_data_start", $provider, $redirect_to );

	$user_id              = null;
	$requested_user_login = '';
	$requested_user_email = '';
	$wordpress_user_id    = 0;

	if ( isset( $_SESSION['uwp_social']['uwp::userprofile'] ) && $_SESSION['uwp_social']['uwp::userprofile'] ) {
		$hybridauth_user_profile = json_decode( $_SESSION['uwp_social']['uwp::userprofile'] );
	} else {
		$hybridauth_user_profile                    = uwp_request_user_social_profile( $provider );
		$_SESSION['uwp_social']['uwp::userprofile'] = json_encode( $hybridauth_user_profile );
	}

	// must be error template
	if ( is_string( $hybridauth_user_profile ) ) {
		echo $hybridauth_user_profile;
		die();
	}

	$adapter = uwp_social_get_provider_adapter( $provider );

	$hybridauth_user_email          = isset( $hybridauth_user_profile->email ) ? sanitize_email( $hybridauth_user_profile->email ) : '';
	$hybridauth_user_email_verified = isset( $hybridauth_user_profile->emailVerified ) ? sanitize_email( $hybridauth_user_profile->emailVerified ) : '';

	// check if user already exist in uwp social profiles
	if ( ! empty( $hybridauth_user_profile->identifier ) ) {
		$user_id = (int) uwp_get_social_profile( $provider, $hybridauth_user_profile->identifier );
	} else {
		$user_id = 0;
	}

	// if not found in uwp social profiles, then check his verified email
	if ( ! $user_id && ! empty( $hybridauth_user_email_verified ) ) {
		// check if the verified email exist in wp_users
		$user_id = (int) uwp_email_exists( $hybridauth_user_email_verified );

		// the user exists in Wordpress
		$wordpress_user_id = $user_id;

		// check if the verified email exist in uwp social profiles
		if ( ! $user_id ) {
			$user_id = (int) uwp_get_social_profile_by_email_verified( $hybridauth_user_email_verified );
		}
	}

	if ( ! $user_id ) {
		// Accept new registrations?
		if ( ! get_option( 'users_can_register' ) ) {
			return uwp_social_render_notice( array('message' => __( "Registration is now closed.", 'uwp-social' )) );
		}

		if ( ! is_email( $hybridauth_user_email_verified ) ) {
			$incorrect_email_error_msg = apply_filters( 'uwp_incorrect_email_error_msg', __( 'The email address isn&#8217;t correct.', 'uwp-social' ) );

			return uwp_social_render_notice( array('message' => $incorrect_email_error_msg) );
		}

		$linking_enabled = apply_filters( 'uwp_social_linking_enabled', true, $provider );
		$require_email   = apply_filters( 'uwp_social_require_email', true, $provider );
		$change_username = apply_filters( 'uwp_social_change_username', true, $provider );

		if ( $linking_enabled ) {
			do {
				list
					(
					$shall_pass,
					$user_id,
					$requested_user_login,
					$requested_user_email
					)
					= uwp_social_new_users_gateway( $provider, $redirect_to, $hybridauth_user_profile );
			} while ( ! $shall_pass );
			$wordpress_user_id = $user_id;
		} elseif ( ( $require_email && empty( $hybridauth_user_email ) ) || $change_username ) {
			do {
				list
					(
					$shall_pass,
					$user_id,
					$requested_user_login,
					$requested_user_email
					)
					= uwp_social_new_users_gateway( $provider, $redirect_to, $hybridauth_user_profile );
			} while ( ! $shall_pass );
		}
	} else {
		$wordpress_user_id = $user_id;
	}

	return array(
		$user_id,
		$adapter,
		$hybridauth_user_profile,
		$requested_user_login,
		$requested_user_email,
		$wordpress_user_id
	);
}

function uwp_social_create_wp_user( $provider, $hybridauth_user_profile, $requested_user_login, $requested_user_email ) {
	do_action( "uwp_social_create_wp_user_start", $provider, $hybridauth_user_profile, $requested_user_login, $requested_user_email );

	$user_login = '';
	$user_email = '';

	// if coming from "complete registration form"
	if ( $requested_user_login ) {
		$user_login = $requested_user_login;
	}

	if ( $requested_user_email ) {
		$user_email = $requested_user_email;
	}

	if ( ! $user_login ) {
		// attempt to generate user_login from hybridauth user profile display name
		$user_login = $hybridauth_user_profile->displayName;

		// sanitize user login
		$user_login = sanitize_user( $user_login, true );

		// remove spaces and dots
		$user_login = trim( str_replace( array( ' ', '.' ), '_', $user_login ) );
		$user_login = trim( str_replace( '__', '_', $user_login ) );

		// if user profile display name is not provided
		if ( empty( $user_login ) ) {
			$user_login = sanitize_user( current( explode( '@', $user_email ) ), true );
		}

		// user name should be unique
		if ( username_exists( $user_login ) ) {
			$i = 1;

			do {
				$user_login_tmp = $user_login . "_" . ( $i ++ );
			} while ( username_exists( $user_login_tmp ) );

			$user_login = $user_login_tmp;
		}
	}


	if ( ! $user_email ) {
		$user_email   = $hybridauth_user_profile->email;
		$email_domain = 'example.com';
		$email_domain = apply_filters( 'uwp_social_login_email_domain', $email_domain );

		// generate an email if none
		if ( ! isset ( $user_email ) OR ! is_email( $user_email ) ) {
			$user_email = strtolower( $provider . "_user_" . $user_login ) . '@' . $email_domain;
		}

		// email should be unique
		if ( uwp_email_exists( $user_email ) ) {
			do {
				$user_email = md5( uniqid( wp_rand( 10000, 99000 ) ) ) . '@' . $email_domain;
			} while ( uwp_email_exists( $user_email ) );
		}
	}

	$display_name = $hybridauth_user_profile->displayName;

	if ( empty( $display_name ) ) {
		$display_name = $hybridauth_user_profile->firstName;
	}

	if ( empty( $display_name ) ) {
		$display_name = strtolower( $provider ) . "_user";
	}


	// user name should be unique
	if ( username_exists( $user_login ) ) {
		$i = 1;

		do {
			$user_login_tmp = $user_login . "_" . ( $i ++ );
		} while ( username_exists( $user_login_tmp ) );

		$user_login = $user_login_tmp;
	}

	$userdata = array(
		'user_login' => sanitize_user( $user_login ),
		'user_email' => sanitize_email( $user_email ),

		'display_name' => sanitize_text_field( $display_name ),

		'first_name'  => sanitize_text_field( $hybridauth_user_profile->firstName ),
		'last_name'   => sanitize_text_field( $hybridauth_user_profile->lastName ),
		'user_url'    => "'" . esc_url( $hybridauth_user_profile->profileURL ) . "'",
		'description' => sanitize_textarea_field( $hybridauth_user_profile->description ),

		'user_pass' => wp_generate_password()
	);

	$role             = uwp_get_option( 'uwp_social_default_role' );
	$userdata['role'] = isset( $role ) && ! empty( $role ) ? $role : get_option( 'default_role' );

	$userdata = apply_filters( 'uwp_social_alter_wp_insert_user_data', $userdata, $provider, $hybridauth_user_profile );

	do_action( 'uwp_social_before_wp_insert_user', $userdata, $provider, $hybridauth_user_profile );

	$user_id = wp_insert_user( $userdata );

	$user_id = apply_filters( 'uwp_social_delegate_wp_insert_user', $user_id, $provider, $hybridauth_user_profile );

	// do not continue without user_id
	if ( ! $user_id || ! is_integer( $user_id ) ) {
		if ( is_wp_error( $user_id ) ) {
			return uwp_social_render_notice( array('message' => __( "An error occurred while creating a new user: ", 'uwp-social' ) . $user_id->get_error_message()) );
		}

		return uwp_social_render_notice( array('message' => __( "An error occurred while creating a new user!", 'uwp-social' )) );
	}

	// wp_insert_user may fail on first and last name meta, expliciting setting to correct.
	update_user_meta( $user_id, 'first_name', apply_filters( 'uwp_social_pre_user_first_name', $userdata['first_name'] ) );
	update_user_meta( $user_id, 'last_name', apply_filters( 'uwp_social_pre_user_last_name', $userdata['last_name'] ) );
	update_user_meta( $user_id, 'is_uwp_social_login', 1 );
	update_user_meta( $user_id, 'uwp_social_login_provider', $provider );

	if ( 1 == uwp_get_option( 'uwp_social_require_moderation' ) ) {
		update_user_meta( $user_id, 'uwp_mod', '1' );
		do_action( 'uwp_require_admin_review', $user_id, array() );
	}

	wp_new_user_notification( $user_id, null, 'both' );

	// Send notifications
	do_action( 'uwp_social_after_wp_insert_user', $user_id, $provider, $hybridauth_user_profile );

	// returns the user created user id
	return $user_id;
}


/**
 *  Grab the user profile from social network
 */
function uwp_request_user_social_profile( $provider ) {
	$adapter                 = null;
	$config                  = null;
	$hybridauth_user_profile = null;

	try {

		// get idp adapter
		$adapter = uwp_social_get_provider_adapter( $provider );

		$config = uwp_get_provider_config_from_session_storage( $provider );
		// if user authenticated successfully with social network
		if ( $adapter->isConnected() ) {
			// grab user profile via hybridauth api
			$hybridauth_user_profile = $adapter->getUserProfile();
		} // if user not connected to provider (ie: session lost, url forged)
		else {
			return array(
				uwp_social_render_notice( array('message' => sprintf( __( "Sorry, we couldn't connect you with <b>%s</b>. <a href=\"%s\">Please try again</a>.", 'uwp-social' ), $provider, site_url( 'wp-login.php', 'login_post' ) )) )
			);
		}
	} // if things doesn't go as expected, we display the appropriate error message
	catch ( Exception $e ) {
		return array(
			uwp_social_render_error( $e, $config, $provider, $adapter )
		);
	}

	return $hybridauth_user_profile;
}


function uwp_social_update_user_data( $is_new_user, $user_id, $provider, $adapter, $hybridauth_user_profile, $wp_user ) {
	do_action( "uwp_social_update_user_data_start", $is_new_user, $user_id, $provider, $adapter, $hybridauth_user_profile, $wp_user );

	uwp_social_store_user_profile( $user_id, $provider, $hybridauth_user_profile );

}

/**
 * Authenticate a user within wordpress
 *
 */
function uwp_social_authenticate_user( $user_id, $provider, $redirect_to, $adapter, $hybridauth_user_profile, $wp_user ) {
	do_action( "uwp_social_authenticate_user_start", $user_id, $provider, $redirect_to, $adapter, $hybridauth_user_profile, $wp_user );

	// update some fields in usermeta for the current user
	update_user_meta( $user_id, 'uwp_current_provider', sanitize_text_field( $provider ) );

	$allow_profile_image = apply_filters( 'uwp_social_enable_avatar_update', true );
	$avatar_thumb        = uwp_get_usermeta( $user_id, 'avatar_thumb', '' );

	if ( $hybridauth_user_profile->photoURL && $allow_profile_image && empty( $avatar_thumb ) ) {
		uwp_update_usermeta( $user_id, 'avatar_thumb', esc_url( $hybridauth_user_profile->photoURL ) );
		update_user_meta( $user_id, 'uwp_social_user_image', esc_url( $hybridauth_user_profile->photoURL ) );
	}

	if ( 1 == uwp_get_option( 'uwp_social_require_moderation' ) && 1 == get_user_meta( $user_id, 'uwp_mod', true ) ) {
		echo uwp_social_render_notice( array('message' => __( "Your account is under moderation. We will email you once its approved.", 'uwp-social' )) );
		die();
	}

	// This action runs just before logging the user in (before creating a WP cookie)
	do_action( "uwp_social_authenticate_user_set_auth_cookie", $user_id, $provider, $hybridauth_user_profile );

	// Set WP auth cookie
	wp_set_auth_cookie( $user_id, true );

	if ( $wp_user ) {
		$user_login = isset( $wp_user->user_login ) ? $wp_user->user_login : '';
		do_action( 'wp_login', $user_login, $wp_user );
	}

	do_action( "uwp_social_authenticate_before_wp_safe_redirect", $user_id, $provider, $hybridauth_user_profile, $redirect_to );

	do_action( 'uwp_social_authenticate_session' );

	wp_safe_redirect( $redirect_to );

	exit();
}

function uwp_social_new_users_gateway( $provider, $redirect_to, $hybridauth_user_profile ) {
	do_action( "uwp_social_new_users_gateway_start", $provider, $redirect_to, $hybridauth_user_profile );

	$assets_base_url = UWP_SOCIAL_PLUGIN_URL . 'assets/images/16/';

	remove_action( 'register_form', 'uwp_render_auth_widget_in_wp_register_form' );

	$hybridauth_user_email          = isset( $hybridauth_user_profile->email ) ? sanitize_email( $hybridauth_user_profile->email ) : '';
	$hybridauth_user_email_verified = isset( $hybridauth_user_profile->emailVerified ) ? sanitize_email( $hybridauth_user_profile->emailVerified ) : '';
	$hybridauth_user_login          = isset( $hybridauth_user_profile->displayName ) ? sanitize_user( $hybridauth_user_profile->displayName, true ) : '';
	$hybridauth_user_avatar         = isset( $hybridauth_user_profile->photoURL ) ? esc_url( $hybridauth_user_profile->photoURL ) : '';

	$hybridauth_user_login = trim( str_replace( array( ' ', '.' ), '_', $hybridauth_user_login ) );
	$hybridauth_user_login = trim( str_replace( '__', '_', $hybridauth_user_login ) );

	$requested_user_email = isset( $_REQUEST["user_email"] ) ? sanitize_email( $_REQUEST["user_email"] ) : $hybridauth_user_email;
	$requested_user_login = isset( $_REQUEST["user_login"] ) ? sanitize_user( $_REQUEST["user_login"] ) : $hybridauth_user_login;

	$requested_user_email = apply_filters( 'uwp_new_users_gateway_alter_requested_email', $requested_user_email );
	$requested_user_login = apply_filters( 'uwp_new_users_gateway_alter_requested_login', $requested_user_login );

	$linking_data = array();

	$user_id    = 0;
	$shall_pass = false;

	$account_linking        = false;
	$account_linking_errors = array();

	$profile_completion        = false;
	$profile_completion_errors = array();

	$linking_enabled = apply_filters( 'uwp_social_linking_enabled', true, $provider );
	$require_email   = apply_filters( 'uwp_social_require_email', false, $provider );
	$change_username = apply_filters( 'uwp_social_change_username', false, $provider );

	if ( isset( $_REQUEST["account_linking"] ) ) {
		if ( ! $linking_enabled ) {
			return uwp_social_render_notice( array('message' => __( "Linking is not enabled. Please check the social login add on settings.", 'uwp-social' )) );
		}

		$account_linking = true;

		$username = isset( $_REQUEST["user_login"] ) ? sanitize_user( $_REQUEST["user_login"] ) : '';
		$password = isset( $_REQUEST["user_password"] ) ? trim( $_REQUEST["user_password"] ) : '';

		// http://codex.wordpress.org/Function_Reference/wp_authenticate
		$user = wp_authenticate( $username, $password );

		// WP_Error object?
		if ( is_wp_error( $user ) ) {
			// we give no useful hint.
			$account_linking_errors[] =
				sprintf(
					__(
						'<strong>ERROR</strong>: Invalid username or incorrect password. <a href="%s">Lost your password</a>?',
						'uwp-social'
					),
					wp_lostpassword_url( home_url() )
				);
		} elseif ( is_a( $user, 'WP_User' ) ) {
			$user_id = $user->ID;

			$shall_pass = true;
		}
	} elseif ( isset( $_REQUEST["profile_completion"] ) ) {
		// Profile Completion enabled?
		if ( ! $require_email && ! $change_username ) {
			$shall_pass = true;
		} // otherwise we request email &or username &or extra fields
		else {
			$profile_completion = true;

			// validate usermail
			if ( $require_email ) {
				if ( empty( $requested_user_email ) ) {
					$profile_completion_errors[] = __( '<strong>ERROR</strong>: Please type your e-mail address.', 'uwp-social' );
				}

				if ( ! is_email( $requested_user_email ) ) {
					$profile_completion_errors[] = __( '<strong>ERROR</strong>: Please enter a valid email address.', 'uwp-social' );
				}

				if ( uwp_email_exists( $requested_user_email ) ) {
					$profile_completion_errors[] = __( '<strong>ERROR</strong>: Sorry, that email address is already used!', 'uwp-social' );
				}
			}

			// validate username
			if ( $change_username ) {
				$illegal_names = array( 'www', 'web', 'root', 'admin', 'main', 'invite', 'administrator' );

				$illegal_names = apply_filters( 'uwp_new_users_gateway_alter_illegal_names', $illegal_names );

				if ( ! validate_username( $requested_user_login ) || in_array( $requested_user_login, $illegal_names ) == true ) {
					$profile_completion_errors[] = __( '<strong>ERROR</strong>: That username is not allowed.', 'uwp-social' );
				}

				$username_length = uwp_get_option( 'register_username_length' );
				$username_length = ! empty( $username_length ) ? (int) $username_length : 4;

				if ( strlen( $requested_user_login ) < $username_length ) {
					$profile_completion_errors[] = sprintf( __( '<strong>ERROR</strong>: Username must be at least %s characters.', 'uwp-social' ), $username_length );
				}

				if ( preg_match( '/^[0-9]*$/', $requested_user_login ) ) {
					$profile_completion_errors[] = __( '<strong>ERROR</strong>: Sorry, usernames must have letters too!', 'uwp-social' );
				}

				if ( username_exists( $requested_user_login ) ) {
					$profile_completion_errors[] = __( '<strong>ERROR</strong>: Sorry, that username already exists!', 'uwp-social' );
				}
			}

			$profile_completion_errors = apply_filters( 'uwp_new_users_gateway_alter_profile_completion_errors', $profile_completion_errors );

			if ( ! $profile_completion_errors ) {
				$shall_pass = true;
			}
		}
	}


	if ( ! $require_email && ! $change_username ) {
		$shall_pass = true;
	} else {
		$linking_data['provider']                  = $provider;
		$linking_data['account_linking']           = $account_linking;
		$linking_data['profile_completion']        = $profile_completion;
		$linking_data['require_email']             = $require_email;
		$linking_data['change_username']           = $change_username;
		$linking_data['linking_enabled']           = $linking_enabled;
		$linking_data['redirect_to']               = $redirect_to;
		$linking_data['account_linking_errors']    = $account_linking_errors;
		$linking_data['profile_completion_errors'] = $profile_completion_errors;
		$linking_data['requested_user_email']      = $requested_user_email;
		$linking_data['requested_user_login']      = $requested_user_login;
		$linking_data['hybridauth_user_profile']   = $hybridauth_user_profile;
		$linking_data['hybridauth_user_avatar']    = $hybridauth_user_avatar;
		$linking_data['assets_base_url']           = $assets_base_url;
	}

	uwp_social_account_linking( $shall_pass, $linking_data );

	return array( $shall_pass, $user_id, $requested_user_login, $requested_user_email );
}

function uwp_social_account_linking( $shall_pass, $args ) {
	if ( $shall_pass == false && ! empty( $args ) ) {
		uwp_get_template( "linking.php", $args, '', UWP_SOCIAL_PATH . 'templates' );
		die();
	}
}

function uwp_social_provider_redirect_loading_screen( $args = array() ) {
	ob_start();
	uwp_get_template( "loading_screen.php", $args, '', UWP_SOCIAL_PATH . 'templates' );
	echo ob_get_clean();
	die();
}

function uwp_social_provider_loading_screen( $args ) {
	ob_start();
	uwp_get_template( "provider_loading_screen.php", $args, '', UWP_SOCIAL_PATH . 'templates' );
	echo ob_get_clean();
	die();
}

function uwp_social_render_error_page( $args ) {
	ob_start();
	uwp_get_template( "render_error_page.php", $args, '', UWP_SOCIAL_PATH . 'templates' );
	return ob_get_clean();
}

function uwp_social_render_notice_page( $args ) {
	ob_start();
	uwp_get_template( "render_notice_page.php", $args, '', UWP_SOCIAL_PATH . 'templates' );
	return ob_get_clean();
}

function uwp_social_render_notice( $args ) {
	do_action( "uwp_social_render_notice_page_before", $args );

	return uwp_social_render_notice_page( $args );
}

function uwp_social_render_error( $e, $config = null, $provider = null, $adapter = null ) {

	do_action( "uwp_social_render_error", $e, $config, $provider, $adapter );

	$message = __( "Unspecified error!", 'uwp-social' );

	if ( is_string( $e ) ) {
		$message  = $e;
		$apierror = $e;
	} else {
		$apierror = substr( $e->getMessage(), 0, 145 );
	}

	$provider_name = uwp_social_get_provider_name_by_id( $provider );
	$notes         = $e->getMessage();

	switch ( $e->getCode() ) {
		case 0 :
			! empty( $apierror ) ? $message = $apierror : $message = __( "Unspecified error.", 'uwp-social' );
			break;
		case 1 :
			$message = __( "UsersWP Social Login is not properly configured.", 'uwp-social' );
			break;
		case 2 :
			$message = sprintf( __( "UsersWP Social Login is not properly configured.<br /> <b>%s</b> need to be properly configured.", 'uwp-social' ), $provider_name );
			break;
		case 3 :
			$message = __( "Unknown or disabled provider.", 'uwp-social' );
			break;
		case 4 :
			$message = sprintf( __( "UsersWP Social Login is not properly configured.<br /> <b>%s</b> requires your application credentials.", 'uwp-social' ), $provider_name );
			$notes   = sprintf( __( "<b>What does this error mean ?</b><br />Most likely, you didn't setup the correct application credentials for this provider. These credentials are required in order for <b>%s</b> users to access your website and for UsersWP Social Login to work.", 'uwp-social' ), $provider_name ) . __( '<br />Instructions for use can be found in the <a href="#" target="_blank">User Manual</a>.', 'uwp-social' );
			break;
		case 5 :
			$message = sprintf( __( "Authentication failed. Either you have cancelled the authentication or <b>%s</b> refused the connection.", 'uwp-social' ), $provider_name );
			break;
		case 6 :
			$message = sprintf( __( "Request failed. Either you have cancelled the authentication or <b>%s</b> refused the connection.", 'uwp-social' ), $provider_name );
			break;
		case 7 :
			$message = __( "You're not connected to the provider.", 'uwp-social' );
			break;
		case 8 :
			$message = __( "Provider does not support this feature.", 'uwp-social' );
			break;
	}

	$message = apply_filters('uwp_social_error_messages', $message, $e, $config, $provider, $adapter);

	if ( ! empty( $provider ) ) {
		$config     = uwp_social_build_provider_config( $provider );
		$hybridauth = new Hybridauth\Hybridauth( $config );
		$hybridauth->disconnectAllAdapters();
	}

	return uwp_social_render_error_page( array('message' => $message) );
}

add_filter( 'uwp_social_require_email', 'uwp_social_require_email_value', 10, 2 );
function uwp_social_require_email_value( $value, $provider ) {
	$provider = strtolower( $provider );
	$enabled  = uwp_get_option( 'uwp_social_' . $provider . '_pick_email', "0" );
	if ( $enabled == '1' ) {
		$value = true;
	}

	return $value;
}

add_filter( 'uwp_social_change_username', 'uwp_social_change_username_value', 10, 2 );
function uwp_social_change_username_value( $value, $provider ) {
	$provider = strtolower( $provider );
	$enabled  = uwp_get_option( 'uwp_social_' . $provider . '_pick_username', "0" );
	if ( $enabled == '1' ) {
		$value = true;
	}

	return $value;
}

function uwp_set_provider_config_in_session_storage( $provider, $config ) {
	$provider = strtolower( $provider );
	if ( ! session_id() ) {
		session_start();
	}
	$_SESSION['uwp_social']['uwp:provider']                   = $provider;
	$_SESSION['uwp_social'][ 'uwp:' . $provider . ':config' ] = (array) $config;
}

function uwp_get_provider_config_from_session_storage( $provider ) {
	$provider = strtolower( $provider );
	if ( ! session_id() ) {
		session_start();
	}
	if ( isset( $_SESSION['uwp_social'][ 'uwp:' . $provider . ':config' ] ) ) {
		return (array) $_SESSION['uwp_social'][ 'uwp:' . $provider . ':config' ];
	}
}

add_action( 'template_redirect', 'uwp_social_check_auth_done' );
function uwp_social_check_auth_done() {

	if ( 1 == get_query_var( 'hauth_done' ) && ! empty( get_query_var( 'provider' ) ) ) {
		$provider_id = get_query_var( 'provider' );
		if ( ! session_id() ) {
			session_start();
		}
		if ( ! $provider_id && isset( $_SESSION['uwp_social']['uwp:provider'] ) ) {
			$provider_id = $_SESSION['uwp_social']['uwp:provider'];
		}

		if ( "yaho" == strtolower( $provider_id ) ) {
			$provider_id = "Yahoo";
		}

		$config = uwp_get_provider_config_from_session_storage( $provider_id );

		if ( ! $config ) {
			return;
		}

		$callback_url = isset( $config['current_page'] ) ? $config['current_page'] : home_url();

		if ( ! class_exists( 'Hybridauth' ) ) {
			require_once UWP_SOCIAL_PATH . '/vendor/hybridauth/autoload.php';
		}

		$adapter = null;

		try {
			$hybridauth = new Hybridauth\Hybridauth( $config );

			$adapter = $hybridauth->authenticate( $provider_id );

			Hybridauth\HttpClient\Util::redirect( $callback_url );
		} catch ( Exception $e ) {
			echo uwp_social_render_error( $e, $config, $provider_id, $adapter );
			die();
		}
	}

}
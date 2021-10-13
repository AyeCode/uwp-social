<?php
function uwp_social_account_linking( $shall_pass, $linking_data ) {
	if ( $shall_pass == false && ! empty( $linking_data ) ) {
		$provider_name             = uwp_social_get_provider_name_by_id( $linking_data["provider"] );
		$provider                  = sanitize_text_field( $linking_data["provider"] );
		$account_linking_errors    = $linking_data['account_linking_errors'];
		$profile_completion_errors = $linking_data['profile_completion_errors'];
		$linking_enabled           = $linking_data['linking_enabled'];
		$account_linking           = $linking_data['account_linking'];
		$profile_completion        = $linking_data['profile_completion'];
		$require_email             = $linking_data['require_email'];
		$change_username           = $linking_data['change_username'];
		$requested_user_email      = $linking_data['requested_user_email'];
		$requested_user_login      = $linking_data['requested_user_login'];
		$hybridauth_user_profile   = $linking_data['hybridauth_user_profile'];
		$hybridauth_user_avatar    = $linking_data['hybridauth_user_avatar'];
		$redirect_to               = $linking_data['redirect_to'];
		?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo get_bloginfo( 'name' ); ?></title>
            <style type="text/css">
                html, body {
                    height: 100%;
                    margin: 0;
                    padding: 0;
                }

                body {
                    background: none repeat scroll 0 0 #f1f1f1;
                    font-size: 14px;
                    color: #444;
                    font-family: "Open Sans", sans-serif;
                }

                hr {
                    border-color: #eeeeee;
                    border-style: none none solid;
                    border-width: 0 0 1px;
                    margin: 2px 0 0;
                }

                h4 {
                    font-size: 14px;
                    margin-bottom: 10px;
                }

                #login {
                    width: 616px;
                    margin: auto;
                    padding: 114px 0 0;
                }

                #login-panel {
                    background: none repeat scroll 0 0 #fff;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.13);
                    margin: 2em auto;
                    box-sizing: border-box;
                    display: inline-block;
                    padding: 70px 0 15px;
                    position: relative;
                    text-align: center;
                    width: 100%;
                }

                #avatar {
                    margin-left: -76px;
                    top: -80px;
                    left: 50%;
                    padding: 4px;
                    position: absolute;
                }

                #avatar img {
                    background: none repeat scroll 0 0 #fff;
                    border: 3px solid #f1f1f1;
                    border-radius: 75px !important;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.13);
                    height: 145px;
                    width: 145px;
                }

                #welcome {
                    height: 55px;
                    margin: 15px 20px 35px;
                }

                .button-primary {
                    background-color: #21759b;
                    background-image: linear-gradient(to bottom, #2a95c5, #21759b);
                    border-color: #21759b #21759b #1e6a8d;
                    border-radius: 3px;
                    border-style: solid;
                    border-width: 1px;
                    box-shadow: 0 1px 0 rgba(120, 200, 230, 0.5) inset;
                    box-sizing: border-box;
                    color: #fff;
                    cursor: pointer;
                    display: inline-block;
                    float: none;
                    font-size: 12px;
                    height: 36px;
                    line-height: 23px;
                    margin: 0;
                    padding: 0 10px 1px;
                    text-decoration: none;
                    text-shadow: 0 1px 0 rgba(0, 0, 0, 0.1);
                    white-space: nowrap;
                }

                .button-primary.focus, .button-primary:hover {
                    background: #1e8cbe;
                    border-color: #0074a2;
                    -webkit-box-shadow: inset 0 1px 0 rgba(120, 200, 230, .6);
                    box-shadow: inset 0 1px 0 rgba(120, 200, 230, .6);
                    color: #fff
                }

                input[type="text"] {
                    border: 1px solid #e5e5e5;
                    box-shadow: 1px 1px 2px rgba(200, 200, 200, 0.2) inset;
                    color: #555;
                    font-size: 17px;
                    height: 30px;
                    line-height: 1;
                    margin-bottom: 16px;
                    margin-right: 6px;
                    margin-top: 2px;
                    outline: 0 none;
                    padding: 3px;
                    width: 100%;
                }

                input[type="text"]:focus {
                    border-color: #5b9dd9;
                    -webkit-box-shadow: 0 0 2px rgba(30, 140, 190, .8);
                    box-shadow: 0 0 2px rgba(30, 140, 190, .8)
                }

                input[type="submit"] {
                    float: right;
                }

                label {
                    color: #777;
                    font-size: 14px;
                    cursor: pointer;
                    vertical-align: middle;
                    text-align: left;
                }

                table {
                    width: 355px;
                    margin-left: auto;
                    margin-right: auto;
                }

                #mapping-options {
                    width: 555px;
                }

                #mapping-authenticate {
                    display: none;
                }

                #mapping-complete-info {
                    display: none;
                }

                .error {
                    display: none;
                    background-color: #fff;
                    border-left: 4px solid #dd3d36;
                    box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.1);
                    margin: 0 21px;
                    padding: 12px;
                    text-align: left;
                }

                .back-to-options {
                    float: left;
                    margin: 7px 0px;
                }

                .back-to-home {
                    font-size: 12px;
                    margin-top: -18px;
                }

                .back-to-home a {
                    color: #999;
                    text-decoration: none;
                }

                <?php
                    if( $linking_enabled )
                    {
                        ?>
                #login {
                    width: 400px;
                }

                #welcome, #mapping-options, #errors-account-linking, #mapping-complete-info {
                    display: none;
                }

                #errors-profile-completion, #mapping-complete-info {
                    display: block;
                }

                <?php
			}
			elseif( $account_linking )
			{
				?>
                #login {
                    width: 400px;
                }

                #welcome, #mapping-options, #errors-profile-completion, #mapping-complete-info {
                    display: none;
                }

                #errors-account-linking, #mapping-authenticate {
                    display: block;
                }

                <?php
			}
			elseif( $profile_completion )
			{
				?>
                #login {
                    width: 400px;
                }

                #welcome, #mapping-options, #errors-account-linking, #mapping-complete-info {
                    display: none;
                }

                #errors-profile-completion, #mapping-complete-info {
                    display: block;
                }

                <?php
			}
		?>
            </style>
            <script>
                // good old time
                function toggleEl(el, display) {
                    if (el = document.getElementById(el)) {
                        el.style.display = display;
                    }
                }

                function toggleWidth(el, width) {
                    if (el = document.getElementById(el)) {
                        el.style.width = width;
                    }
                }

                function display_mapping_options() {
                    toggleWidth('login', '616px');

                    toggleEl('welcome', 'block');
                    toggleEl('mapping-options', 'block');

                    toggleEl('errors-profile-completion', 'none');
                    toggleEl('mapping-authenticate', 'none');

                    toggleEl('errors-account-linking', 'none');
                    toggleEl('mapping-complete-info', 'none');
                }

                function display_mapping_authenticate() {
                    toggleWidth('login', '400px');

                    toggleEl('welcome', 'none');
                    toggleEl('mapping-options', 'none');

                    toggleEl('errors-account-linking', 'block');
                    toggleEl('mapping-authenticate', 'block');

                    toggleEl('errors-profile-completion', 'none');
                    toggleEl('mapping-complete-info', 'none');
                }

                function display_mapping_complete_info() {
                    toggleWidth('login', '400px');

                    toggleEl('welcome', 'none');
                    toggleEl('mapping-options', 'none');

                    toggleEl('errors-account-linking', 'none');
                    toggleEl('mapping-authenticate', 'none');

                    toggleEl('errors-profile-completion', 'block');
                    toggleEl('mapping-complete-info', 'block');
                }
            </script>
        </head>
        <body>
        <div id="login">
            <div id="login-panel">
                <div id="avatar">
                    <img src="<?php echo esc_url( $hybridauth_user_avatar ); ?>">
                </div>

                <div id="welcome">
                    <b><?php printf( __( "Hi %s", 'uwp-social' ), htmlentities( $hybridauth_user_profile->displayName ) ); ?></b>
                    <p><?php printf( __( "You're now signed in with your %s account but you are still one step away of getting into our website", 'uwp-social' ), $provider ); ?>
                        .</p>

                    <hr/>
                </div>

                <table id="mapping-options" border="0">
                    <tr>
						<?php if ( $linking_enabled ): ?>
                            <td valign="top" width="50%" style="text-align:center;">
                                <h4><?php echo __( "Already have an account", 'uwp-social' ); ?>?</h4>
                                <p style="font-size: 12px;"><?php printf( __( "Link your existing account on our website to your %s ID.", 'uwp-social' ), $provider ); ?></p>
                            </td>
						<?php endif; ?>

                        <td valign="top" width="50%" style="text-align:center;">
                            <h4><?php echo __( "New to our website", 'uwp-social' ); ?>?</h4>
                            <p style="font-size: 12px;"><?php printf( __( "Create a new account and it will be associated with your %s ID.", 'uwp-social' ), $provider ); ?></p>
                        </td>
                    </tr>

                    <tr>
						<?php if ( $linking_enabled ): ?>
                            <td valign="top" width="50%" style="text-align:center;">
                                <input type="button" value="<?php echo __( "Link my account", 'uwp-social' ); ?>"
                                       class="button-primary" onclick="display_mapping_authenticate();">
                            </td>
						<?php endif; ?>

                        <td valign="top" width="50%" style="text-align:center;">
							<?php if ( $require_email != 1 && $change_username != 1 ): ?>
                                <input type="button" value="<?php echo __( "Create a new account", 'uwp-social' ); ?>"
                                       class="button-primary" onclick="document.getElementById('info-form').submit();">
							<?php else : ?>
                                <input type="button" value="<?php echo __( "Create a new account", 'uwp-social' ); ?>"
                                       class="button-primary" onclick="display_mapping_complete_info();">
							<?php endif; ?>
                        </td>
                    </tr>
                </table>

				<?php
				if ( $account_linking_errors ) {
					echo '<div id="errors-account-linking" class="error">';

					foreach ( $account_linking_errors as $error ) {
						?><p><?php echo $error; ?></p><?php
					}

					echo '</div>';
				}

				if ( $profile_completion_errors ) {
					echo '<div id="errors-profile-completion" class="error">';

					foreach ( $profile_completion_errors as $error ) {
						?><p><?php echo $error; ?></p><?php
					}

					echo '</div>';
				}
				?>

                <form method="post" action="<?php echo site_url( 'wp-login.php', 'login_post' ); ?>" id="link-form">
                    <table id="mapping-authenticate" border="0">
                        <tr>
                            <td valign="top" width="50%" style="text-align:center;">
                                <h4><?php echo __( "Already have an account", 'uwp-social' ); ?>?</h4>

                                <p><?php printf( __( "Please enter your username and password of your existing account on our website. Once verified, it will linked to your % ID", 'uwp-social' ), ucfirst( $provider ) ); ?>
                                    .</p>
                            </td>
                        </tr>
                        <tr>
                            <td valign="bottom" width="50%" style="text-align:left;">
                                <label>
									<?php echo __( "Username", 'uwp-social' ); ?>
                                    <br/>
                                    <input type="text" name="user_login" class="input" value="" size="25"
                                           placeholder=""/>
                                </label>

                                <label>
									<?php echo __( "Password", 'uwp-social' ); ?>
                                    <br/>
                                    <input type="text" name="user_password" class="input" value="" size="25"
                                           placeholder=""/>
                                </label>

                                <input type="submit" value="<?php echo __( "Continue", 'uwp-social' ); ?>"
                                       class="button-primary">

                                <a href="javascript:void(0);" onclick="display_mapping_options();"
                                   class="back-to-options"><?php echo __( "Back", 'uwp-social' ); ?></a>
                            </td>
                        </tr>
                    </table>

                    <input type="hidden" id="redirect_to" name="redirect_to"
                           value="<?php echo esc_url( $redirect_to ); ?>">
                    <input type="hidden" id="provider" name="provider" value="<?php echo $provider ?>">
                    <input type="hidden" id="action" name="action" value="uwp_social_account_linking">
                    <input type="hidden" id="account_linking" name="account_linking" value="1">
                </form>

                <form method="post" action="<?php echo site_url( 'wp-login.php', 'login_post' ); ?>" id="info-form">
                    <table id="mapping-complete-info" border="0">
                        <tr>
                            <td valign="top" width="50%" style="text-align:center;">
								<?php if ( $linking_enabled ): ?>
                                    <h4><?php echo __( "New to our website", 'uwp-social' ); ?>?</h4>
								<?php endif; ?>

                                <p><?php printf( __( "Please fill in your information in the form below. Once completed, you will be able to automatically sign into our website through your %s ID", 'uwp-social' ), $provider_name ); ?>
                                    .</p>
                            </td>
                        </tr>
                        <tr>
                            <td valign="bottom" width="50%" style="text-align:left;">
								<?php if ( $change_username == 1 ): ?>
                                    <label>
										<?php echo __( "Username", 'uwp-social' ); ?>
                                        <br/>
                                        <input type="text" name="user_login" class="input"
                                               value="<?php echo esc_attr( $requested_user_login ); ?>" size="25"
                                               placeholder=""/>
                                    </label>
								<?php endif; ?>

								<?php if ( $require_email == 1 ): ?>
                                    <label>
										<?php echo __( "E-mail", 'uwp-social' ); ?>
                                        <br/>
                                        <input type="text" name="user_email" class="input"
                                               value="<?php echo esc_attr( $requested_user_email ); ?>" size="25"
                                               placeholder=""/>
                                    </label>
								<?php endif; ?>

                                <input type="submit" value="<?php echo __( "Continue", 'uwp-social' ); ?>"
                                       class="button-primary">

								<?php if ( $linking_enabled ): ?>
                                    <a href="javascript:void(0);" onclick="display_mapping_options();"
                                       class="back-to-options"><?php echo __( "Back", 'uwp-social' ); ?></a>
								<?php endif; ?>
                            </td>
                        </tr>
                    </table>

                    <input type="hidden" id="redirect_to" name="redirect_to"
                           value="<?php echo esc_url( $redirect_to ); ?>">
                    <input type="hidden" id="provider" name="provider" value="<?php echo $provider ?>">
                    <input type="hidden" id="action" name="action" value="uwp_social_account_linking">
                    <input type="hidden" id="profile_completion" name="profile_completion" value="1">
                </form>
            </div>

            <p class="back-to-home">
                <a href="<?php echo esc_url( home_url() ); ?>">&#8592; <?php printf( __( "Back to %s", 'uwp-social' ), get_bloginfo( 'name' ) ); ?></a>
            </p>
        </div>

        </body>
        </html>
		<?php
		die();
	}
}
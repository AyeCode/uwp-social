<?php
function uwp_social_render_error( $e, $config = null, $provider = null, $adapter = null )
{

    do_action( "uwp_social_render_error", $e, $config, $provider, $adapter );
    
    $message  = __("Unspecified error!", 'uwp-social');

    if (is_string($e)) {
        $message = $e;
        $apierror = $e;
    } else {
        $apierror = substr( $e->getMessage(), 0, 145 );
    }
    
    $provider_name = uwp_social_get_provider_name_by_id($provider);
    $notes = $e->getMessage();

    switch( $e->getCode() )
    {
        case 0 : !empty($apierror) ? $message = $apierror : $message = __("Unspecified error.", 'uwp-social'); break;
        case 1 : $message = __("UsersWP Social Login is not properly configured.", 'uwp-social'); break;
        case 2 : $message = sprintf( __("UsersWP Social Login is not properly configured.<br /> <b>%s</b> need to be properly configured.", 'uwp-social'), $provider_name ); break;
        case 3 : $message = __("Unknown or disabled provider.", 'uwp-social'); break;
        case 4 : $message = sprintf( __("UsersWP Social Login is not properly configured.<br /> <b>%s</b> requires your application credentials.", 'uwp-social'), $provider_name );
                 $notes   = sprintf( __("<b>What does this error mean ?</b><br />Most likely, you didn't setup the correct application credentials for this provider. These credentials are required in order for <b>%s</b> users to access your website and for UsersWP Social Login to work.", 'uwp-social'), $provider_name ) . __('<br />Instructions for use can be found in the <a href="#" target="_blank">User Manual</a>.', 'uwp-social');
                 break;
        case 5 : $message = sprintf( __("Authentication failed. Either you have cancelled the authentication or <b>%s</b> refused the connection.", 'uwp-social'), $provider_name ); break;
        case 6 : $message = sprintf( __("Request failed. Either you have cancelled the authentication or <b>%s</b> refused the connection.", 'uwp-social'), $provider_name ); break;
        case 7 : $message = __("You're not connected to the provider.", 'uwp-social'); break;
        case 8 : $message = __("Provider does not support this feature.", 'uwp-social'); break;
    }

    if( !empty($provider) )
    {
	    $config = uwp_social_build_provider_config($provider);
	    $hybridauth = new Hybridauth\Hybridauth( $config );
	    $hybridauth->disconnectAllAdapters();
    }

    return uwp_social_render_error_page( $message, $notes );
}

function uwp_social_render_error_page( $message, $notes = null )
{
    ob_start();
    $assets_base_url = UWP_SOCIAL_PLUGIN_URL . 'assets/images/';
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta name="robots" content="NOINDEX, NOFOLLOW">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?php bloginfo('name'); ?> - <?php _e("Oops! We ran into an issue", 'uwp-social') ?>.</title>
        <style type="text/css">
            body {
                background: #f1f1f1;
            }
            h4 {
                color: #666;
                font: 20px "Open Sans", sans-serif;
                margin: 0;
                padding: 0;
                padding-bottom: 7px;
            }
            p {
                font-size: 14px;
                margin: 15px 0;
                line-height: 25px;
                padding: 10px;
                text-align:left;
            }
            a {
                color: #21759B;
                text-decoration: none;
            }
            a:hover {
                color: #D54E21;
            }
            #error-page {
                background: #fff;
                color: #444;
                font-family: "Open Sans", sans-serif;
                margin: 2em auto;
                padding: 1em 2em;
                max-width: 700px;
                -webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.13);
                box-shadow: 0 1px 3px rgba(0,0,0,0.13);
                margin-top: 50px;
            }
            #error-page pre {
                max-width: 680px;
                overflow: scroll;
                padding: 5px;
                background: none repeat scroll 0 0 #F5F5F5;
                border-radius:3px;
                font-family: Consolas, Monaco, monospace;
            }
            .error-message {
                line-height: 26px;
                background-color: #f2f2f2;
                border: 1px solid #ccc;
                padding: 10px;
                text-align:center;
                box-shadow: 0 1px 3px rgba(0,0,0,0.13);
                margin-top:25px;
            }
            .error-hint{
                margin:0;
            }
            #debuginfo {
                display:none;
                text-align: center;
                margin: 0;
                padding: 0;
                padding-top: 10px;
                margin-top: 10px;
                border-top: 1px solid #d2d2d2;
            }
        </style>
    </head>
    <body>
    <div id="error-page">
        <table width="100%" border="0">
            <tr>
                <td align="center"><img src="<?php echo esc_url($assets_base_url); ?>alert.png" /></td>
            </tr>

            <tr>
                <td align="center"><h4><?php _e("Oops! We ran into an issue", 'uwp-social') ?>.</h4></td>
            </tr>

            <tr>
                <td>
                    <div class="error-message">
                        <?php echo $message ; ?>
                    </div>

                    <?php
                    // any hint or extra note?
                    if( $notes )
                    {
                        ?>
                        <p class="error-hint"><?php _e( $notes, 'uwp-social'); ?></p>
                        <?php
                    }
                    ?>
                </td>
            </tr>

            <tr>
                <td>
                    <p style="padding: 0;">
                        <a href="<?php echo home_url(); ?>" style="float:left">&xlarr; <?php _e("Back to home", 'uwp-social') ?></a>
                    </p>
                </td>
            </tr>
        </table>
    </div>
    </body>
    </html>
    <?php
    $output = ob_get_contents();
    ob_end_clean();
    return trim($output);
}

function uwp_social_render_notice( $message )
{
    do_action( "uwp_social_render_notice_page_before", $message );

    return uwp_social_render_notice_page( $message );
}

function uwp_social_render_notice_page( $message )
{
    ob_start();
    $assets_base_url = UWP_SOCIAL_PLUGIN_URL . 'assets/images/';
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta name="robots" content="NOINDEX, NOFOLLOW">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title><?php bloginfo('name'); ?></title>
        <style type="text/css">
            body {
                background: #f1f1f1;
            }
            h4 {
                color: #666;
                font: 20px "Open Sans", sans-serif;
                margin: 0;
                padding: 0;
                padding-bottom: 12px;
            }
            a {
                color: #21759B;
                text-decoration: none;
            }
            a:hover {
                color: #D54E21;
            }
            p {
                font-size: 14px;
                line-height: 1.5;
                margin: 25px 0 20px;
            }
            #notice-page {
                background: #fff;
                color: #444;
                font-family: "Open Sans", sans-serif;
                margin: 2em auto;
                padding: 1em 2em;
                max-width: 700px;
                -webkit-box-shadow: 0 1px 3px rgba(0,0,0,0.13);
                box-shadow: 0 1px 3px rgba(0,0,0,0.13);
                margin-top: 50px;
            }
            #notice-page code {
                font-family: Consolas, Monaco, monospace;
            }
            .notice-message {
                line-height: 26px;
                background-color: #f2f2f2;
                border: 1px solid #ccc;
                padding: 10px;
                text-align:center;
                box-shadow: 0 1px 3px rgba(0,0,0,0.13);
                margin-top:25px;
            }
        </style>
    </head>
    <body>
    <div id="notice-page">
        <table width="100%" border="0">
            <tr>
                <td align="center"><img src="<?php echo esc_url($assets_base_url); ?>alert.png" /></td>
            </tr>
            <tr>
                <td align="center">
                    <div class="notice-message">
                        <?php echo nl2br( $message ); ?>
                    </div>
                </td>
            </tr>

            <tr>
                <td>
                    <p style="padding: 0;">
                        <a href="<?php echo home_url(); ?>" style="float:left">&xlarr; <?php _e("Back to home", 'uwp-social') ?></a>
                    </p>
                </td>
            </tr>
        </table>
    </div>
    </body>
    </html>
    <?php
    $output = ob_get_contents();
    ob_end_clean();
    return trim($output);
}
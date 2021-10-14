<?php
$provider          = isset( $args['provider'] ) ? $args['provider'] : '';
$redirect_to       = isset( $args['redirect_to'] ) ? $args['redirect_to'] : '';
$authenticated_url = isset( $args['authenticated_url'] ) ? $args['authenticated_url'] : '';
?>
<!DOCTYPE html>
<html>
    <head>
        <meta name="robots" content="NOINDEX, NOFOLLOW">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php _e( "Redirecting...", 'uwp-social' ) ?> - <?php bloginfo( 'name' ); ?></title>
        <style type="text/css">
            html {
                background: #f1f1f1;
            }

            body {
                background: #fff;
                color: #444;
                font-family: "Open Sans", sans-serif;
                margin: 2em auto;
                padding: 1em 2em;
                max-width: 700px;
                -webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.13);
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.13);
            }

            #loading-screen {
                margin-top: 50px;
            }

            #loading-screen div {
                line-height: 20px;
                background-color: #f2f2f2;
                border: 1px solid #ccc;
                padding: 10px;
                text-align: center;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.13);
                margin-top: 25px;
            }
        </style>
        <script>
            function init() {
                document.loginform.submit();
            }
        </script>
    </head>
    <body id="loading-screen" onload="init();">
        <table width="100%" border="0">
            <tr>
                <td align="center"><img src="<?php echo esc_url( UWP_SOCIAL_PLUGIN_URL . 'assets/images/loading.gif' ); ?>"/>
                </td>
            </tr>
            <tr>
                <td align="center">
                    <div>
                        <?php _e( "Processing, please wait...", 'uwp-social' ); ?>
                    </div>
                </td>
            </tr>
        </table>

        <form name="loginform" method="post" action="<?php echo esc_attr( $authenticated_url ); ?>">
            <input type="hidden" id="redirect_to" name="redirect_to" value="<?php echo esc_url( $redirect_to ); ?>">
            <input type="hidden" id="provider" name="provider" value="<?php echo esc_attr( $provider ); ?>">
            <input type="hidden" id="action" name="action" value="uwp_social_authenticated">
        </form>
    </body>
</html>
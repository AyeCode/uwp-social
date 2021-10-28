<!DOCTYPE html>
<html>
<head>
    <meta name="robots" content="NOINDEX, NOFOLLOW">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php bloginfo( 'name' ); ?> - <?php _e( "Oops! We ran into an issue", 'uwp-social' ) ?>.</title>
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
            text-align: left;
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
            -webkit-box-shadow: 0 1px 3px rgba(0, 0, 0, 0.13);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.13);
            margin-top: 50px;
        }

        #error-page pre {
            max-width: 680px;
            overflow: scroll;
            padding: 5px;
            background: none repeat scroll 0 0 #F5F5F5;
            border-radius: 3px;
            font-family: Consolas, Monaco, monospace;
        }

        .error-message {
            line-height: 26px;
            background-color: #f2f2f2;
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.13);
            margin-top: 25px;
        }

        .error-hint {
            margin: 0;
        }

        #debuginfo {
            display: none;
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
            <td align="center"><img src="<?php echo esc_url( UWP_SOCIAL_PLUGIN_URL . 'assets/images/alert.png' ); ?>"/>
            </td>
        </tr>

        <tr>
            <td align="center"><h4><?php _e( "Oops! We ran into an issue", 'uwp-social' ) ?>.</h4></td>
        </tr>

		<?php
		if ( isset( $args['message'] ) && ! empty( $args['message'] ) ) {
			?>
            <tr>
                <td>
                    <div class="error-message">
						<?php echo esc_html( $args['message'] ); ?>
                    </div>
                </td>
            </tr>
			<?php
		}
		?>

        <tr>
            <td>
                <p style="padding: 0;">
                    <a href="<?php echo home_url(); ?>"
                       style="float:left">&xlarr; <?php _e( "Back to home", 'uwp-social' ) ?></a>
                </p>
            </td>
        </tr>
    </table>
</div>
</body>
</html>
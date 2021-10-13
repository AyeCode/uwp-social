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
            setTimeout(function () {
                window.location.replace(window.location.href + "&uwp_redirect_to_provider=true");
            }, 250);
        }
	</script>
</head>
<body id="loading-screen" onload="init();">
<table width="100%" border="0">
	<tr>
		<td align="center"><img src="<?php echo esc_url( UWP_SOCIAL_PLUGIN_URL . 'assets/images/loading.gif' ); ?>"/></td>
	</tr>
	<tr>
		<td align="center">
			<div>
				<?php _e( "Processing, please wait...", 'uwp-social' ); ?>
			</div>
		</td>
	</tr>
</table>
</body>
</html>
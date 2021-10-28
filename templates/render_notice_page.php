<!DOCTYPE html>
<html>
<head>
	<meta name="robots" content="NOINDEX, NOFOLLOW">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
			<td align="center"><img src="<?php echo esc_url(UWP_SOCIAL_PLUGIN_URL . 'assets/images/alert.png'); ?>" /></td>
		</tr>
		<?php
		if ( isset( $args['message'] ) && ! empty( $args['message'] ) ) {
			?>
            <tr>
                <td align="center">
                    <div class="notice-message">
						<?php echo nl2br( $args['message'] ); ?>
                    </div>
                </td>
            </tr>
			<?php
		}
		?>
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
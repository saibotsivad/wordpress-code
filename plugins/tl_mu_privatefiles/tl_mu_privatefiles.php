<?php
/*
Plugin Name: Multisite Private Files
Plugin URI: http://tobiaslabs.com/
Description: Disallow file access if a user is not logged in.
Version: 0.1
Author: Tobias Davis
Author URI: http://davistobias.com
*/

// This code needs to get put in your htaccess file
/*
# private files
RewriteCond %{HTTP_HOST} sub.domain.com
RewriteRule ^files/(.+) /afilethatdoesnotexist.txt [L]
*/

add_filter('404_template', 'tl_private_filter');
function tl_private_filter()
{
	global $userdata;
	get_currentuserinfo();
	if ( $userdata->user_login && is_multisite() )
	{
		$url = urldecode( $_SERVER['REQUEST_URI'] );
		// strip "/files/" from string
		$url = substr( $url, 7 );
		// set GET, which is used in the file script
		$_GET['file'] = $url;
		// this core code will output the file correctly
		require_once( ABSPATH . 'wp-includes/ms-files.php' );
		//print_r( $_GET );
	}
}

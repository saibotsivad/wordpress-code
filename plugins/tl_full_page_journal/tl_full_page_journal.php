<?php
/*
Plugin Name: Full Page Journal
Plugin URI: http://tobiaslabs.com
Description: A full page text editor, free from distraction.
Author: Tobias Davis
Version: 0.1
Author URI: http://davistobias.com
*/

// WordPress bootstrap
require_once( '../../../wp-load.php' );
require_once( '../../../wp-admin/admin.php' );

// Initialization loads as little as possible.
if ( function_exists( 'is_admin' ) && is_admin() )
{
	echo 'sdfsdfsd';
	$TL_FullPageJournal = new TL_FullPageJournal_Admin;
}

class TL_FullPageJournal_Admin
{

	/**
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function __construct()
	{
		// add actions
		add_action( 'admin_init', array( $this, 'Init' ) );
		add_action( 'admin_menu', array( $this, 'AdminMenu' ) );
	}
	
	/**
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function Init()
	{
		if ( isset( $_GET['page'] ) && $_GET['page'] == 'tl_full_page_journal' ) {
			?>
			<html><head><title>Hey!</title><body>butts</body></html>
			<?php
		}
	}
	
	/**
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function AdminMenu()
	{
		add_menu_page( 'Full Page', 'Full Page', 'edit_posts', 'tl_full_page_journal', array( $this, 'EmptyFunction' ), 'icon_url', 5 );
	}
	
	/**
	 * This doen't do anything, check outside the class
	 *
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function EmptyFunction() { }
	
}

?>
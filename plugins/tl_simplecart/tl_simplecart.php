<?php
/*
Plugin Name: Simple Cart
Plugin URI: http://tobiaslabs.com
Description: Manage a simple shopping cart in your theme, and let the user check out using PayPal or Google Checkout.
Author: Tobias Davis
Version: 0.1
Author URI: http://davistobias.com
*/

// Initialization loads as little as possible.
if ( is_admin() )
{
	require_once( 'include/plugin/admin-core.php' );
	$TL_SimpleCart = new TL_SimpleCart_Admin;
}
else
{
	$TL_SimpleCart = new TL_SimpleCart_Core;
}

// The core visitor-side functionality is held held here
class TL_SimpleCart_Core
{

	// bag of holding
	protected $cart_items = array();
	
	// user id, number of items, total dollar amount
	protected $viewer_info = false;

	/**
	 * @since 0.1
	 * @author Tobias Davis
	*/
	protected function __construct()
	{
		// add actions
		add_action( 'init', array( $this, 'Init' ) );
		
		// set the current viewers info
		global $wpdb;
		if ( isset( $_COOKIE['tlpc_shoppingcart'] ) )
		{
			$value = mysql_real_escape_string( $_COOKIE['tlpc_shoppingcart'] );
			// TODO: grab more information, like the cart dollar total, the user name, and the number of items
			$result = $wpdb->get_results( "SELECT `id`, `items` FROM `{$wpdb->prefix}tlpc_cart` WHERE `id`='{$value}'" );
			if( isset( $result[0]->id ) )
			{
				$this->viewer_info = $result[0];
			}
		}
	}
	
	/**
	 * @since 0.1
	 * @author Tobias Davis
	*/
	protected function Init()
	{
		// these shortcodes will return a template, just put them in your page and they will look okay, style them for more individualization
		add_shortcode( 'TLSC_CartList', array( $this, 'CartList' ) );
		add_shortcode( 'TLSC_CartShort', array( $this, 'CartShort' ) );
	}
	
	/**
	 * @since 0.1
	 * @author Tobias Davis
	*/
	protected function CartList()
	{
		$template = get_option( 'plugin_tl_simplecart_template' );
		//TODO: how do you make sure the text is processed to run the shortcodes within the shortcodes?
		run_the_code_magically( $template['cartlist'] );
	}
	
	/**
	 * @since 0.1
	 * @author Tobias Davis
	*/
	protected function CartShort()
	{
		$template = get_option( 'plugin_tl_simplecart_template' );
		//TODO: how do you make sure the text is processed to run the shortcodes within the shortcodes?
		run_the_code_magically( $template['cartshort'] );
	}
	
	

}

?>
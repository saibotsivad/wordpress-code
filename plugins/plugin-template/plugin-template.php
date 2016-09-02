<?php
/*
Plugin Name: PluginTemplate
Plugin URI: http://tobiaslabs.com
Description: AsimpleTemplateForMakingPlugins
Author: Tobias Davis
Version: 0.1
Author URI: http://davistobias.com
*/

// Initialization loads as little as possible.
if ( is_admin() )
{
	require_once( 'include/plugin/admin-core.php' );
	$TL_PluginTemplate = new TL_PluginTemplate_Admin;
}
else
{
	$TL_PluginTemplate = new TL_PluginTemplate_Core;
}

// The core visitor-side functionality is held held here
class TL_PluginTemplate_Core
{

	// plugin information, for the admin side
	var $plugin_info = array();
	
	/**
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function __construct()
	{
		// set the plugin information, for activation and other options
		$this->plugin_info['plugin_location'] = __FILE__;
		
		// Initialization
		add_action( 'init', array( $this, 'Init' ) );
	}
	
	/**
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function Init()
	{
	}

}
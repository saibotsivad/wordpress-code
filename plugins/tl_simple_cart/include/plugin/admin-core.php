<?php

// security
if ( !is_admin() ) die();

class TL_PluginTemplate_Admin extends TL_PluginTemplate_Core
{

	function __construct()
	{
		// additional security
		if ( get_parent_class($this) != 'TL_BibBlue_Core' ) die();
		// functions named the same need to call the parent class function
		parent::__construct();
		
		// admin actions
		add_action( 'admin_init', array( $this, 'AdminInit' ) );
	
	}
	
	function AdminInit()
	{
	}
	
}

?>
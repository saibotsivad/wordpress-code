<?php

// security
if ( !is_admin() ) die();

class TL_SimpleCart_Admin extends TL_SimpleCart_Core
{

	function __construct()
	{
		// additional security
		if ( get_parent_class($this) != 'TL_SimpleCart_Core' ) die();
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
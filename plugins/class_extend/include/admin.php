<?php

// security
//if ( !is_admin() || !isset($this) || get_parent_class($this) != 'TL_CE_Core' ) die();

class TL_CE_Admin extends TL_CE_Core
{

	function __construct()
	{
		// functions named the same need to call the parent class function
		parent::__construct();
		add_action( 'admin_init', array( $this, 'AdminInit' ) );
	}
	
	function AdminInit()
	{
		add_meta_box(
			'tlsp_metabox',
			'Sermon Details',
			array( $this, 'MetaBox' ),
			'sermon',
			'normal',
			'low'
		);
	}
	
	function MetaBox()
	{	
		echo get_parent_class($this);
	}

}

?>
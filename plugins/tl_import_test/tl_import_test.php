<?php
/* 
Plugin Name: Import Test
Plugin URI: http://www.tobiaslabs.com/
Description: Testing certain AJAX functions of importing.
Version: 0.1
Author: Tobias Labs
Author URI: http://tobiaslabs.com
*/

$TL_ImportTest = new TL_ImportTest;

class TL_ImportTest
{

	/**
	 * @since 0.1
	 * @author Tobias Davis
	*/
	public function __construct()
	{
		add_action( 'admin_init', array( $this, 'AdminInit' ) );
		add_action( 'admin_head', array( $this, 'AdminHead' ) );
		add_action('wp_ajax_tl_importtest_ajax', array( $this, 'AjaxCallback' ) );
	}
	
	/**
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function AdminInit()
	{
		register_importer(
			'TL_ImportTest_Importer',
			'AJAX Import Test',
			'Test some functions of importing, using AJAX callbacks.',
			array( $this, 'Import' )
		);
	}
	
	/**
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function Import()
	{
		if ( current_user_can( 'import' ) )
		{
			?>
			<div class="wrap">
				<div id="icon-tools" class="icon32"></div>
				<h2>Import Test: AJAX Style!</h2>
				<form name="tl_importtest_form" action="admin.php?import=TL_ImportTest_Importer" method="post" id="tl_importtest_form">
					<fieldset>
						<p>Click here to begin!</p>
						<input type="submit" name="tl_importtest_submit" id="tl_importtest_submit" class="button" value="Click here!" />
					</fieldset>
				</form>
				<table class="widefat" id="tl_importtest_results">
					<tbody>
					</tbody>
				</table>
				<p id="tl_importtest_indicator"><br /></p>
			</div>
			<?php
		}
	}
	
	/**
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function AdminHead()
	{
		if ( isset( $_GET['import'] ) && $_GET['import'] == 'TL_ImportTest_Importer' && current_user_can( 'import' ) )
		{
			?>
			<style type="text/css">
			table#tl_importtest_results {
				display: none;
				margin-top: 20px;
			}
			form#tl_importtest_form {
				padding-bottom: 20px;
			}
			p#tl_importtest_indicator {
				display: none;
				text-align: center;
				padding: 20px;
				border: 1px solid rgb(187, 187, 187);
				-webkit-border-radius: 4px;
				-moz-border-radius: 4px;
				border-radius: 4px;
				width: 120px;
				margin: 20px auto;
				background: url('<?php echo plugins_url( 'progress_bar.gif', __FILE__ ); ?>') no-repeat center center;
			}
			</style>
			<script type="text/javascript">
			jQuery(document).ready( function() {
				jQuery('#tl_importtest_submit').click(function(e) {
					jQuery('#tl_importtest_results').show(); // display table
					jQuery('#tl_importtest_form').remove(); // delete entire form (eliminates double clicking)
					jQuery('#tl_importtest_indicator').show(); // display progress indicator
					jQuery.each(['one','two','three'], function(index,value) {
						var data = {
							action: 'tl_importtest_ajax',
							import_step: value,
							import_index: index,
							tl_nonce: '<?php echo wp_create_nonce( 'tl_ImportTest_nonce' ); ?>'
						};
						jQuery.post(ajaxurl, data, function(response) {
							var json = jQuery.parseJSON(response);
							jQuery('#tl_importtest_results > tbody:last').append('<tr><td>'+json.key+'</td><td>'+json.type+'</td><td>'+json.text+'</td></tr>');
							if( json.progress=='final') { // on the last one, remove the indicator
								jQuery('#tl_importtest_indicator').remove();
							}
						});
					});
					e.preventDefault(); // don't reload the page
				});
			});
			</script>
			<?php
		}
	}
	
	/**
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function AjaxCallback()
	{
	
		// security test
		check_ajax_referer('tl_ImportTest_nonce', 'tl_nonce');
		if ( !current_user_can( 'import' ) ) die();
		
		// grab the data from POST
		$import_step = $_POST['import_step'];
		$import_index = (int)$_POST['import_index'];
		
		// do stuff here
		sleep( $import_index * 3 );
		
		// what to return
		$return_array = array(
			'key' => $import_index,
			'type' => $import_step,
			'text' => 'This is some returnable text.'
		);
		// on the last one, let the javascript know that we are done
		$return_array['progress'] = ( $import_index == 2 ? 'final' : 'continue' );
		
		// echo out as json
		echo json_encode( $return_array );
		
		// must die at end
		die();
	
	}

}

?>
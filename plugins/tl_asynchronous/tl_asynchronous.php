<?php
/*
Plugin Name: TL Asynchronous
Plugin URI: http://tobiaslabs.com/
Description: Working out a way to get the SP import process to run uninterrupted
Version: 0.1
Author: Tobias Davis
Author URI: http://davistobias.com/
*/

new TL_Asynchronous;

class TL_Asynchronous
{

	function __construct()
	{
		// admin actions
		add_action( 'admin_init', array( $this, 'AdminInit' ) );
		add_action('wp_ajax_tlsp_import_ajax', array( $this, 'ImportAjax' ) );
		add_action( 'tlsp_importing_sermons', array( $this, 'ImportBackground' ) );
	}
	
	// register importer method
	function AdminInit()
	{
		register_importer(
			'tlsp_importer',
			'Sermon Import',
			'Import sermons from the Sermon Browser plugin to the Sermon Posts plugin.',
			array( $this, 'Importing' )
		);
	}
	
	// the cron job accesses this
	function ImportBackground()
	{
		update_option( 'plugin_tlsp_cron', array('data'=>0) );
		$i = 1;
		while( $i<10 )
		{
			sleep( 3 );
			$data = get_option( 'plugin_tlsp_cron' );
			$data['data'] = $i;
			update_option( 'plugin_tlsp_cron', $data );
			$i++;
		}
	}
	
	// the HTML on the imprt page
	function Importing()
	{
		$data = get_option( 'plugin_tlsp_cron' );
		
		// add a cron job to run immediately, only if it hasn't been run before
		if ( isset( $_POST['tlsp_import_submit'] ) && !$data )
		{
			wp_schedule_single_event( time(), 'tlsp_importing_sermons' );
			// then the javascript
			?>
			<style type="text/css">
			table#tlsp_import_results {
				display: none;
				margin-top: 20px;
			}
			form#tlsp_import_form {
				padding-bottom: 20px;
			}
			p#tlsp_import_indicator {
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
				jQuery('#tlsp_import_form').remove();
				jQuery('#tlsp_import_indicator').show();
				jQuery('#tlsp_import_results').show();
			});
			function updateImportProgress(){
				var data = {
					action: 'tlsp_import_ajax',
					tl_nonce: '<?php echo wp_create_nonce( 'tl_ImportTest_nonce' ); ?>'
				};
				jQuery.post(ajaxurl, data, function(response) {
					var currently = jQuery("tr:last .key").html();
					var json = jQuery.parseJSON(response);
					if(json.key!=currently)
					{
						jQuery('#tlsp_import_results > tbody:last').append('<tr><td class="key">'+json.key+'</td><td>'+json.type+'</td><td>'+json.text+'</td></tr>');
					}
					if( json.progress=='final') { // on the last one, remove the indicator
						jQuery('#tlsp_import_indicator').remove();
						jQuery('#tlsp_import_success').show();
					}else{
						setTimeout(updateImportProgress,500);
					}
				});
			}
			updateImportProgress();
			//window.setInterval(updateImportProgress, 500);
			</script>
			<?php
		}
		?>
		<div class="wrap">
			<div id="icon-tools" class="icon32"></div>
			<h2>Sermon Import</h2>
			<form name="tlsp_import_form" id="tlsp_import_form" action="admin.php?import=tlsp_importer" method="post">
				<p class="submit"><input type="submit" name="tlsp_import_submit" id="tlsp_import_submit" class="button-primary" value="Start Import" /></p>
			</form>
			<table class="widefat" id="tlsp_import_results">
				<tbody>
				</tbody>
			</table>
			<p id="tlsp_import_indicator"><br /></p>
			<div style="display:none;" id="tlsp_import_success">
				<h2>Success!</h2>
				<p>The import process was successfullll, congratulations!</p>
			</div>
		</div>
		<?php
	}
	
	// the AJAX behind the thing
	function ImportAjax()
	{
		check_ajax_referer('tl_ImportTest_nonce', 'tl_nonce');
		if ( !current_user_can( 'import' ) ) die();
		
		// check progress
		$data = get_option( 'plugin_tlsp_cron' );
		if ( $data['data'] == 9 )
		{
			$return = array(
				'key' => $data['data'],
				'type' => 'last',
				'text' => 'The last one!',
				'progress' => 'final'
			);
		}
		else
		{
			$return = array(
				'key' => $data['data'],
				'type' => 'num',
				'text' => time()
			);
		}
		
		// echo out as json
		echo json_encode( $return );
		
		// required to die at the end
		die();
	
	}

}
<?php
/*
Plugin Name: CMS Plugin
Plugin URI: http://tobiaslabs.com/
Description: Manage content with the familiar WordPress admin interface, but display it elsewhere!
Version: 0.1
Author: Tobias
Author URI: http://tobiaslabs.com
*/

$TL_CMSPlugin = new TL_CMSPlugin;

class TL_CMSPlugin
{
	
	var $filepath = 'aaa_test/wp_cms_plugin/img/';
	
	function __construct()
	{
		add_action( 'admin_menu', array( $this, 'AdminMenu' ) );
		register_activation_hook( __FILE__, array( $this, 'Install' ) ); 
	}
	
	function AdminMenu()
	{
		add_menu_page(
			'CMS',
			'CMS',
			'edit_posts',
			'tl_cmsplugin',
			array( $this, 'MainMenu' ),
			'',
			4
		);
	}
	
	function MainMenu()
	{
		global $wpdb;
		
		// save the picture into the database
		if ( !empty( $_POST ) && isset( $_FILES['lolcat'] ) && wp_verify_nonce( $_POST['tl_cmsplugin_addnew'], 'tl_cmsplugin_addnew' ) )
		{
			// get the filetype
			$filetype = explode( "/", $_FILES['lolcat']['type'] );
			$filetype = $filetype[1];
			
			if ( ( $filetype == ( 'jpeg' || 'png' || 'gif' ) && (int)$_FILES['lolcat']['size'] <= 65500 ) )
			{
				// time of insert
				$time = (int)time();
				
				$query = "INSERT INTO `lolcat`
					(
						`file_type`,
						`loltext`,
						`srstext`,
						`unixdate`
					)
					VALUES (
						'".$filetype."',
						'".$wpdb->escape($_POST['loltext'])."',
						'".$wpdb->escape($_POST['srstext'])."',
						{$time}
					)";
				$query = $wpdb->query( $query );
				
				// get the row id and turn into base 36
				$filename = base_convert( $wpdb->insert_id, 10, 36 );
				
				// put the file in it's correct place
				$targetpath = $_SERVER['DOCUMENT_ROOT'] . $this->filepath . $filename . $filetype;
				$result = move_uploaded_file( $_FILES['lolcat']['tmp_name'], $targetpath );
				$addnew_result = ( $result ? "<div id='message' class='updated fade'><p>The image was added!</p></div>" : "<div id='message' class='error fade'><p>Failure at the internets!</p></div>" );
				
				// now make a thumbnail? use the same method above? use "-thumbnail" or something?
				
			}
			
		}
		
		// display the "add new" stuff
		if ( isset( $_GET['tl_cmsplugin_addnewclick'] ) )
		{
			?>
			<div class="wrap">
				<div id="icon-edit" class="icon32"><br /></div>
				<h2>Add new CMS Stuff</h2>
				<form id="tl_cmsplugin_addnew" enctype="multipart/form-data" action="admin.php?page=tl_cmsplugin" method="post">
					<div id="col-container">
						<?php wp_nonce_field('tl_cmsplugin_addnew','tl_cmsplugin_addnew'); ?>
						LOL Text: <input type="text" name="loltext" value="" /><br />
						SRS Text: <input type="text" name="srstext" value="" /><br />
						File: <input type="file" name="lolcat" /><br />
						<input type="submit" value="Submit" />
					</div>
				</form>
			</div>
			<?php
		}
		
		// otherwise, display the list
		else
		{
			$query = "SELECT
				`id` AS `id`,
				`file_type` AS `file_type`,
				`loltext` AS `loltext`,
				`srstext` AS `srstext`,
				`unixdate` AS `unixdate`
				FROM `lolcat`";
			$lulz = $wpdb->get_results( $query );
			?>
			<div class="wrap">
				<div id="icon-upload" class="icon32"><br /></div>
				<h2>CMS Stuff <a class="add-new-h2" href="admin.php?page=tl_cmsplugin&tl_cmsplugin_addnewclick">Add New</a> </h2>
				<?php echo $addnew_result; ?>
				<table class="widefat">
					<thead>
						<tr>
							<th>ID</th>
							<th>Type</th>
							<th>LOL Text</th>
							<th>SRS Text</th>
							<th>Link</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th>ID</th>
							<th>Type</th>
							<th>LOL Text</th>
							<th>SRS Text</th>
							<th>Link</th>
						</tr>
					</tfoot>
					<tbody>
						<?php
						foreach ( $lulz as $lol )
						{
							echo "<tr>";
							echo "<td>". $lol->id ."</td>";
							echo "<td>". $lol->file_type ."</td>";
							echo "<td>". $lol->loltext ."</td>";
							echo "<td>". $lol->srstext ."</td>";
							$id = base_convert( $lol->id, 10, 36 );
							echo "<td><a href='http://localhost/aaa_test/wp_cms_plugin/{$id}'>Link</a></td>";
							echo "</tr>";
						}
						?>
					</tbody>
				</table>
			</div>
			<?php
		}
	}
	
	function Install()
	{
		global $wpdb;
		$result = $wpdb->query( "CREATE TABLE IF NOT EXISTS `lolcat` (
			id int NOT NULL AUTO_INCREMENT, PRIMARY KEY(id),
			file_type VARCHAR(4),
			loltext varchar(255),
			srstext varchar(255),
			unixdate int
		)" );
	}
}

?>
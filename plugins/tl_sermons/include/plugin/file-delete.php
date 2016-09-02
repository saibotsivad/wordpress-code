<?php
// ===== Security Check ===== //
if( isset ( $security ) ) {
// ========================== //

// if they just got here, verify the deleting process
if ( empty( $_POST ) )
{

	// we need to get the ids of the files desired to be deleted
	$ids = array();
	if ( isset( $_GET['trashfile'] ) ) $ids = array( (int)$_GET['trashfile'] );
	else foreach ( $_GET['file'] as $fileid ) $ids[] = (int)$fileid;

	if ( !empty( $ids ) && $ids != array( 0 ) )
	{
	
		// print out the form
		?>
		<div class="wrap">
			<h2>Delete Files</h2>
			<div id="ajax-response"></div>
			<form id="sermon-files-pre-delete" action="edit.php?post_type=sermon_post&page=tlsp-file-manager&trashfile=confirmed" method="post">
				<?php wp_nonce_field('tlsp_managefiles','tlsp_managefiles'); ?>
				<p>Are you sure you want to <strong>permanently</strong> (a very long time!) delete these files?</p>
				<ul>
		<?php
		foreach ( $ids as $id )
		{
			$post = get_post( $id );
			echo "<li>&#149; {$post->post_title} - {$post->guid} <input type='hidden' name='file[]' value='{$post->ID}' /></li>";
		}
		?>
				</ul>
				<a href="<?php echo get_bloginfo('url'); ?>/wp-admin/edit.php?post_type=sermon_post&page=tlsp-file-manager" class="button-primary" name="Cancel" title="Cancel">Cancel</a>
				<input type="submit" class="button-secondary" name="tlsp_OptionsPage_submit" value="Delete Files">
			</form>
		</div>
		<?php
	}
	else echo "No files selected to delete.";
}

// otherwise, if they clicked the "delete" then really delete the files
else
{

	if ( isset( $_POST['tlsp_managefiles'] ) && wp_verify_nonce( $_POST['tlsp_managefiles'], 'tlsp_managefiles' ) )
	{
		$error = array();
		foreach ( $_POST['file'] as $fileid )
		{
			$file = get_post( $fileid );
			$file = ABSPATH . end( explode( get_bloginfo('url').'/', $file->guid ) );
			$check = @unlink( $file );
			if ( !$check ) $error[$file] = $file;
			wp_delete_attachment( (int)$fileid );
		}
		echo "<div class='wrap'><h2>Deleting Files</h2>";
		if ( empty( $error ) )
		{
			echo "<p>Files successfully deleted.</p></div>";
		}
		else
		{
			echo "<p>Errors found while deleting files, files may not exist or are protected:</p><ul>";
			foreach ( $error as $name => $data )
			{
				echo "<li>{$name}</li>";
			}
			echo "</ul>";
		}
	}
	else wp_die( "You do not have sufficient priveleges for this operation." );

}

// ===== End Security Check ===== //
}
// ============================== //
?>
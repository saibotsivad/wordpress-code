<?php

/**
 * The import process begins here. At each step of the import process, the user is prompted to review what the
 * import function is going to insert into the actual WordPress database. During the import process, several
 * temporary database tables are made to store the transitional data, this means that even after writing the
 * data to the WordPress structured database, the action is (ideally) reversible. When the import process is
 * fully complete, the temporary tables are removed, but they are exported into a log file and saved.
 *
 * @since 0.14
 * @author Tobias Davis
*/

// first we'll check and see if the import process was started already, and if so, where it was left off
$status = get_option( 'plugin_tlsp_importstatus' );

// if the import process hasn't started, we'll set some default values
if ( !$status )
{
	$status = array(
		'intromessage' => "Welcome to the sermon post import process! Here you can see all the steps needed, so just click on the first one and get started!",
		'steps' => array(
			array(
				'status' => 'notdone',
				'title' => 'Add Preachers',
				'statusreport' => 'This one is not done, you should start it now.',
				'submittext' => 'Click here to start',
				'submitname' => 'preachers'
			),
			array(
				'status' => 'notdone',
				'title' => 'Add Series',
				'statusreport' => 'This one is not done, you should start it now.',
				'submittext' => 'Click here to start',
				'submitname' => 'series'
			),
		)
	);
}

// TODO: after each step, you'll need to update that step and the intromessage of the status option

// if the user clicked on a step, display that step
if ( isset( $_POST['tl_sermonimport'] ) ) // I don't know if input arrays work on type=submit elements?
{
	// the button name must be the same as the file that governs that specific import process
	$allowed_buttons = array( 'files', 'passages', 'preachers', 'series', 'sermons', 'services', 'tags' );
	if ( in_array( $_POST['tl_sermonimport'], $allowed_buttons ) )
	{
		include( 'include/tlsp/import-'. $_POST['tl_sermonimport'] .'.php' );
		// NOTE: if the import process has multiple steps, each form must have $_POST['tl_sermonimport'] equal to itself
	}
}

// otherwise, we'll display the import steps with help text on top
else
{

	?>
	<div id="wrap">
		<h2>Sermon Import</h2>
		<p><?php echo $status['intromessage']; ?></p>
		<form id="tl_importsteps" type="post">
			<?php echo "WP Nonce field here"; ?>
			<input type="hidden" name="tl_sermonimport" value="tl_sermonimport" />
			<ul>
				<?php foreach ( $status['steps'] as $step ) { ?>
					<li class="importstep <?php echo $step['status']; ?>">
						<h3><?php echo $step['title']; ?></h3>
						<p class="report <?php echo $step['status']; ?>"><?php echo $step['statusreport']; ?></p>
						<button type="submit" name="tl_sermonimport" value="<?php echo $step['submitname']; ?>"><?php echo $step['submittext']; ?></button>
					</li>
				<?php } /* end foreach */ ?>
			</ul>
		</form>
	</div>
	<?php

}

?>
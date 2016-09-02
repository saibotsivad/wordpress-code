<?php

// ===== Security Check ===== //
if( isset ( $security ) ) :
// ========================== //

// wordpress global class
global $wpdb;

// element count, not including the three custom tables, which are just dropped
$total_count = 0;

// the tables you can delete
$tables = array(
	array(
		'name' => $wpdb->prefix."sermon_reference",
		'id' => 'sermonref',
		'desc' => 'The ranges of verses associated with a sermon.',
		'count' => 0
	),
	array(
		'name' => $wpdb->prefix."sermon_thebible",
		'id' => 'thebible',
		'desc' => 'The verses of the Bible, each with a unique ID.',
		'count' => 0
	),
	array(
		'name' => $wpdb->prefix."sermon_biblebook",
		'id' => 'biblebook',
		'desc' => 'The books of the Bible.',
		'count' => 0
	)
);

// get the counts, if the table exists
foreach ( $tables as $i => $table )
{
	if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table['name']}'" ) )
	{
		$tables[$i]['count'] = (int)$wpdb->get_var( "SELECT COUNT(*) FROM `{$table['name']}`" );
	}
	else unset( $tables[$i] );
}

// the terms you can delete
$terms = array(
	array(
		'name' => 'tlsp_preacher',
		'id' => 'preacher',
		'count' => 0
	),
	array(
		'name' => 'tlsp_series',
		'id' => 'series',
		'count' => 0
	),
	array(
		'name' => 'tlsp_service',
		'id' => 'service',
		'count' => 0
	),
	array(
		'name' => 'tlsp_tag',
		'id' => 'tag',
		'count' => 0
	)
);

// get the counts, if the terms exist
foreach ( $terms as $i => $term )
{
	$terms[$i]['count'] = count( get_terms( $term['name'] ) );
	$total_count = $total_count + $terms[$i]['count'];
	if ( $terms[$i]['count'] == 0 ) unset( $terms[$i] );
}

// the posts you can delete
$posts = array(
	array(
		'name' => "`{$wpdb->posts}` JOIN `{$wpdb->posts}` `a` ON `a`.`ID`=`{$wpdb->posts}`.`post_parent` AND `{$wpdb->posts}`.`post_type`='attachment'",
		'id' => 'attachment',
		'count' => 0
	),
	array(
		'name' => "`{$wpdb->posts}` WHERE `{$wpdb->posts}`.`post_type`='sermon_post'",
		'id' => 'sermonpost',
		'count' => 0
	)
);

// set the counts, if the posts exist
foreach ( $posts as $i => $post )
{
	$posts[$i]['count'] = (int)$wpdb->get_var( "SELECT COUNT(*) FROM {$post['name']}" );
	$total_count = $total_count + $posts[$i]['count'];
	if( $posts[$i]['count'] == 0 ) unset( $posts[$i] );
}

// grab the import options, check if importing has been done
$options = get_option( 'plugin_tlsp_importreport' );

// the transitional tables
$transition_tables = array(
	$wpdb->prefix."sb_transition_preacher",
	$wpdb->prefix."sb_transition_series",
	$wpdb->prefix."sb_transition_tag",
	$wpdb->prefix."sb_transition_service",
	$wpdb->prefix."sb_transition_passage",
	$wpdb->prefix."sb_transition_file",
	$wpdb->prefix."sb_transition_sermon"
);

// check for the existence of any these magical tables
$transition = false;
foreach ( $transition_tables as $row )
{
	if ( $wpdb->get_var( "SHOW TABLES LIKE '{$row}'" ) ) $transition = true;
}

// for the first page display the tables
if ( $_GET['editdatabase'] == '1' )
{

	// if all the tables/etc. are empty
	if ( empty( $tables ) && empty( $terms ) && empty( $posts ) && empty( $options ) )
	{
		?>
		<div class="wrap">
			<div id="icon-edit" class="icon32"><br></div>
			<h2>Sermon Posts Database Management</h2>
			<p>There is nothing to delete.</p>
			<p>Note: To recreate the custom tables, you must deactivate and reactivate the plugin.</p>
		</div>
		<?php
	}
	else
	{
	?>
	<div class="wrap">
		<div id="icon-edit" class="icon32"><br></div>
		<h2>Sermon Posts Database Management</h2>
		<div id="ajax-response"></div>
		<form id="tlsp_deletetables" name="tlsp_deletetables" action="options-general.php?page=tl-sermon-posts&editdatabase=2" method="post">
			<input type="hidden" name="reallydeletetables" value="true" />
			<?php wp_nonce_field( 'tlsp_options_deletetables', 'tlsp_options_deletetables' ); ?>
	<?php if ( !empty( $tables ) ) : ?>
			<p>Here are the database tables you can delete permanently. Doing so will remove them
			using "DROP TABLES", and they will never be seen again, so <em>make sure you have
			a backup!</em></p>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox"></th>
						<th id="table_name">Table Name</th>
						<th id="table_count">Count of Items</th>
					</tr>
				</thead>
				<tbody>
	<?php foreach ( $tables as $i => $row ) : ?>
					<tr>
						<th scope="row" class="check-column"><input name="table[<?php echo $i; ?>]" type="checkbox" />
						<?php wp_nonce_field( 'tlsp_delete'.$row['id'], 'tlsp_delete'.$row['id'] ); ?></th>
						<td><?php echo $row['name']; ?></td>
						<td><?php echo $row['count']; ?></td>
					</tr>
	<?php endforeach; ?>
				</tbody>
			</table>
	<?php endif; ?>
	<?php if ( !empty( $terms ) ) : ?>
			<p>Here are the taxonomy terms you can delete. Deleting any of these is <em>permanent</em>
			and cannot be undone!</p>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox"></th>
						<th id="table_name">Term Name</th>
						<th id="table_count">Count of Terms</th>
					</tr>
				</thead>
				<tbody>
	<?php foreach ( $terms as $i => $row ) : ?>
					<tr>
						<th scope="row" class="check-column"><input name="term[<?php echo $i; ?>]" type="checkbox" />
						<?php wp_nonce_field( 'tlsp_delete'.$row['id'], 'tlsp_delete'.$row['id'] ); ?></th>
						<td><?php echo $row['name']; ?></td>
						<td><?php echo $row['count']; ?></td>
					</tr>
	<?php endforeach; ?>
				</tbody>
			</table>
	<?php endif; ?>
	<?php if ( !empty( $posts ) ) : ?>
			<p>You can also clear out all the posts that are of the post type "sermon_post" (the
			sermons) and remove the sermon files from the database. Removing the sermon files from
			the database will <em>not</em> delete the file from the server. Deleting any of these
			is <em>permanent</em> and cannot be undone!</p>
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" id="cb" class="manage-column column-cb check-column" style=""><input type="checkbox"></th>
						<th id="table_name">Item Name</th>
						<th id="table_count">Count of Items</th>
					</tr>
				</thead>
				<tbody>
	<?php foreach ( $posts as $i => $row ) : ?>
					<tr>
						<th scope="row" class="check-column"><input name="post[<?php echo $i; ?>]" type="checkbox" />
						<?php wp_nonce_field( 'tlsp_delete'.$row['id'], 'tlsp_delete'.$row['id'] ); ?></th>
						<td><?php echo $row['name']; ?></td>
						<td><?php echo $row['count']; ?></td>
					</tr>
	<?php endforeach; ?>
				</tbody>
			</table>
	<?php endif; ?>
	<?php if ( !empty( $options ) ) : ?>
			<p><input name="deleteoptions" type="checkbox" />
			<?php wp_nonce_field( 'tlsp_delete_options', 'tlsp_delete_options' ); ?>
			Check here to delete the report stored from the Sermon Posts Import procedure. Doing so
			will also remove the transitional tables from the database.</p>
	<?php endif; ?>
	<?php if ( $transition ) : ?>
			<p><input name="deletetransitional" type="checkbox" />
			<?php wp_nonce_field( 'tlsp_delete_transitional', 'tlsp_delete_transitional' ); ?>
			Check here to delete the transitional tables created during the Sermon Posts
			import procedure. This will <em>not</em> remove the import report.</p>
	<?php endif; ?>
			<p>Note: To recreate the custom tables, you must deactivate and reactivate the plugin.</p>
	<?php if ( $total_count ) : ?>
			<p>Deleting terms and posts takes a long time, since each delete is passed
			through the WordPress delete functions to avoid caching errors. If you delete all
			<?php echo $total_count; ?> elements it will take about <?php echo round( $total_count/220, 2 ); /* I am totally guesing here */ ?>
			minutes to complete.</p>
	<?php endif; ?>
			<p><input name="deletetables" id="deletetables" class="button-primary" type="submit" value="Permanently Delete Items" /> <strong>Cannot be undone!</strong></p>
		</form>
	</div>
	<?php
	}
}

// if the user clicked "delete" we'll delete things
elseif ( 	$_GET['editdatabase'] == '2' &&
			isset( $_POST['reallydeletetables'] ) &&
			$_POST['reallydeletetables'] == "true" &&
			isset( $_POST['tlsp_options_deletetables'] ) &&
			wp_verify_nonce( $_POST['tlsp_options_deletetables'], 'tlsp_options_deletetables' )
		)
{

	// delete specified custom tables
	foreach ( $tables as $i => $row )
	{
		if ( isset( $_POST['table'][$i] ) &&
			isset( $_POST['tlsp_delete'.$row['id']] ) &&
			wp_verify_nonce( $_POST['tlsp_delete'.$row['id']], 'tlsp_delete'.$row['id'] ) )
		{
			$wpdb->query( "DROP TABLE IF EXISTS {$row['name']}" );
			echo $row['name'].'<br />';
		}
	}
	
	// delete specified terms
	foreach ( $terms as $i => $row )
	{
		//there's no built-in function to delete all terms so...
		$taxes = get_terms( $row['name'] );
		foreach ( $taxes as $tax )
		{
			if ( isset( $_POST['term'][$i] ) &&
				isset( $_POST['tlsp_delete'.$row['id']] ) &&
				wp_verify_nonce( $_POST['tlsp_delete'.$row['id']], 'tlsp_delete'.$row['id'] ) )
			{
				wp_delete_term( $tax->term_id, $row['name'] );
			}
		}
	}
	
	// delete specified posts
	foreach ( $posts as $i => $row )
	{
	
		if ( isset( $_POST['post'][$i] ) &&
			isset( $_POST['tlsp_delete'.$row['id']] ) &&
			wp_verify_nonce( $_POST['tlsp_delete'.$row['id']], 'tlsp_delete'.$row['id'] ) )
		{
			$results = $wpdb->get_results( "SELECT `{$wpdb->posts}`.`ID` AS `id` FROM {$row['name']}" );
			foreach ( $results as $result )
			{
				wp_delete_post( $result->id, true );
			}
		}
	}
	
	// delete the options item
	if ( isset(  $_POST['deleteoptions'] ) &&
		isset( $_POST['tlsp_delete_options'] ) &&\
		wp_verify_nonce( $_POST['tlsp_delete_options'], 'tlsp_delete_options' ) )
	{
		delete_option( 'plugin_tlsp_importreport' );
	}
	
	// delete the transitional tables
	if ( isset(  $_POST['deletetransitional'] ) &&
		isset( $_POST['tlsp_delete_transitional'] ) &&\
		wp_verify_nonce( $_POST['tlsp_delete_transitional'], 'tlsp_delete_transitional' ) )
	{
		foreach ( $transition_tables as $row )
		{
			$wpdb->query( "DROP TABLE IF EXISTS `{$row}`" );
		}
	}
	
	// print out the final message
	echo "<p>Deleting is complete! You will need to deactivate and reactivate the Sermon Posts plugin to reinitialize the tables.</p>";
}
else echo "<p>You do not have sufficient permission to be here. Please leave. Seriously.</p>";

// ===== End Security Check ===== //
endif;
// ============================== //
?>
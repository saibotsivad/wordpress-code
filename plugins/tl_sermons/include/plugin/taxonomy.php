<?php

// which taxonomy are we working on
if ( isset( $_GET['page'] ) )
{
	$taxonomy = ( $_GET['page'] == 'tl_sermons-tlsp_service' ? 'tlsp_service' : false );
	$taxonomy = ( $_GET['page'] == 'tl_sermons-tlsp_series' ? 'tlsp_series' : $taxonomy );
	$taxonomy = ( $_GET['page'] == 'tl_sermons-tlsp_preacher' ? 'tlsp_preacher' : $taxonomy );
}

// security check
if( $taxonomy )
{

	$taxonomy_info = get_taxonomies( array( 'name' => $taxonomy ), 'objects' );
	$labels = $taxonomy_info[$taxonomy];
	$labels = $labels->labels;
	
	// if adding a new taxonomy
	if( isset( $_POST['addnew'] ) && wp_verify_nonce( $_POST[$taxonomy], 'tlsp_addnew_tax' ) && current_user_can( 'edit_others_posts' ) )
	{
		// make sure we are adding a new one, not changing an old one
		if ( !term_exists( $_POST['name'], $taxonomy ) )
		{
			$args = array(
				'description' => $_POST['description'],
				'slug' => $_POST['slug'],
			);
			
			$results = wp_insert_term( $_POST['name'], $taxonomy, $args );
			if ( is_array( $results ) ) echo "<div id='message' class='updated fade'><p>New {$labels->singular_name} created: {$_POST['name']}</p></div>";
			else echo '<div id="message" class="error fade"><p>Invalid taxonomy or term, please try again.</p></div>';
		}
		else echo '<div id="message" class="error fade"><p>The item "'.$_POST['name'].'" already exists.</p></div>';
	}
	
	// change an old taxonomy
	elseif ( isset( $_POST['savechanges'] ) && wp_verify_nonce( $_POST[$taxonomy], 'tlsp_edit_tax' ) && current_user_can( 'edit_others_posts' ) )
	{
		// make sure taxonomy exists
		if ( get_term_by( 'id', (int)$_POST['tax_id'], $taxonomy ) && isset( $_POST['name'] ) )
		{
			$args = array(
				'name' => $_POST['name'],
				'description' => $_POST['description'],
				'slug' => $_POST['slug']
			);
			$results = wp_update_term( (int)$_POST['tax_id'], $taxonomy, $args );
			if ( is_array( $results ) ) echo '<div id="message" class="updated fade"><p>'.ucfirst( $_POST['name'] ).' updated!</p></div>';
			else echo '<div id="message" class="error fade"><p>'.ucfirst( $_POST['name'] ).' failed to update!</p></div>';
		}
		else echo '<div id="message" class="error fade"><p>'.ucfirst( $_POST['name'] ).' does not exist, please make a new item.</p></div>';
	}
	
	// confirm delete a taxonomy
	elseif ( isset( $_POST['deletetax'] ) && wp_verify_nonce( $_POST[$taxonomy], 'tlsp_edit_tax' ) && current_user_can( 'edit_others_posts' ) )
	{
		if ( get_term_by( 'id', (int)$_POST['tax_id'], $taxonomy ) )
		{
			$result = wp_delete_term( (int)$_POST['tax_id'], $taxonomy );
			if ( $result ) echo '<div id="message" class="updated fade"><p>'.ucfirst( $_POST['name'] ).' deleted!</p></div>';
			else echo '<div id="message" class="error fade"><p>Could not delete '.ucfirst( $_POST['name'] ).', try refreshing the page.</p></div>';
		}
		else echo '<div id="message" class="error fade"><p>Could not delete '.ucfirst( $_POST['name'] ).', try refreshing the page.</p></div>';
	}
	
	// grab the taxonomies
	$args = array(
		'orderby' => 'name',
		'hide_empty' => false
	);
	$terms = get_terms( $taxonomy, $args );
	
// Toggle display of "Add New" and "Edit Term" dialogues
?>
<script language='javascript' type='text/javascript'>
	function hideAddNew() {
		if (document.getElementById) {
			document.getElementById('addnew_screen').style.display = 'none';
			document.getElementById('addnew_button').style.visibility = 'visible';
		}
	}

	function showAddNew() {
		if (document.getElementById) {
			document.getElementById('addnew_screen').style.display = 'block';
			document.getElementById('addnew_button').style.visibility = 'hidden';
		}
	}
	
	function showEditTax(element_id) {
		if (document.getElementById) {
			document.getElementById('view_tax_'+element_id).style.display = 'none';
			document.getElementById('edit_tax_'+element_id).style.display = '';
		}
	}
	
	function hideEditTax(element_id) {
		if (document.getElementById) {
			document.getElementById('view_tax_'+element_id).style.display = '';
			document.getElementById('edit_tax_'+element_id).style.display = 'none';
		}
	}
</script> 
<div class="wrap">
	<div id="icon-edit" class="icon32"><br></div>
	<h2><?php echo $labels->name; ?>
		<div id="addnew_button" style="display:inline;">
			<a class="button add-new-h2" href="#" onclick="showAddNew();return false;">Add New</a>
		</div>
	</h2>
	<div id="ajax-response"></div>
	<div id="addnew_screen" style="display:none;">
		<h2>Add New <?php echo $labels->name; ?></h2>
		<form id="add<?php echo $taxonomy; ?>" action="edit.php?post_type=sermon_post&page=tl_sermons-<?php echo $taxonomy; ?>" method="post">
			<input type="hidden" name="addnew" value="<?php echo $taxonomy; ?>">
			<?php wp_nonce_field( 'tlsp_addnew_tax', $taxonomy ); ?>
			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row"><label for="name">Name</label></th>
						<td>
							<input name="name" id="name" value="" class="regular-text" type="text" /><br />
							<span class="description">Human readable name.</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="description">Description</label></th>
						<td>
							<textarea name="description" id="description" class="regular-text" cols="50" rows="6"></textarea><br />
							<span class="description">In a few words, explain this <?php echo $labels->singular_name; ?>.</span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row"><label for="slug">Slug</label></th>
						<td>
							<input name="slug" id="slug" value="" class="regular-text" type="text" /><br />
							<span class="description">The "slug" is the URL-friendly version of the name. It will be auto-generated from the name if you don't make one. It is usually all lowercase and contains only letters, numbers, and hyphens.</span>
						</td>
					</tr>
					<tr valign="bottom">
						<td>
							<p><input class="button-primary" type="submit" name="submit" value="Add New <?php echo $labels->singular_name; ?>" /></p>
							<p><a href="#" onclick="hideAddNew();return false;" class="button-secondary">Cancel</a></p>
						</td>
					</tr>
				<tbody>
			</table>
		</form>
	</div>
	<table class="widefat">
		<thead>
			<tr>
				<th><?php echo $labels->singular_name; ?> Name</th>
				<th>Description</th>
				<th>Slug</th>
				<th>Count</th>
			</tr>
		</thead>
		<tbody>
		
		<?php foreach ( $terms as $term ) : ?>
		
			<tr id="view_tax_<?php echo $term->term_id; ?>">
				<td style="width:20%;"><?php echo $term->name; ?>
					<div class="row-actions">
						<a href="#" onclick="showEditTax('<?php echo $term->term_id; ?>');return false;" title="Edit this <?php echo $labels->singular_name; ?>">Edit</a>
					</div>
				</td>
				<td style="width:50%;"><?php echo $term->description; ?></td>
				<td><?php echo $term->slug; ?></td>
				<td><a href="<?php echo site_url() . "/wp-admin/edit.php?{$taxonomy}={$term->slug}&post_type=sermon_post"; ?>"><?php echo $term->count; ?></a></td>
			</tr>
			<tr style="display:none;" id="edit_tax_<?php echo $term->term_id; ?>">
				<td colspan=4>
					<form id="edit-<?php echo $type; ?>" action="" method="post" style="border:1px solid #dfdfdf;">
						<?php wp_nonce_field( 'tlsp_edit_tax', $taxonomy ); ?>
						<input type="hidden" name="tax_type" value="<?php echo $taxonomy; ?>" />
						<input type="hidden" name="tax_id" value="<?php echo $term->term_id; ?>">
						<table class="form-table">
							<tbody>
								<tr valign="top">
									<th scope="row"><label for="name">Name</label></th>
									<td>
										<input name="name" id="name" value="<?php echo $term->name; ?>" class="regular-text" type="text" />
										<span class="description">Human readable name.</span>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><label for="description">Description</label></th>
									<td>
										<textarea name="description" id="description" class="regular-text" cols="50" rows="6"><?php echo $term->description; ?></textarea><br />
										<span class="description">In a few words, explain this <?php echo $labels->singular_name; ?>.</span>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><label for="slug">Slug</label></th>
									<td>
										<input name="slug" id="slug" value="<?php echo $term->slug; ?>" class="regular-text" type="text" /><br />
										<span class="description">The "slug" is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.</span>
									</td>
								</tr>
								<tr valign="bottom">
									<th scope="row">Save Changes</th>
									<td>
										<p style="padding-top:20px;"><a href="#" onclick="hideEditTax('<?php echo $term->term_id; ?>');return false;" class="button-secondary">Cancel</a></p>
										<p><input type="submit" class="button-primary" name="savechanges" value="Save Changes" /> <input style="float:right;color:#bc0b0b;" type="submit" class="button-delete" name="deletetax" value="Delete Item"></p>
									</td>
								</tr>
							<tbody>
						</table>
						
					</form>
				</td>
			</tr>
		
		<?php endforeach; ?>
		
		</tbody>
	</table>
</div>
<?php

}
?>
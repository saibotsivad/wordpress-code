<?php
/**
 * Copied and Modified from the Media Library administration panel file
 * This whole dumb file could go away if I could add hooks to the file manager screen...
*/

// ===== Security Check ===== //
if( isset ( $security ) ) {
// ========================== //

/*
 *		Setting up the variables
 */

// get the attachment information
$post_id = (int)$_GET['editfileid'];
$file = get_post( $post_id, ARRAY_A );

// set up some other variables
$filenodomain = explode( get_option('siteurl').'/', $file['guid'] ); // like: /uploads/sermons/filename.mp3
$folderlocation = ABSPATH . $filenodomain[1]; // like: /home/www/site.com/wp-content/uploads/sermons/filename.mp3
$uploader = explode( '/', $filenodomain[1] ); // like: /uploads/sermons
$filename = array_pop( $uploader ); // like: filename.mp3
$uploadfolder = '';
foreach( $uploader as $folder ) { $uploadfolder = $uploadfolder . '/' . $folder; }
$filelocation = substr( ABSPATH, 0, strlen( ABSPATH ) - 1 ).$uploadfolder.'/'.$filename; //

// get the mp3 data
if ( $file['post_mime_type'] == 'audio/mpeg' )
{

	// supported mp3 tags: 'Display name' => 'Tag Name'
	$supportedtags = array(
		'Title' => 'title',
		'Artist' => 'artist',
		'Album' => 'album',
		'Year' => 'year'
	);

	// grab the existing tags
	$mp3info = $this->getID3->analyze( $folderlocation );

}

// grab the parent description
$parentdescription = '';
if ( isset( $file['ancestors'][0] ) )
{
	$parentdescription = get_post( $file['ancestors'][0], ARRAY_A );
	$parentdescription = $parentdescription['post_content'];
}

/*
 *		The "save" page
 */

// if this page is accessed after pressing "submit", we'll save the data
if ( isset( $_POST['modifysermonfile'] ) && isset( $_POST['editfileid'] ) )
{
	
	// check if the featured image needs to be removed
	if ( isset( $_POST['removefeaturedimage'] ) )
	{
		delete_post_meta( $file['ID'], '_thumbnail_id' );
	}
	
	// create the updated post array
	$my_post = array();
	
	// the post id
	$my_post['ID'] = $_POST['editfileid'];
	
	// check the title
	if ( isset( $_POST['filetitle'] ) )
	{
		$my_post['post_title'] = $_POST['filetitle'];
	}
	
	// check the description
	if ( isset( $_POST['filedescription'] ) )
	{
		$my_post['post_content'] = $_POST['filedescription'];
	}
	
	// check the date
	if ( isset( $_POST['dateposted'] ) )
	{
		$my_post['post_date'] = $_POST['dateposted'];
	}
	
	// check the slug
	if ( isset( $_POST['fileslug'] ) )
	{
		$my_post['post_name'] = $_POST['fileslug'];
	}
	
	// check the filename
	if ( isset( $_POST['filename'] ) )
	{
	
		// check that the file specified is not the same as the attached one
		if ( $uploadfolder.'/'.$_POST['filename'] != $uploadfolder.'/'.$filename )
		{

			// check that the filename does not exist yet
			if ( !file_exists( ABSPATH . $uploadfolder.'/'.$_POST['filename'] ) )
			{
			
				// if it's not, we'll rename the old file
				$result = rename( ABSPATH.$uploadfolder.'/'.$filename, ABSPATH.$uploadfolder.'/'.$_POST['filename'] );
				
				// if the file was successfully renamed we'll change the guid
				if ( $result )
				{
				
					$filelocation = ABSPATH.$uploadfolder.'/'.$_POST['filename'];
					$my_post['guid'] = get_option('siteurl').$uploadfolder.'/'.$_POST['filename'];
				
				}
			
			}
			
			// TODO: pass the error back to the user: file name already taken
		
		}
	
	}
	
	// update the post
	wp_update_post( $my_post );

/* // saving id3 tags is not working yet, saving tags destroys embedded picture and year on v2	
	// save options for mp3 files
	if( $file['post_mime_type'] == 'audio/mpeg' )
	{
	
		// we can only modify the ID3 tags if the Sermon Browser plugin is disabled, due to namespacing issues
		if ( !is_plugin_active( 'sermon-browser/sermon.php' ) )
		{
		
			// check if the mp3 tags have changed
			$newtags = false;
			foreach ( $supportedtags as $name => $key )
			{
			
				// if the "rebuild tags" was checked, we'll go ahead
				if ( isset( $_POST['rebuildid3v1tags'] ) )
				{
					$mp3info['tags']['id3v2'][$key][0] = (string)$_POST['id3v2tag_'.$key];
					$newtags = true;
				}
				// if the tag has changed, add it to the new array
				elseif ( isset( $mp3info['tags']['id3v2'][$key][0] )
					&& isset( $_POST['id3v2tag_'.$key] )
					&& ( $_POST['id3v2tag_'.$key] != $mp3info['tags']['id3v2'][$key][0] ) )
				{
					$mp3info['tags']['id3v2'][$key][0] = (string)$_POST['id3v2tag_'.$key];
					$newtags = true;
				}
				
			}
			
			// if an album cover is already in existance, we have to pass it back in
			if ( isset( $mp3info['tags']['id3v2']['picture'] ) )
			{
				$mp3info['tags']['id3v2']['attached_picture'][0]['data'] = $mp3info['id3v2']['PIC'][0]['data'];
				$mp3info['tags']['id3v2']['attached_picture'][0]['picturetypeid'] = $mp3info['id3v2']['PIC'][0]['imagetype'];
				$mp3info['tags']['id3v2']['attached_picture'][0]['description'] = $mp3info['id3v2']['PIC'][0]['framenameshort'];
				$mp3info['tags']['id3v2']['attached_picture'][0]['mime'] = $mp3info['id3v2']['PIC'][0]['image_mime'];
			}
			
			// if the tags have changed, update them
			if( $newtags )
			{
			
				// set the file
				$this->writeID3->filename = $filelocation;
				
				// set the tag types
				$this->writeID3->tagformats = array( 'id3v1', 'id3v2.3', 'id3v2.4' );
				
				// only update data
				$this->writeID3->overwrite_tags = true;
				
				// set encoding
				$this->writeID3->tag_encoding = 'UTF-8';
				
				// set the tags
				$this->writeID3->tag_data = $mp3info['tags']['id3v2'];
				//$this->writeID3->tag_data = $newtags;
				
				// write the data
				$results = $this->writeID3->WriteTags();
				
				//check for errors
				if ( !$results )
				{
					$error = array( $this->writeID3->warnings, $this->writeID3->errors );
					var_dump( $error );
					die();
				}
			}
		
		}
	
	}
*/
	
	// finally, redirect back to the page so the data in the browser refreshes
	header( 'Location: '.get_option('siteurl').'/wp-admin/edit.php?post_type=sermon_post&page=tlsp-file-manager&editfileid='.$file['ID'] );

}

// otherwise display the file information
else
{

?>
<div class="wrap">
	<div id="icon-upload" class="icon32"></div>
	<h2>Manage File</h2>
	<div id="ajax-response"></div>
	<p>Manage the file settings here. Change the name, change the slug (HTML safe name), and add or change the "Featured Image".</p>
	<p>Additionally, if the file is an MP3, you can view and edit the ID3 tags, and add or change the embedded Album Art.</p>
	<form name="modifysermonfile" id="modifysermonfile" action="edit.php?post_type=sermon_post&page=tlsp-file-manager&editfileid=<?php echo $file['ID']; ?>" method="post">
		<input type="hidden" name="post_type" value="sermon_post" />
		<input type="hidden" name="page" value="tlsp-file-manager" />
		<input type="hidden" name="editfileid" value="<?php echo $file['ID']; ?>" />
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label for="filetitle">Title</label></th>
					<td>
						<input name="filetitle" id="filetitle" value="<?php echo $file['post_title']; ?>" class="regular-text" type="text">
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="filedescription">Description</label></th>
					<td>
						<textarea name="filedescription" id="filedescription" cols="50" rows="6"><?php echo @$file['post_content']; ?></textarea>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="dateposted">Date Posted</label></th>
					<td>
						<input name="dateposted" id="dateposted" value="<?php echo @$file['post_date']; ?>" class="regular-text" type="text">
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="filename">File Name</label></th>
					<td>
						<input name="filename" id="filename" value="<?php echo @$filename; ?>" class="regular-text" type="text">
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="fileslug">Attachment Slug</label></th>
					<td>
						<input name="fileslug" id="fileslug" value="<?php echo @$file['post_name']; ?>" class="regular-text" type="text">
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">File Location</th>
					<td>
						<?php echo $uploadfolder; ?>
					</td>
				</tr>
<?php if( $file['post_mime_type'] == 'audio/mpeg' ) : ?>
				<tr valign="top">
					<th scope="row">MP3 Tags</th>
					<td>
						<table class="widefat" style="width:100%">
							<thead>
								<tr>
									<th style='width:30px'>Tag</th>
									<th>ID3v2 Value</th>
									<th>ID3v1 Value</th>
								</tr>
							</thead>
<?php
foreach ( $supportedtags as $title => $key )
{
	echo '<tr><td>'.$title.'</td>';
	echo '<td>'.@$mp3info['tags']['id3v2'][$key][0].'</td>';
	echo '<td>'.@$mp3info['tags']['id3v1'][$key][0].'</td>';
	echo "</tr>";
}
echo '</table>';

/* // writing id3 tags is broken because the Sermon Browser plugin causes namespace issues with it
if ( is_plugin_active( 'sermon-browser/sermon.php' ) )
{

	foreach ( $supportedtags as $title => $key )
	{
		echo '<tr><td>'.$title.'</td>';
		echo '<td>'.@$mp3info['tags']['id3v2'][$key][0].'</td>';
		echo '<td>'.@$mp3info['tags']['id3v1'][$key][0].'</td>';
		echo "</tr>";
	}
	echo '</table><p>Editing the ID3 tags is disabled while the Sermon Browser plugin is active.</p>';
}
else
{

	foreach ( $supportedtags as $title => $key )
	{
		echo "<tr><td>{$title}</td>";
		echo '<td><input name="id3v2tag_'.$key.'" id="id3v2tag_'.$key.'" value="'.(string)@$mp3info['tags']['id3v2'][$key][0].'" type="text" style="width:100%" /></td>';
		echo '<td>'.@$mp3info['tags']['id3v1'][$key][0].'</td>';
		echo '</tr>';
	}
	echo '</table>';
	echo '<p style="text-align:right;padding-right:30px;"><label for="rebuildid3v1tags">Rebuild the ID3v1 tags from ID3v2 tags.</label> <input type="checkbox" id="rebuildid3v1tags" name="rebuildid3v1tags"></p>';
} */

?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">MP3 Album Art</th>
					<td>
						<?php if ( isset( $mp3info['id3v2']['PIC'][0]['data'] ) ) : ?>
							<table><tr><td>
								<img width="150px" src="?post_type=sermon_post&page=tlsp-file-manager&fileid_id3image=<?php echo $file['ID']; ?>" />
							</td><td>
								<!-- <p><a class="button-secondary">Remove Image</a></p>
								<p><a class="button-secondary">Use New Image</a></p> -->
							</td></tr></table>
						<?php else: ?>
							<table><tr><td style="border:1px black solid;width:150px;">
								No Image Set
							</td><td>
								<!-- <p><a class="button-secondary">Use New Image</a></p> -->
							</td></tr></table>
						<?php endif; ?>
					</td>
				</tr>
<?php endif; ?>
				<tr valign="top">
					<th scope="row">Featured Image</th>
					<td>
<?php if ( has_post_thumbnail( $file['ID'] ) ) : ?>
						<table><tbody>
							<tr>
								<td>
									<img src="<?php $imageurl = get_post( get_post_thumbnail_id( $file['ID'] ), ARRAY_A ); echo $imageurl['guid']; ?>" width="150px" />
								</td>
								<td>
									<p><a id="set-sermon_post-thumbnail" class="button-secondary thickbox" href="<?php echo get_option('siteurl'); ?>/wp-admin/media-upload.php?post_id=<?php echo $file['ID']; ?>&type=image&TB_iframe=1&width=640&height=563">Add/Edit Featured Image</a></p>
									<p><label for="removefeaturedimage">Remove the Featured Image</label> <input type="checkbox" name="removefeaturedimage" id="removefeaturedimage" /></p>
									<span class="description">Removing the image will not remove it from the Media Library</span>
								</td>
							</tr>
						</tbody></table>
<?php else: ?>
						<table><tbody>
							<tr>
								<td style="border:1px black solid;width:150px;">
									No Image Set
								</td>
								<td>
									<p><a id="set-post-thumbnail" class="button-secondary thickbox" href="<?php echo get_option('siteurl'); ?>/wp-admin/media-upload.php?post_id=<?php echo $file['ID']; ?>&type=image&TB_iframe=1&width=640&height=563">Add/Edit Featured Image</a></p>
									<span class="description">Update page to see an added image.</span>
								</td>
							</tr>
						</tbody></table>
<?php endif; ?>	
					</td>
				</tr>
			</tbody>
		</table>
		<br /><br />
		<input class="button-primary" type="submit" name="modifysermonfile" value="Update All Fields" id="submitbutton" />
		<br /><br />
	</form>
</div>
<?php
}
// ===== End Security Check ===== //
}
// ============================== //
?>
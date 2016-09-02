<?php
// ===== Security Check ===== //
if( current_user_can('upload_files') ) :
// ========================== //

	// if the user clicked save, they maybe checked or unchecked some attachments
	if ( isset( $_POST['save'] ) && isset( $_POST['tlsp_allfiles'] ) && $this->parent_id )
	{
		foreach ( $_POST['tlsp_allfiles'] as $key => $id )
		{
			if ( wp_verify_nonce( $_POST['tlsp_update_file_'.$key], 'tlsp_update_file_'.$key ) )
			{
				// if the checkbox is set, make sure the file is attached
				if ( isset( $_POST['tlsp_checkbox'][$key] ) )
				{
					$file = absint( $_POST['tlsp_checkbox'][$key] );
					$post = get_post( $file );
					if ( $post->post_parent != $this->parent_id )
					{
						$post = array();
						$post['ID'] = $file;
						$post['post_parent'] = $this->parent_id;
						wp_update_post( $post );
					}
				}
				// if it's not set, make sure it's not attached
				else
				{
					$file = absint( $key );
					$post = get_post( $file );
					if ( $post->post_parent == $this->parent_id )
					{
						$post = array();
						$post['ID'] = $file;
						$post['post_parent'] = 0;
						wp_update_post( $post );
					}
				}
			}
		}
		// check the nonce, check other security measures
		// for each attachment, if it needs de/attaching do it
	}

	// these are the tabs I support right now
	$tabs = array(
		array( 'computer', 'From Computer' ),
		//array( 'url', 'From URL' ),
		array( 'library', 'From Media Library' )
	);

	// check for previously attached files
	$already_files = get_posts( array( 'post_type' => 'attachment', 'post_parent' => $this->parent_id ) );
	if ( !empty( $already_files ) )
	{
		// add the tab
		array_unshift( $tabs, array( 'previous', 'Attached Files', $already_files ) );
		// if no tab was selected (click from metabox) display the attached files
		if ( !isset( $_GET['tlsp_tab'] ) ) $_GET['tlsp_tab'] = 'previous';
	}

// set the default tab
if ( !isset( $_GET['tlsp_tab'] ) ) $_GET['tlsp_tab'] = $tabs[0][0];

// grab the options
$options = get_option( 'plugin_tlsp_options' );

// see /wp-admin/includes/media.php 302 wp_iframe() for guidance, but I'm still uncertain...
	//wp_enqueue_style( 'global' );
	//wp_enqueue_style( 'wp-admin' );
	//wp_enqueue_style( 'colors' );
	wp_enqueue_style( 'media' ); // not sure if it's getting called, I'll just leave it in
	//wp_enqueue_style( 'ie' );
	wp_enqueue_script('swfupload-all');
	wp_enqueue_script('swfupload-handlers');
	//do_action('admin_enqueue_scripts', 'media-upload-popup');
	//do_action('admin_print_styles-media-upload-popup');
	do_action('admin_print_styles'); // this one is getting called already?
	//do_action('admin_print_scripts-media-upload-popup');
	//do_action('admin_print_scripts');
	//do_action('admin_head-media-upload-popup');
	//do_action('admin_head');

// I don't know why this is here... the library thingy inserts it
if ( @$_GET['tlsp_tab'] != 'library' )
{
	echo "<script type='text/javascript'>post_id = {$this->parent_id};</script>";
}

// these are the tabs
?>
<style>
	p.howto { display: none; }
	li#tab-tlsp_inactive a { color: #C0C0C0; }
</style>
<div id="media-upload-header">
	<ul id="sidemenu">
		<?php if ( empty( $already_files ) ) echo "<li id='tab-tlsp_inactive'><a>Attached Files</a></li>";
		foreach ( $tabs as $tab ) :
			$class = '';
			if ( $_GET['tlsp_tab'] == $tab[0] ) $class = ' class="current"';
			echo "<li id='tab-{$tab[0]}'><a href='{$this->thickbox_url}&tlsp_tab={$tab[0]}'{$class}>{$tab[1]}</a></li>";
		endforeach; ?>
	</ul>
</div>
<div class="wrap">
<?php
// the form header (the library gets it's own)
if ( $_GET['tlsp_tab'] != 'library' )
{
	?><form enctype="multipart/form-data" method="post" action="<?php echo $this->thickbox_url; ?>" class="media-upload-form validate" id="library-form"><?php
}

// this stuff is the tabs

if ( $_GET['tlsp_tab'] == 'previous' )
{
	$tab = 'previous';
	echo '<h3 class="media-title">Currently added files</h3>';
}
elseif ( $_GET['tlsp_tab'] == 'computer' )
{
	$tab = 'computer';
	echo '<h3 class="media-title">Add media files from your computer</h3>';
	// a WordPress function
	media_upload_form();
}
/*
 * I don't have URLs figured out yet. The default WordPress action is to insert the URL
 * directly into the post content, so it doesn't actually manage these links at all. I mean,
 * they don't go in a database anywhere... I've got a few ideas, but wanted to get the main
 * file part done first.
 */
elseif ( $_GET['tlsp_tab'] == 'url' )
{
	$tab = 'url';
	echo '<h3 class="media-title">Add media files from a URL</h3>';
}
/*
 * The library page
 */
elseif ( $_GET['tlsp_tab'] == 'library' )
{
	$tab = 'library';
	?>
	<h3 class="media-title">Add media files from the Media Library</h3>
	<style>
		/* p.ml-submit { display: none; } */
		li#tab-gallery { display: none; }
	</style>
	<?php
	$error = array();
	media_upload_library_form( $error );
}

// ============ end of the tabs content

?><div id="media-items"><?php

// the library tab doesn't need any of this, and if there aren't any files the same is true
if ( !empty( $already_files ) && $tab != 'library' )
{
	?>
	<script type="text/javascript">
	//<![CDATA[
	jQuery(function($){
		var preloaded = $(".media-item.preloaded");
		if ( preloaded.length > 0 ) {
			preloaded.each(function(){prepareMediaItem({id:this.id.replace(/[^0-9]/g, '')},'');});
		}
		updateMediaForm();
	});
	//]]>
	</script>
	<?php
	// another part lifted out of the core somewhere...
	foreach ( $already_files as $id )
	{
		if ( $id )
		{
			if ( !is_wp_error($id) )
			{
				add_filter('attachment_fields_to_edit', 'media_post_single_attachment_fields_to_edit', 10, 2);
				echo get_media_items( $id, null );
			}
			else
			{
				echo '<div id="media-upload-error">'.esc_html($id->get_error_message()).'</div>';
				exit;
			}
		}
	}
}
?>
</div><?php /* #media-items */ ?>
	<?php if ( $tab != 'library' ) { ?>
		<div>
			<p style="padding-left:20px;padding-bottom:20px;">
				<input id="save" class="button savebutton" type="submit" value="Save all changes" name="save" />
			</p>
		</div>
	</form>
	<?php } ?>
</div><?php /* .wrap */ ?>
<?php
// ===== End Security Check ===== //
endif;
?>
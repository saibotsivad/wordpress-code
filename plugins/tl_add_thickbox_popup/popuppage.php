<?php
if ( !current_user_can('upload_files') ) :
wp_die(__('You do not have permission to upload files.'));
else:
// ===============

// in the outputted form, we can create tabs by using conditionals and passing
// additional GET or POST arguments

// for some guidance, check out the core WordPress file: wp-admin/media-upload.php

if ( isset( $_GET['paged'] ) && $_GET['paged'] == '2' )
{
	echo '<p>This is page 2.</p>';
}
else
{
	echo "<p><a href='{$this->link}&paged=2'>Click to go to page 2.</a></p>";
}

// ===============
endif;
?>
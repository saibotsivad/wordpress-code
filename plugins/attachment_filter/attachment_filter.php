<?php
/*
Plugin Name: Attachment Filter
Plugin URI: http://tobiaslabs.com/
Description: Demonstrates the core code needed to modify the attachment fields.
Version: 1.0
Author: Tobias Labs
Author URI: http://tobiaslabs.com/
*/

$AttachmentFilter = new AttachmentFilter_Class;

// it is a good practice to put functions inside a class, to avoid namespace issues
class AttachmentFilter_Class
{

	function __construct()
	{
		add_filter( 'attachment_fields_to_edit', array( $this, 'EditFilter' ), 11, 2 );
	}
	
	function EditFilter( $form_fields, $post )
	{
	/*
		if ( substr($post->post_mime_type, 0, 5) == 'image' ) {
			$alt = get_post_meta($post->ID, '_wp_attachment_image_alt', true);
			if ( empty($alt) )
				$alt = '';

			$form_fields['post_title']['required'] = true;

			$form_fields['image_alt'] = array(
				'value' => $alt,
				'label' => __('Alternate Text'),
				'helps' => __('Alt text for the image, e.g. &#8220;The Moona Lisa&#8221;')
			);

			$form_fields['align'] = array(
				'label' => __('Alignment'),
				'input' => 'html',
				'html'  => image_align_input_fields($post, get_option('image_default_align')),
			);

			$form_fields['image-size'] = image_size_input_fields( $post, get_option('image_default_size', 'medium') );

		} else {
			unset( $form_fields['image_alt'] );
		}
		return $form_fields;
	*/
		$form_fields = array();
		return $form_fields;
	}

}

?>
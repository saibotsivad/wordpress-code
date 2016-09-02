<?php
/*
Plugin Name: Amazon URL
Plugin URI: http://tobiaslabs.com
Description: Adds a metabox that lets you paste in an Amazon URL and get out the ASIN. Saves the ASIN as a post meta, so you can access it in your themes.
Author: Tobias Davis
Version: 1.0
Author URI: http://davistobias.com
*/

$TL_AmazonURL = new TL_AmazonURL;

class TL_AmazonURL
{
	
	// usually you can leave this alone, but sometimes you might have post_meta name collisions so you can change the key here
	var $meta_key = 'amazonkey';
	
	// Follow this around, it is what makes things unique
	var $unique_key = 'tl_amazonurl';

	/**
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function __construct()
	{
		add_action( 'add_meta_boxes', array( $this, 'MetaBoxSetup' ) );
		add_action( 'save_post', array( $this, 'SavePostMeta' ) );
		add_action( 'admin_head', array( $this, 'AdminHead' ) );
		add_action('wp_ajax_tl_amazonurl_ajax', array( $this, 'AjaxCallback' ) );
	}
	
	/**
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function MetaBoxSetup()
	{
		add_meta_box( 
			'tl_amazonurl',
			__( 'Amazon ASIN', 'tl_amazonurl_title' ),
			array( $this, 'MetaBox' ),
			'post',
			'side',
			'high'
		);
	}
	
	/**
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function MetaBox()
	{
		// setup the variables
		global $post;
		$asin = get_post_meta( $post->ID, $this->meta_key );
		$asin = ( $asin ? $asin[0] : "" );
		$nonce = wp_create_nonce( 'tl_amazonurl_nonce' );
		?>
		<div id="tl_amazonurl_div">
			<p>Paste in the URL from Amazon:</p>
			<input type="hidden" name="tl_amazonurl_checked" value="" />
			<input type="hidden" name="tl_amazonurl_nonce" value="<?php echo $nonce; ?>" />
			<input type="text" id="tl_amazonurl_link" name="tl_amazonurl_link" value="<?php echo $asin; ?>" /><br />
			<span id="tl_amazonurl_return"></span>
			<input type="button" id="tl_amazonurl_check" class="button-primary" name="tl_amazonurl_check" value="Check URL" />
		</div>
		<?php
	}
	
	function AdminHead()
	{
		?>
		<style type="text/css">
		input#tl_amazonurl_link {
			width: 100%;
		}
		div#tl_amazonurl_div {
			text-align: right;
		}
		div#tl_amazonurl_div p {
			text-align: left;
		}
		input#tl_amazonurl_check {
			margin-top: 8px;
		}
		span#tl_amazonurl_return {
			float: left;
			height: 32px;
			margin-top: 6px;
			margin-left: 4px;
			padding-top: 6px;
			padding-left: 32px;
		}
		span#tl_amazonurl_return.success {
			background: url('<?php echo plugins_url( 'check_mark.jpg' , __FILE__ ); ?>') top left no-repeat;
		}
		span#tl_amazonurl_return.failure {
			background: url('<?php echo plugins_url( 'x_mark.jpg' , __FILE__ ); ?>') top left no-repeat;
		}
		</style>
		<script type="text/javascript">
		jQuery(document).ready( function($) {
			$('#tl_amazonurl_check').click( function () {
				$('#tl_amazonurl_return').text('Checking...');
				$("#tl_amazonurl_return").removeClass("failure");
				$("#tl_amazonurl_return").removeClass("success");
				var data = {
					action: 'tl_amazonurl_ajax',
					amazonurl: $("#tl_amazonurl_link").val()
				};
				$.post(ajaxurl, data, function(response) {
					if ( response == 'fail' ) {
						$('#tl_amazonurl_link').val('');
						$('#tl_amazonurl_return').text('Invalid URL!');
						$("#tl_amazonurl_return").removeClass("success").addClass("failure");
					} else {
						$('#tl_amazonurl_link').val(response);
						$('#tl_amazonurl_return').text('Success!');
						$("#tl_amazonurl_return").removeClass("failure").addClass("success");
					}
				});
			});
		});
		</script>
		<?php
	}
	
	function AjaxCallback()
	{
		$key = $this->ValidateASIN( $_POST['amazonurl'] );
		if ( $key ) echo $key;
		else echo 'fail';
		die();
	}
	
	/**
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function SavePostMeta()
	{
		// check security
		if ( current_user_can( 'edit_posts' ) && isset( $_POST['tl_amazonurl_nonce'] ) && wp_verify_nonce( $_POST['tl_amazonurl_nonce'], 'tl_amazonurl_nonce' ) )
		{
			// is this the right way?
			global $post;
			// if the amazon url field is set, we'll check it
			if ( isset( $_POST['tl_amazonurl_link'] ) && $_POST['tl_amazonurl_link'] != '' )
			{
				// we'll double check the link, based on it's size
				if ( strlen( $_POST['tl_amazonurl_link'] ) >= 22 )
				{
					$key = $this->ValidateASIN( $_POST['tl_amazonurl_link'] );
					if ( $key )
					{
						update_post_meta( $post->ID, $this->meta_key, $key );
					}
					else $error = "The link failed to validate with Amazon. Please try copying and pasting again.";
				}
				else
				{
					$url = "http://www.amazon.com/dp/" . $_POST['tl_amazonurl_link'];
					$key = $this->ValidateASIN( $url );
					if ( $key )
					{
						update_post_meta( $post->ID, $this->meta_key, $key );
					}
					else $error = "The link failed to validate with Amazon. Please try copying and pasting again.";
				}
			}
			// if no asin was included, we'll delete the post meta (delete returns false if meta key not found, in this case it's okay)
			else
			{
				delete_post_meta( $post->ID, $this->meta_key );
			}
		}
		// if security checks fail, generate an error message
		else
		{
			$error = "You don't have permission to edit this field.";
		}
		
	}
	
	/**
	 * Given a text string URL from Amazon, the function checks with Amazon and gets the properly formatted ASIN code.
	 *
	 * @since 0.1
	 * @author Tobias Davis
	*/
	function ValidateASIN( $url )
	{
	
		// to easily retrieve the HTML element, we'll use the parser here: http://simplehtmldom.sourceforge.net/
		include( 'simple_html_dom.php' );
		
		// for some reason some servers won't work without an explicit "http://"
		$link = strpos( $url, "http://" );
		if ( $link === false ) $link = "http://".$url;
		else $link = $url;
		
		// you can go ahead and grab the HTML from the given URL
		$html = @file_get_html( $link );
		
		// if there was a failure to get the HTML, return false immediately
		if ( !$html ) return false;
		
		// now you need to grab this element from the HTML:
		// <link rel="canonical" href="http://www.amazon.com/Building-Scalable-Web-Sites-Applications/dp/0596102356" />
		$element = $html->find('link[rel=canonical]');
		
		// if $html->find returns an array you have to go in and get it
		if ( $element )
		{
			// grab the url
			$element = $element[0];
			$element = $element->attr['href'];
			
			// split the url to get the code
			$pieces = explode( "/", $element );
			$key = $pieces[ count( $pieces ) - 1 ];
			
			// for the above example, the function returns this string: 0596102356
			return $key;
		}
		// the parser didn't find the element
		else
		{
			return false;
		}
	
	}

}

?>
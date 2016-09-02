<?php
/*
Plugin Name: Paypal Cart (TL)
Plugin URI: http://tobiaslabs.com/
Description: Used to manage the transaction between your shopping cart and Paypal. Visit <a href="http://tobiaslabs.com/">here</a> for useage documentation.
Version: 0.1
Author: Tobias Labs
Author URI: http://tobiaslabs.com/
*/

$TLPC = new TLPaypalCart;

class TLPaypalCart
{
	
	// bag of holding
	var $cartitems = array();
	
	// user id
	var $cartid = '';

	// initialization
	public function __construct()
	{
		register_activation_hook( __FILE__, array( $this, 'Install' ) );
		add_action( 'init', array( $this, 'Init' ) );
		add_action( 'admin_menu', array( $this, 'Menu' ) );
		register_deactivation_hook( __FILE__, array( $this, 'Uninstall' ) );
	}
	
	// installation options
	function Install()
	{
		// add options
		$options = array(
			'username'		=> '',
			'password'		=> '',
			'signature'		=> '',
			'version'		=> '',
			'pageslug'		=> ''	// page which will handle the shopping cart
		);
		add_option( 'plugin_TLPC', $options );
		
		// add cart table, if it doesn't exist
		global $wpdb;
		$table = $wpdb->query( "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}tlpc_cart` ( `id` VARCHAR(50) NOT NULL, `items` TEXT NOT NULL, `expire` INT NOT NULL, `token` TEXT NOT NULL, PRIMARY KEY( `id` ) )" );
	}
	
	// uninstallation options
	function Uninstall()
	{
		// delete options
		delete_option( 'plugin_TLPC' );
	}
	
	// wordpress init
	function Init()
	{
		// add shortcodes
		add_shortcode( 'TLPC_CartItemCount', array( $this, 'CartItemCount' ) );
		add_shortcode( 'TLPC_AddItemLink', array( $this, 'AddItemLink' ) );
		add_shortcode( 'TLPC_CartItems', array( $this, 'CartItems' ) );
		
		// check for cookie, set one if there isn't one
		if( isset( $_COOKIE['tlpc_shoppingcart'] ) )
		{
			$info = $this->CheckCookie();
			if( $info != false )
			{
				// set the cart id
				$this->cartid = $info->id;
				// set the cart items
				$this->cart = $info->items;
			}
			else
			{
				$this->cartid = $this->SetCookie();
			}
		}
		else
		{
			$this->cartid = $this->SetCookie();
		}
		
		// check if this is the cart page
		$page = get_option( 'plugin_TLPC' );
		$page = $page['pageslug'];
		if( $page == end( explode( "/", $_SERVER['REQUEST_URI'] ) ) )
		{
			$this->CartControl();
		}

	}
	
	// shortcode function: echo the number of items in the cart
	function CartItemCount()
	{
		if( !is_array( $this->cart ) )
		{
			echo '0';
		}
		else
		{
			echo count( $this->cart );
		}
	}
	
	// shortcode function: turns text into link to add to cart
	function AddItemLink( $id = '', $link = '' )
	{
		// typical use, in post: Click [TLPC_AddItemLink id='3']here[/TLPC_AddItemLink] to add to cart.
		if( isset( $id['id'] ) )
		{
			// figure out the correct url
			$site = site_url();
			$cart = get_option( 'plugin_TLPC' );
			$cart = $cart['pageslug'];
			$url = "{$site}/{$cart}?cartaction=add&id={$id['id']}";
			return "<a href='{$url}'>{$link}</a>";
		}
		else
		{
			return $link;
		}
	}
	
	// shortcode function: return the array of cart item ids
	function CartItems()
	{
		return $this->cartitems;
	}
	
	// cookie management: check if a cookie is a valid shopping cart
	function CheckCookie()
	{
		global $wpdb;
		$value = mysql_real_escape_string( $_COOKIE['tlpc_shoppingcart'] );
		$result = $wpdb->get_results( "SELECT `id`, `items` FROM `{$wpdb->prefix}tlpc_cart` WHERE `id`='{$value}'" );
		if( isset( $result[0]->id ) && isset( $result[0]->items ) )
		{
			return $result[0];
		}
		else
		{
			return false;
		}
	}
	
	// cookie management: each viewer gets a cookie which ties them to a cart
	function SetCookie()
	{
		$id = mt_rand() . mt_rand() . mt_rand() . mt_rand();
		$time = time()+60*60*24*10; // expiration length time()+60*60*24*10=10 days
		$check = setcookie( 'tlpc_shoppingcart', $id, $time );
		if( $check )
		{
			global $wpdb;
			$table = $wpdb->query( "INSERT INTO `{$wpdb->prefix}tlpc_cart` ( `id`, `items`, `expire` ) VALUES ( '{$id}', '', {$time} )" );
		}
		else
		{
			return false;
		}
	}
	
	// settings: add the settings menu
	function Menu()
	{
		add_options_page(
			'Paypal Cart Options',
			'Paypal Cart Options',
			'manage_options',
			'tlpc-paypalcart',
			array( $this, 'Options' )
		);
	}
	
	// settings: the options page
	function Options()
	{
	
		// if the page is accessed after pressing "submit" save options otherwise print options
		if( isset( $_POST['action'] ) && $_POST['action'] == 'update' )
		{
		
			// security check
			if ( wp_verify_nonce( $_POST['tlpc_options_nonce'], plugin_basename(__FILE__) ) && current_user_can( 'manage_options' ) )
			{
			
				// all data field checks
				if(
					isset( $_POST['username'] ) && $_POST['username'] != '' &&
					isset( $_POST['password'] ) && $_POST['password'] != '' &&
					isset( $_POST['signature'] ) && $_POST['signature'] != '' &&
					isset( $_POST['version'] ) && $_POST['version'] != '' &&
					isset( $_POST['pageslug'] ) && $_POST['pageslug'] != ''
				)
				{
					// build options
					$options = array(
						'username'		=> $_POST['username'],
						'password'		=> $_POST['password'],
						'signature'		=> $_POST['signature'],
						'version'		=> $_POST['version'],
						'pageslug'		=> $_POST['pageslug']
					);
					// add options
					update_option( 'plugin_TLPC', $options );
					// success
					echo '<div class="wrap">
						<div id="icon-options-general" class="icon32"><br></div>
						<h2>Paypal Cart Options</h2>
						<p>Options updated successfully!</p>
						</div>';
				}
				else
				{
					echo '<div id="message" class="error fade"><p>Failed to save: All fields are required.</p></div>';
				}
			
			}
			else
			{
				echo '<div id="message" class="error fade"><p>Failed to save options, nonce invalid or insufficient priveleges.</p></div>';
			}

		}
		else
		{
			// security check
			if( current_user_can( 'manage_options' ) )
			{
				$options = get_option( 'plugin_TLPC' );
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2>Paypal Cart Options</h2>
	<form method="post" action="options-general.php?page=tlpc-paypalcart">
		<input name="action" value="update" type="hidden">
		<input name="tlpc_options_nonce" id="tlpc_options_nonce" value="<?php echo wp_create_nonce( plugin_basename(__FILE__) ); ?>" type="hidden" />
		<p>Enter the information from your Paypal API key. Read the <a href="">Paypal documentation</a> for more information. <strong>The API login information is not your normal Paypal login!</strong></p>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row">
						<label for="username">API Username</label>
					</th>
					<td>
						<input name="username" id="username" value="<?php echo $options['username']; ?>" class="regular-text" type="text" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="password">API Password</label>
					</th>
					<td>
						<input name="password" id="password" value="<?php echo $options['password']; ?>" class="regular-text" type="text" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="signature">API Signature</label>
					</th>
					<td>
						<input name="signature" id="signature" value="<?php echo $options['signature']; ?>" class="regular-text" type="text" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="version">API Version</label>
					</th>
					<td>
						<input name="version" id="version" value="<?php echo $options['version']; ?>" class="regular-text" type="text" />
						<span class="description">Normal use: 2.0</span>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="pageslug">Page Slug</label>
					</th>
					<td>
						<input name="pageslug" id="pageslug" value="<?php echo $options['pageslug']; ?>" class="regular-text" type="text" />
						<span class="description">The slug of the page the cart will display on.</span>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input name="submit" id="submit" class="button-primary" value="Save Changes" type="submit">
		</p>
	</form>
</div>
<?php
			}
		}
	}
	
	// cart management: user is at cart page, figure out what to do
	function CartControl()
	{
		if ( isset( $_POST['cartaction'] ) )
		{
			if ( $_POST['cartaction'] == 'add' )
			{
				// add item
			}
			elseif ( $_POST['cartaction'] == 'update' )
			{
				// update form
			}
			elseif ( $_POST['cartaction'] == 'checkout' )
			{
				// 1) make a call to the paypal thing, to get the token
				$site = site_url();
				$option = get_option( 'plugin_TLPC' );
$page = <<< EOF
USER={$option['username']}
PWD={$option['password']}
SIGNATURE={$option['signature']}
VERSION={$option['version']}
PAYMENTREQUEST_0_PAYMENTACTION=Sale
PAYMENTREQUEST_0_AMT=12.32
RETURNURL={$site}/{$option['pageslug']}?cartaction=confirmation
CANCELURL={$site}/{$option['pageslug']}?cartaction=cancelpurchase
METHOD=SetExpressCheckout
EOF;
				$paypalurl = "https://api-3t.sandbox.paypal.com/nvp"; // add post info
				$token = "get it from paypal";
				// 2) associate the token to the cookie/cart
				// 3) redirect to paypal
				$paypalurl = "https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token={$token}";
			}
			elseif ( $_POST['cartaction'] == 'confirmation' )
			{
				// make a call to the paypal thing using the token, then print out confirmation
			}
			elseif ( $_POST['cartaction'] == 'cancelpurchase' )
			{
				// display whatever if the person cancelled the purchase while on paypal
			}
		}
	}
	
}


?>
<?php
/*
Plugin Name: Simple Paypal Cart (TL)
Plugin URI: http://tobiaslabs.com/
Description: Used to manage the transaction between your shopping cart and Paypal.
Version: 0.3
Author: Tobias Labs
Author URI: http://tobiaslabs.com/
*/

$TLSPC = new SimplePaypalCart;

class SimplePaypalCart
{
	
	var $option = array(
		'businessid' => '3G4ZYBPASVE3S',
		'currency_code' => 'USD'
	);

	function __construct()
	{
		add_action( 'init', array( $this, 'Init' ) );
	}
	
	function Init()
	{
		add_shortcode( 'TLPC_CartButton', array( $this, 'CartButton' ) );
		add_shortcode( 'TLPC_AddItem', array( $this, 'AddItem' ) );
	}
	
	// shortcode
	function CartButton()
	{
		$form = '<form style="display:inline;" target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_cart">
				<input type="hidden" name="business" value="'.$this->option['businessid'].'">
				<input type="hidden" name="display" value="1">
				<input style="display:inline;" type="image" src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/en_US/i/btn/btn_viewcart_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
				<img style="display:inline;" alt="" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/en_US/i/scr/pixel.gif" width="1" height="1">
				</form>';
		return $form;
	}
	
	// shortcode - outputs a Paypal "Add to cart" button: [TLPC_AddItem id='3']
	function AddItem( $id, $inner )
	{
		if ( isset( $id['id'] ) )
		{
		
			$form = '<form target="paypal" method="post" action="https://www.paypal.com/cgi-bin/webscr">
					<input type="hidden" name="cmd" value="_cart">
					<input type="hidden" name="add" value="1">
					<input type="hidden" name="lc" value="US">
					<input type="hidden" name="button_subtype" value="products">';
		
			// these are the possible fields you can pass in
			// more fields available at: https://cms.paypal.com/us/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_Appx_websitestandard_htmlvariables
			$fields = array(
				'item_name', 'tax_rate', 'item_number', 'amount', 'shipping', 'shipping2', 'handling',
				'discount_amount', 'discount_amount2', 'discount_rate', 'discount_rate2', 'discount_num',
				'quantity', 'tax', 'weight', 'weight_unit'
			);
			foreach ( $fields as $field )
			{
				if ( isset( $id[ $field ] ) )
				{
					$form .= "<input type='hidden' name='{$field}' value='{$id[$field]}' />";
				}
			}
			
			$form .= '<input type="hidden" name="business" value="'.$this->option['businessid'].'" />
					<input type="hidden" name="currency_code" value="'.$this->option['currency_code'].'" />
					<input type="hidden" name="return" value="http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'].'" />
					<input type="hidden" name="bn" value="PP-ShopCartBF:btn_cart_LG.gif:NonHosted">
					'.$inner.'
					<img alt="" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110306-1/en_US/i/scr/pixel.gif" width="1" height="1">
					</form>';
			
			return $form;
		}
		else
		{
			return false;
		}
	}
	
}

?>

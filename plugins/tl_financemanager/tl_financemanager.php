<?php
/*
Plugin Name: Finance Manager
Plugin URI: http://www.tobiaslabs.com/
Description: Use Wordpress to plan and track your finances using an "envelopes" system.
Version: 0.1
Author: Tobias Davis
Author URI: http://davistobias.com
*/

add_action('admin_menu', 'tlfinman_menu');

function tlfinman_menu() {
	add_menu_page( 'Finance Manager', 'Finances', 'manage_options', 'tlfinman_menu', 'tlfinman_menu_main', rtrim(trailingslashit(get_option('siteurl')) . 'wp-content'.'/plugins/'.plugin_basename(dirname(__FILE__)), '/').'/tlfinman-icon.png', 10 );
	add_submenu_page('tlfinman_menu', 'View Transactions', 'Transactions', 'manage_options', 'tlfinman_transactions', 'tlfinman_menu_transactions');
	add_submenu_page('tlfinman_menu', 'Edit Envelopes', 'Envelopes', 'manage_options', 'tlfinman_envelopes', 'tlfinman_menu_envelopes');
	add_options_page('Finance Options', 'Finance Manager', 'manage_options', 'my-unique-identifier', 'tlfinman_options');
}

function tlfinman_menu_main() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
  echo '<div class="wrap">';
  echo '<h2>Finance Manager</h2>';
  echo '<p>This would be where you add view the envelopes and add a transaction.</p>';
  echo '</div>';
}

function tlfinman_menu_envelopes() {
	echo '<div class="wrap"><h2>Edit Envelopes</h2><p>Here\'s where stuff goes!</p></div>';
}

function tlfinman_menu_transactions() {
	echo '<div class="wrap"><h2>View/Edit Transaction History</h2><p>Here\'s where stuff goes!</p></div>';
}

function tlfinman_options() {
	if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
  echo '<div class="wrap">';
  echo '<p>Here is where the form would go if I actually had options.</p>';
  echo '</div>';
}

?>
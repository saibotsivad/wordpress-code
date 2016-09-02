<?php
/*
Plugin Name: TL Mobile Browser
Plugin URI: http://tobiaslabs.com/
Description: Switch templates if the viewer is using a mobile browser.
Version: 0.1
Author: Tobias Labs
Author URI: http://tobiaslabs.com/
*/

// made with help from: http://agafix.org/how-to-automatically-switch-wordpress-themes-onthefly/
class WP_Theme_Switcher
{
	// variables
    public $current_theme;
    public $theme_to_apply;
	public $options;
	
    public function __construct()
	{
		// the theme we want to use by default is whatever was chosen
		$current_theme = get_theme( get_current_theme() );
		$this->current_theme = $current_theme["Template"];
		$this->theme_to_apply = $this->current_theme;
		
		// using the URL we can see if this is a page to apply a different theme
		$this->current_page = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		$result = explode( get_bloginfo('home'), $this->current_page );
		
		// grab the data to see if we should switch pages
		$this->options = get_option( 'template_tobiaslabs' );
		
		// if there are no options, we'll use the defaults
		if ( !$this->options ) $this->SetDefault();
		
		// look for the correct page URL
		$result = explode( $this->options['pagename']['value'], $result[1] );
		
		if ( count( $result ) >= 2 ) $this->theme_to_apply = 'clean-home';
		
		// add the admin settings menu
		add_action( 'admin_menu', array( $this, 'AddSettingsMenu' ) );
    }
	
	// these are the default settings
	public function SetDefault()
	{
		$this->options = array(
			'pagename' => array(
				'value' => 'sermonposts',
				'name' => 'Page Slug',
				'description' => 'The slug of the parent page for the custom template'
			),
			'templatename' => array(
				'value' => 'name',
				'name' => 'Template Name',
				'description' => 'Template name (use the slug) to use for the page and children pages'
			)
		);
	}
	
	// register a settings page, so you can set the parent page thingy
	public function AddSettingsMenu()
	{
		add_options_page(
			'Template Options',
			'Template Options',
			'manage_options',
			'tl-template-options',
			array( $this, 'TemplateOptionsPage' )
		);
	}
	
	// this thing display the admin settings page html
	public function TemplateOptionsPage()
	{
		// get the option, use the default if not set
		$options = get_option( 'template_tobiaslabs', $this->options );	
		// if this page is accessed by saving, save data
		if ( @$_POST['savechanges'] && wp_verify_nonce( @$_POST['tl_saveoptions'], 'tl_saveoptions' ) )
		{
			$options['pageid']['value'] = (int)$_POST['pageid'];
			$options['templatename']['value'] = $_POST['templatename'];
			$result = update_option( 'template_tobiaslabs', $options );
			if ( $result ) echo '<div id="message" class="updated fade"><p>Options updated!</p></div>';
		}
		// output the form
		?>
			<div class="wrap">
				<div id="icon-edit" class="icon32"><br></div>
				<h2>Template Options</h2>
				<div id="ajax-response"></div>
				<form id="templateoptions" name="templateoptions" action="options-general.php?page=tl-template-options" method="post">
					<?php wp_nonce_field( 'tl_saveoptions', 'tl_saveoptions' ); ?>
					<table class="form-table">
						<tbody>
						<?php foreach ( $options as $name => $option ) { ?>
							<tr valign='top'>
								<th scope="row"><label for="<?php echo $name; ?>"><?php echo $option['name']; ?></label></th>
								<td><input name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo $option['value']; ?>" class="regular-text" type="text" />
								<td><span class="description"><?php echo $option['description']; ?></span></td>
							</tr>
						<?php } ?>
						</tbody>
					</table>
					<p class="submit"><input type="submit" name="savechanges" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
				</form>
			</div>
		<?php
	}
	
	// set the correct theme to use
    public function ReallySetTheme()
	{
        return $this->theme_to_apply ;
    }
 
    public function SetTheme(){
        add_filter( 'template', array(&$this, 'ReallySetTheme') );
        add_filter( 'stylesheet', array(&$this, 'ReallySetTheme') );
    }
}
 
$wp_theme_switcher = new WP_Theme_Switcher();

add_action( 'setup_theme', array( &$wp_theme_switcher, 'SetTheme' ) );

?>
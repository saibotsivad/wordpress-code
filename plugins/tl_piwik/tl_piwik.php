<?php
/*
Plugin Name: TL Piwik
Plugin URI: http://tobiaslabs.com/
Description: Track visitors to the website. (Uses the standard Piwik JavaScript.)
Version: 0.1
Author: Tobias
Author URI: http://tobiaslabs.com
*/

$TL_Piwik = new TL_Piwik;

class TL_Piwik
{
	// Put in the appropriate Piwik ID here
	var $id = 5;
	// Put in the appropriate domain for the Piwik URL here
	var $url = "analytics.tobiaslabs.com";
	
	function __construct()
	{
		add_action( 'wp_footer', array( $this, 'Script' ) );
	}
	
	function Script()
	{
	
?>
<!-- Piwik -->
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "https://<?php echo $this->url; ?>/" : "http://<?php echo $this->url; ?>/");
document.write(unescape("%3Cscript src='" + pkBaseURL + "piwik.js' type='text/javascript'%3E%3C/script%3E"));
</script><script type="text/javascript">
try {
var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", <?php echo $this->id; ?>);
piwikTracker.trackPageView();
piwikTracker.enableLinkTracking();
} catch( err ) {}
</script><noscript><p><img src="http://<?php echo $this->url; ?>/piwik.php?idsite=<?php echo $this->id; ?>" style="border:0" alt="" /></p></noscript>
<!-- End Piwik Tracking Code -->
<?php
	
	}
	
	
}

?>
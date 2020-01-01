<?php
/**
 * Plugin Name: WooCommerce Microsoft Ads Conversion Tracking
 * Plugin URI: https://apogee.media.com
 * Description: Apogee Media WooCommerce Microsoft Ads Conversion Tracking Code
 * Author: Andres de la Garza
 * Author URI: https://apogee.media.com
 * Version: 0.1.2
 **/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

add_action('admin_menu', 'test_plugin_setup_menu');
add_action('admin_notices', 'mads_admin_notice');
add_action('wp_head', 'mads_footer');

function mads_wp_api_key() {
	$key = trim(get_option('uetid'));
	return $key; 
}

function test_plugin_setup_menu(){
	add_menu_page( 'Test Plugin Page', 'LP Plugin', 'manage_options', 'test-plugin', 'test_init' );
}

function mads_admin_notice() {
	$api_key = mads_wp_api_key();
  
	$is_plugins_page = (substr($_SERVER["PHP_SELF"], -11) == 'plugins.php');
  
	if ($is_plugins_page && !$api_key && function_exists("admin_url")) {
	  echo '<div class="error"><p><strong>' .
		   sprintf(__('<a href="%s">Enter your Microsooft Ads UET</a> to enable adverstising tracking script insertion.'),
				   admin_url('options-general.php?page=mads')) .
		   '</strong></p></div>';
	}
  }

function test_init() {
	//must check that the user has the required capability
	if (!current_user_can('manage_options')) {
	  wp_die( __('You do not have sufficient permissions to access this page.') );
	}
  
	// Read in existing option value from database
	$uetid = get_option('uetid');
	$server_side_dni = get_option('server_side_dni');
  
	// See if the user has posted us some information
	// If they did, this hidden field will be set to 'Y'
	if(isset($_POST['mads_hidden_field']) && $_POST['mads_hidden_field'] == 'Y') {
	  // Read their posted value
	  $uetid = trim($_POST['uetid']);
	  $server_side_dni = !empty($_POST['server_side_dni']) ? 'true' : 'false';
  
  
	  // Save the posted value in the database
	  update_option('uetid', $uetid );
	  // Put an settings updated message on the screen
	  echo '<div class="updated"><p><strong>Your Microsoft Ads settings were saved successfully.</strong></p></div>';
	}
  

  ?>
	  <div class="wrap">
		<h2>Microsoft Ads Settings</h2>
		<p>Dynamically add Microsoft Ads UET to your site.</p>
		<form method="POST" action="">
		  <input type="hidden" name="mads_hidden_field" value="Y">
		  <table class="form-table" cellpadding="0" cellspacing="0">
			<tr valign="top">
			  <th scope="row" style="padding-left: 0px">
				<label for="uetid">Microsoft Ads UET ID</label>
			  </th>
			  <td>
				<input name="uetid" type="text" id="uetid"
					   class="regular-text code" size="20" value="<?php echo $uetid ?>" />
			  </td>
			</tr>
			<tr valign="top">
			  <td colspan="2" style="padding-left: 0px">
				<span class="description">You can find this value in your
				  <a href="https://ads.microsoft.com/" target="_blank">Microsoft Ads account</a>.
				</span>
			  </td>
			</tr>
			<tr valign="top">
			  <th scope="row" style="padding-left: 0px">
				<label for="server_side_dni">Enable As First Party Script</label>
			  </th>
			</tr>
		  </table>
		  <p class="submit">
			<input type="submit" name="Submit" class="button-primary" value="Save Changes" />
		  </p>
		</form>
	  </div>
  <?php
}

function mads_footer() {
	$api_key = mads_wp_api_key();
  
	if (!$api_key) {
	  return;
	}
    
	echo "\r\n<!-- Microsoft Ads WordPress Integraton -->\r\n";
	echo "<script>(function(w,d,t,r,u){var f,n,i;w[u]=w[u]||[],f=function(){var o={ti:\"{$api_key}\"};o.q=w[u],w[u]=new UET(o),w[u].push(\"pageLoad\")},n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function(){var s=this.readyState;s&&s!==\"loaded\"&&s!==\"complete\"||(f(),n.onload=n.onreadystatechange=null)},i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)})(window,document,\"script\",\"//bat.bing.com/bat.js\",\"uetq\");</script>\r\n\r\n";

}

class MicrosoftAdsConversionTracking {

	public function __construct() {

		// add the Conversion Code to WooCommerce Thank You Page
		add_action( 'woocommerce_thankyou', array( $this, 'displayMicrosoftAdsConversionTracking' ) );

	}

	function displayMicrosoftAdsConversionTracking( $order_id ) {

		// Lets grab the order
		$order = new WC_Order( $order_id );

		?>

		<script type="text/javascript">
			var amount = '<?php echo $order->get_total();?>';
			// Checkout
			window.uetq = window.uetq || []; 
			window.uetq.push({ 'ec':'Checkout', 'ea':'Purchase', 'el':'Purchase', 'gv': amount });
		</script>

		<?php
	}
}

$microsoftAdsConversionTracking = new MicrosoftAdsConversionTracking();

<?php
/*
Plugin Name: BuyPlayTix
Plugin URI: http://wordpress.org/plugins/buyplaytix/
Description: Integrate BuyPlayTix into your wordpress site.
Version: 1.2.5
Author: Tim Thomas
Author URI: https://github.com/tthomas48
License: GPL2
Text Domain: buyplaytix
*/

if (version_compare(PHP_VERSION, '5.2.4') >= 0) {

  require_once 'buyplaytix_ext.php';
} else {
  function version_notice() {
    echo "<div id='notice' class='updated fade'><p>BuyPlayTix Plugin requires PHP 5.2.4 or greater. Feel free to <a target=\"_new\" href=\"mailto:support@buyplaytix.com\">shoot us an email</a> and we'll help you get it setup.</p><p>To get rid of this message <a href=\"" .  site_url("wp-admin/plugins.php?") . "\">deactivate</a> the BuyPlayTix extension.</p></div>\n";

  }
  add_action( 'admin_notices', 'version_notice' );
}

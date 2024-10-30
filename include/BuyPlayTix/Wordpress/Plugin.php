<?php
namespace BuyPlayTix\Wordpress;
class Plugin {

  public static function location_shorttag($attributes) {
    $oauth = new \BuyPlayTix\WordPress\OAuth();
    $location = $oauth->getLocation($attributes["url"]);
    return \BuyPlayTix\WordPress\Plugin::render("location", array(
        "location" => $location
    ));
  }
  public static function additional_info_shorttag($attributes) {
    $oauth = new OAuth();
    $production = $oauth->getProduction($attributes["url"]);
    if(!empty($production->ADDITIONAL_INFO)) {
      return '<div class="add-info">' . $production->ADDITIONAL_INFO . '</div>';
    }
    return "";
  }
  public static function minical_shorttag($attributes) {
    $oauth = new OAuth();
    $shows = $oauth->getShows($attributes["url"]);
    $dates = array();
    foreach($shows as $show) {
      $dates[] = $show->DATE . " " . $show->TIME;
    }
    return \BuyPlayTix\Util\CalendarUtils::create_minical(NULL, $dates);
  }
  public static function logo_shorttag($attributes) {
    $oauth = new OAuth();
    $production = $oauth->getProduction($attributes["url"]);
    if(!empty($production->LOGO)) {
      return '<div class="prod-logo"><img src="' . $production->LOGO . '" /></div>';
    }
    return "";
  }
  public static function when_shorttag($attributes) {
    $oauth = new OAuth();
    $production = $oauth->getProduction($attributes["url"]);
    return '<div class="when">' . $production->RUN_STRING . '</div>';
  }
  public static function people_shorttag($attributes) {
    $oauth = new \BuyPlayTix\WordPress\OAuth();
    $people = $oauth->getPeople($attributes["url"]);
    return \BuyPlayTix\WordPress\Plugin::render("people", array(
        "people" => $people
    ));
  }
  public static function tickets_shorttag($attributes) {
    $oauth = new OAuth();
    $producer = $oauth->getProducer();
    $producer_url_name = $producer->url_name;

    $production_url = $producer->secure_path . "/reservewidget/" . $attributes["url"] . ".html";
    $contents = file_get_contents($production_url);
    if(strstr($contents, '404: Page Not Found') === FALSE) {
      if(empty($contents)) {
        // production is done
        return '<h5>Tickets are no longer being sold for this production.</h5>';

      }
      return '<script type="text/javascript">' . $contents . '</script>';
    }
    return '<div class="error">Error: Production ' . $attributes["url"] . ' not found.</div>';
  }

  public static function donate_shorttag($attributes) {
    $oauth = new OAuth();
    $producer = $oauth->getProducer();

    $attributes["producer"] = $producer;
    return Plugin::render("donate", $attributes);
  }


  public static function calendar_shorttag($attributes) {
    $oauth = new OAuth();
    $producer = $oauth->getProducer();

    return Plugin::render("calendar-large", array(
        "producer" => $producer
    ));
  }

  public static function history_shorttag($attributes) {
    $oauth = new OAuth();
    $history = $oauth->getHistory();

    return Plugin::render("history", array(
	"history" => $history,
    ));
  }

  public function getCalendarUrl($replacements = array()) {
    $attributes = $this->getAttributes();
    $currentValues = array(
        "view" => $attributes["view"],
        "previous" => "false",
        "next" => "false",
        "current" =>   $attributes["current"],
        "metro" =>   $attributes["metro"],
    );
    foreach($replacements as $key => $value) {
      $currentValues[$key] = $value;
    }
    return "calendar.html?" . http_build_query($currentValues);
  }

  public static function admin_menu() {
    add_options_page('BuyPlayTix Options', 'BuyPlayTix', 'manage_options', 'buyplaytix-manage-options', array('BuyPlayTix\Wordpress\Plugin', 'admin_options'));
  }

  public static function admin_notices() {
    if(!OAuth::isConfigured()) {
      $url = site_url("wp-admin/options-general.php?page=buyplaytix-manage-options");
      echo "<div id='notice' class='updated fade'><p>BuyPlayTix Plugin is not configured yet. Please <a href='" . $url . "'>do it now</a>.</p></div>\n";
    }
  }

  public static function admin_options() {
    if ( !current_user_can( 'manage_options' ) )  {
      wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }
    if ($_SERVER["REQUEST_METHOD"] === 'POST') {
      foreach($_POST as $key => $value) {
        if(strstr($key, 'membership') !== false) {
          update_option("buyplaytix_" . $key, $value);
        }
      }
    }



    $oauth = new OAuth();
    $productions = $oauth->getProductions(true);
    $producer = $oauth->getProducer();
    $memberships = $oauth->getMemberships();
    $rawRoles = wp_roles();
    $roles = [];
    foreach($rawRoles->roles as $key => $data) {
      $roles[$key] = $data["name"];
    }
    $membershipKeys = ["none"];
    foreach($memberships as $membership) {
      $membershipKeys[] = $membership->UID;
    }
    $mappings = [];
    foreach($membershipKeys as $key) {
      $fullKey = "buyplaytix_membership_$key";
      $mappings[$fullKey] = get_option($fullKey, NULL);
    }


    $pages = array_merge(get_posts(), get_pages());
    //$all_pages = array_merge($pages, get_pages(array("post_type" => "post")));
    print Plugin::render("admin-oauth", array(
        "productions" => $productions,
        "producer" => $producer,
        "memberships" => $memberships,
        "mappings" => $mappings,
        "pages" => $pages,
        "roles" => $roles,
        "active" => empty($_REQUEST["tab"]) ? 'authorize': $_REQUEST["tab"],
    ));
  }

  public static function admin_init() {

    if(array_key_exists("page", $_REQUEST) && $_REQUEST["page"] != "buyplaytix-manage-options") {
      return;
    }

    $action = "";
    if (array_key_exists("action", $_REQUEST)) {
      $action = $_REQUEST["action"];
    }
    if($action == "reauthorize") {
      update_option("buyplaytix_token", "");
      update_option("buyplaytix_secret", "");
    }
    elseif($action == "linkProduction") {
      $oauth = new OAuth();
      $response = $oauth->linkProductions($_REQUEST["productions"], $_REQUEST["pages"]);
      if($response->code != 200) {
        add_settings_error("BuyPlayTix", "linkProduction", "Unable to link production.");
      }
      return;
    }

    $oauth = new OAuth();
    $oauth->finish();

    if(!OAuth::isConfigured()) {
      $oauth->signin(site_url("wp-admin/options-general.php?page=buyplaytix-manage-options"));
    }
  }

  public static function get_featured_image($url_name) {
    if(empty($url_name)) {
      return "";
    }

    global $wpdb;
    
    $row = $wpdb->get_row( 
	$wpdb->prepare( 
		"
                SELECT post_id FROM $wpdb->postmeta
		 WHERE meta_key = %d
		 AND meta_value = %s
		",
	        "bpt_production", $url_name
        )
    );

    if(!$row->post_id) {
      return "";
    }

    if (!has_post_thumbnail($row->post_id)) {
      return "";
    }

    // we just take the first one.
    $thumb = get_the_post_thumbnail($row->post_id, "medium");
    $thumb = preg_replace('/.*src="([^"]+)".*/', '$1', $thumb);
    return $thumb;
  }

  public static function render($page, $attributes = array()) {
    \HTML_Template_Nest_View::$CACHE = false;
    \HTML_Template_Nest_View::$INCLUDE_PATHS = array(realpath(dirname(__FILE__) . "/../../../views"));

    $view = new \HTML_Template_Nest_View($page);
    $view->setAttributes($attributes);
    return $view->render();
  }

  public static function settings_link($links, $file) {

    if ( $file == 'buyplaytix/buyplaytix.php' ) {
      /* Insert the link at the end*/
      $links['settings'] = sprintf( '<a href="%s"> %s </a>', admin_url( 'options-general.php?page=buyplaytix-manage-options' ), __( 'Settings', 'plugin_domain' ) );
    }
    return $links;
  }

  public static function production_box_content($post) {

    $current_production = get_post_meta( $post->ID, 'bpt_production', true );
    $oauth = new \BuyPlayTix\Wordpress\OAuth();
    $productions = $oauth->getProductions(true);

    wp_nonce_field(plugin_basename( __FILE__ ), 'production_box_content_nonce');
    echo '<select id="bpt_production" name="bpt_production">';
    echo '<option value="">Not Assigned</option>';
    foreach($productions as $production) {
      echo '<option value="' . $production->URL_NAME . '" ' . ($current_production == $production->URL_NAME ? ' selected="selected" ' : '') . '>' . $production->NAME  . '</option>';
    }
    echo '</select>';
    echo '<br />';
    echo '<label for="bpt_link_production">Link Production</label>';
    echo '<input id="bpt_link_production" name="bpt_link_production" type="checkbox" checked="checked" value="true" />';

  }
  public static function register_production_box() {
    add_meta_box(
    'production_box',
    __( 'Production', 'buyplaytix_textdomain' ),
    array('\BuyPlayTix\Wordpress\Plugin', 'production_box_content'),
    'post',
    'side',
    'high'
        );
        add_meta_box(
        'production_box',
        __( 'Production', 'buyplaytix_textdomain' ),
        array('\BuyPlayTix\Wordpress\Plugin', 'production_box_content'),
        'page',
        'side',
        'default'
            );

  }
  public static function production_box_save( $post_id ) {
    if (defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE) {
      return;
    }

    if (!wp_verify_nonce( $_POST['production_box_content_nonce'], plugin_basename( __FILE__ ) )) {
      return;
    }

    if ('page' == $_POST['post_type']) {
      if ( !current_user_can( 'edit_page', $post_id ) ) {
        return;
      }
    } else {
      if ( !current_user_can( 'edit_post', $post_id ) ) {
        return;
      }
    }

    $production = $_POST['bpt_production'];
    $link_production = $_POST['bpt_link_production'];
    update_post_meta( $post_id, 'bpt_production', $production);
    if($link_production == "true") {
      $oauth = new OAuth();
      $oauth->linkProductions($production, get_permalink($post_id));
    }
  }
}

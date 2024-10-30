<?php

require 'vendor/autoload.php';

function buyplaytix_scripts()
{
  $oauth = new \BuyPlayTix\Wordpress\OAuth();
  $producer = $oauth->getProducer();

  wp_enqueue_style('bpt-ecss.css', $producer->css_path . '/ecss.css');
  wp_enqueue_script('jquery.scrollTo', plugins_url('/buyplaytix/js/jquery.scrollTo-1.4.2-min.js'), array('jquery'));
  wp_enqueue_script('bpt-common', plugins_url('/buyplaytix/js/common.js'), array('jquery'));
  wp_enqueue_script('bpt-wordpress', plugins_url('/buyplaytix/js/wordpress.js'), array('jquery'));
  wp_enqueue_script('bpt-transaction-common', plugins_url('/buyplaytix/js/transaction-common.js'), array('jquery'));
  wp_enqueue_script('bpt-donate', plugins_url('/buyplaytix/js/donate.js'), array('jquery'));
  wp_enqueue_script('fullcalendar', plugins_url('/buyplaytix/js/vendor/fullcalendar/fullcalendar.min.js'), array('jquery'));
  wp_enqueue_script('fullcalendar.bpt', plugins_url('/buyplaytix/js/vendor/fullcalendar/bpt.js'), array('jquery'));
  wp_enqueue_style('fullcalendar.css', plugins_url('/buyplaytix/css/vendor/fullcalendar/fullcalendar.css'));
}

function buyplaytix_admin_scripts()
{
  $oauth = new \BuyPlayTix\Wordpress\OAuth();
  $producer = $oauth->getProducer();

//  wp_enqueue_style('bpt-ecss.css', $producer->css_path . '/ecss.css');
  wp_enqueue_script('jquery.scrollTo', plugins_url('/buyplaytix/js/jquery.scrollTo-1.4.2-min.js'), array('jquery'));
  wp_enqueue_script('bpt-common', plugins_url('/buyplaytix/js/common.js'), array('jquery'));
  wp_enqueue_script('bpt-wordpress', plugins_url('/buyplaytix/js/wordpress.js'), array('jquery'));
  wp_enqueue_script('bpt-transaction-common', plugins_url('/buyplaytix/js/transaction-common.js'), array('jquery'));
  wp_enqueue_script('bpt-donate', plugins_url('/buyplaytix/js/donate.js'), array('jquery'));
//  wp_enqueue_script('fullcalendar', plugins_url('/buyplaytix/js/vendor/fullcalendar/fullcalendar.min.js'), array('jquery'));
//  wp_enqueue_script('fullcalendar.bpt', plugins_url('/buyplaytix/js/vendor/fullcalendar/bpt.js'), array('jquery'));
//  wp_enqueue_style('fullcalendar.css', plugins_url('/buyplaytix/css/vendor/fullcalendar/fullcalendar.css'));
}

add_action('wp_enqueue_scripts', 'buyplaytix_scripts');
add_action('admin_enqueue_scripts', 'buyplaytix_admin_scripts');


function buyplaytix_widgets()
{
  register_widget('BuyPlayTix\Wordpress\Widget\DonateWidget');
  register_widget('BuyPlayTix\Wordpress\Widget\CalendarWidget');
  register_widget('BuyPlayTix\Wordpress\Widget\ProductionWidget');
}

function buyplaytix_membership($user, $username, $password)
{
  if (empty($user) || array_key_exists("errors", $user)) {
    return $user;
  }
  if (in_array( 'admin', (array) $user->roles ) || in_array( 'administrator', (array) $user->roles )) {
    // we're not messing with admin users
    return $user;
  }
  $roles = wp_roles();
  $oauth = new BuyPlayTix\Wordpress\OAuth();
  $subscriptions = $oauth->getSubscriptions($user);
  $currentRole = get_option("buyplaytix_membership_none", NULL);
  foreach($subscriptions as $subscription) {
    $fullKey = "buyplaytix_membership_" . $subscription->MEMBERSHIP->UID;
    $roleId = get_option($fullKey, NULL);
    $currentRole = $roleId;
  }
  if (!$currentRole) {
    return $user;
  }
  if ( !in_array( $currentRole, (array) $user->roles ) ) {
    // add this role
    $user->role = $currentRole;
    $new_user_id = wp_update_user( $user ); // A new user has been created
    if ( is_wp_error( $new_user_id ) ) {
      return $new_user_id;
    }
  }
  return $user;
}

function buyplaytix_mailchimp_sync( $sync, $user ) {

  if (!$sync) {
    return false;
  }

  // unsubscribe inactive users
  $defaultRole = get_option("buyplaytix_membership_none", NULL);
  if ( in_array( $defaultRole, (array) $user->roles ) ) {
    error_log("unsubscribe");
    return false;
  }
  $oauth = new BuyPlayTix\Wordpress\OAuth();
  $subscriptions = $oauth->getSubscriptions($user);
  foreach($subscriptions as $subscription) {
    $fullKey = "buyplaytix_membership_" . $subscription->MEMBERSHIP->UID;
    $roleId = get_option($fullKey, NULL);
    if (in_array( $roleId, (array) $user->roles ) ) {
      error_log("subscribe");
      return true;
    }
  }
  // otherwise check if they have any of these roles manually selected
  $memberships = $oauth->getMemberships();
  foreach($memberships as $membership) {
    $fullKey = "buyplaytix_membership_" . $membership->UID;
    $roleId = get_option($fullKey, NULL);
    if (in_array( $roleId, (array) $user->roles ) ) {
      error_log("subscribe");
      return true;
    }
  }
  error_log("unsubscribe");
  return false;
}

function extra_profile_fields( $user ) {
  $oauth = new BuyPlayTix\Wordpress\OAuth();
  $subscriptions = $oauth->getSubscriptions($user);
  ?>
  <h2>Membership Subscription</h2>
  <?php
}

function membership_export($user) {
    error_log($user->user_email);
    $defaultRole = get_option("buyplaytix_membership_none", NULL);
    if ( in_array( $defaultRole, (array) $user->roles ) ) {
        // do we want to do something here or trust BuyPlayTix?
    }

    $oauth = new BuyPlayTix\Wordpress\OAuth();
    $memberships = $oauth->getMemberships();
    $subscriptions = $oauth->getSubscriptions($user);
    foreach($memberships as $membership) {
        $fullKey = "buyplaytix_membership_" . $membership->UID;
        $roleId = get_option($fullKey, NULL);
        if (in_array( $roleId, (array) $user->roles ) ) {
            $create = true;
            foreach($subscriptions as $subscription) {
                if ($subscription->MEMBERSHIP_UID === $membership->UID) {
                    $create = false;
                }
            }
            if ($create) {
                error_log("create " . $membership->NAME);
                $response = $oauth->createSubscription($user, $membership);
                error_log(var_export($response, true));
                return $response->code === 200;
            }
        }
    }
    error_log("skip");
    return true;
}

function membership_export_batch_process() {
    register_batch_process( array(
        'name'     => 'Export Memberships to BuyPlayTix',
        'type'     => 'user',
        'callback' => 'membership_export',
        'args'     => [],
    ) );
}


add_action('widgets_init', 'buyplaytix_widgets');
add_action('admin_init', array('BuyPlayTix\Wordpress\Plugin', 'admin_init'));
add_action('admin_menu', array('BuyPlayTix\Wordpress\Plugin', 'admin_menu'));
add_action('admin_notices', array('BuyPlayTix\Wordpress\Plugin', 'admin_notices'));
add_action('add_meta_boxes', array('BuyPlayTix\WordPress\Plugin', 'register_production_box'));
add_action('save_post', array('BuyPlayTix\WordPress\Plugin', 'production_box_save'));

add_filter('plugin_action_links', array('BuyPlayTix\WordPress\Plugin', 'settings_link'), 10, 2);
add_filter('authenticate', 'buyplaytix_membership', 10, 3);

add_filter('mailchimp_sync_should_sync_user', 'buyplaytix_mailchimp_sync', 10, 2);
add_action( 'show_user_profile', 'extra_profile_fields', 10, 1);
add_action( 'edit_user_profile', 'extra_profile_fields', 10, 1);

add_action( 'locomotive_init', 'membership_export_batch_process' );

add_shortcode('bpt_calendar', array('BuyPlayTix\Wordpress\Plugin', 'calendar_shorttag'));
add_shortcode('bpt_tickets', array('BuyPlayTix\Wordpress\Plugin', 'tickets_shorttag'));
add_shortcode('bpt_donate', array('BuyPlayTix\Wordpress\Plugin', 'donate_shorttag'));
add_shortcode('bpt_minical', array('BuyPlayTix\Wordpress\Plugin', 'minical_shorttag'));
add_shortcode('bpt_when', array('BuyPlayTix\Wordpress\Plugin', 'when_shorttag'));
add_shortcode('bpt_logo', array('BuyPlayTix\Wordpress\Plugin', 'logo_shorttag'));
add_shortcode('bpt_additional_info', array('BuyPlayTix\Wordpress\Plugin', 'additional_info_shorttag'));
add_shortcode('bpt_location', array('BuyPlayTix\Wordpress\Plugin', 'location_shorttag'));
add_shortcode('bpt_history', array('BuyPlayTix\Wordpress\Plugin', 'history_shorttag'));








<?php
namespace BuyPlayTix\Wordpress;
class OAuth {

  // public settings
  public static $BASE_PATH = "https://secure.buyplaytix.com/";
  private static $KEY = "aefb672d3a4c3abff16be5d51fe4d72db312280d";
  private static $SECRET = "7be1ca0b1f02863352d89e091c025d00ab5e67cf";

  // test mode settings
//  public static $BASE_PATH = "https://secure.buyplaytix.com/bpt/";
//  private static $KEY = "71270885d0a5c7f36089dc2e3b04f95d0cf5449e";
//  private static $SECRET = "7f746ab35f341b1e80bfef8855c63d4e8889bc1a";



  public static function oauth_query_vars() {
    return array("action", "oauth_token", "oauth_verifier");
  }

  public static function isConfigured() {
    $oauth_token = get_option("buyplaytix_token", NULL);
    $oauth_secret = get_option("buyplaytix_secret", NULL);
    if(empty($oauth_token) || empty($oauth_secret)) {
      return false;
    }
    return true;
  }
  
  public function getProducer() {
    if(!OAuth::isConfigured()) {
      return array();
    }
    
    $consumer = $this->getConsumer();
    $response = $consumer->sendRequest(OAuth::$BASE_PATH . "api/1.0/producer/me", array(), 'GET');
    $body = $response->getResponse()->getBody();
    $response = json_decode($body);
    return $response->response;    
  }

  public function getProduction($url_name) {
    if(empty($url_name)) {
      return NULL;
    }
    $consumer = $this->getConsumer();
    $response = $consumer->sendRequest(OAuth::$BASE_PATH . "api/1.0/production/" . $url_name, array(), 'GET');
    $body = $response->getResponse()->getBody();
    $response = json_decode($body);
    if(is_array($response->response)) {
      return $response->response[0];
    }
    return $response->response;
  }

  public function getLocation($url_name) {
    if(empty($url_name)) {
      return NULL;
    }
    $consumer = $this->getConsumer();
    $response = $consumer->sendRequest(OAuth::$BASE_PATH . "api/1.0/production/" . $url_name . "/location", array(), 'GET');
    $body = $response->getResponse()->getBody();
    $response = json_decode($body);
    return $response->response;
  }

  public function getHistory() {
    $consumer = $this->getConsumer();
    $response = $consumer->sendRequest(OAuth::$BASE_PATH . "api/1.0/production/history", array(), 'GET');
    $body = $response->getResponse()->getBody();
    $response = json_decode($body);
    return $response->response;
  }

  
  public function getPeople($url_name) {
    if(empty($url_name)) {
      return NULL;
    }
    $consumer = $this->getConsumer();
    $response = $consumer->sendRequest(OAuth::$BASE_PATH . "api/1.0/production/" . $url_name . "/people", array(), 'GET');
    $body = $response->getResponse()->getBody();
    $response = json_decode($body);
    return $response->response;
  }

  public function getShows($url_name) {
    if(empty($url_name)) {
      return NULL;
    }
    $consumer = $this->getConsumer();
    $response = $consumer->sendRequest(OAuth::$BASE_PATH . "api/1.0/show/" . $url_name . "/", array(), 'GET');
    $body = $response->getResponse()->getBody();
    $response = json_decode($body);
    return $response->response;
  }


  public function getProductions($all = false) {
    if(!OAuth::isConfigured()) {
      return array();
    }
    
    $consumer = $this->getConsumer();
    $response = $consumer->sendRequest(OAuth::$BASE_PATH . "api/1.0/production/", array("all" => $all), 'GET');
    $body = $response->getResponse()->getBody();
    $response = json_decode($body);
    return $response->response;
  }
  
  public function linkProductions($production_url, $link) {
    if(!OAuth::isConfigured()) {
      return;
    }
  
    $consumer = $this->getConsumer();
    $response = $consumer->sendRequest(OAuth::$BASE_PATH . "api/1.0/production/" . $production_url . "/link", array(
        "link" => $link
    ), 'POST');
    $body = $response->getResponse()->getBody();
    $response = json_decode($body);
    return $response;
  }

  public function getSubscriptions($user) {
    if(!OAuth::isConfigured()) {
      return [];
    }
    $consumer = $this->getConsumer();
    $response = $consumer->sendRequest(OAuth::$BASE_PATH . "api/1.0/user/" . urlencode($user->user_email) . "/subscriptions", [], 'GET');
    $body = $response->getResponse()->getBody();
    $decoded = json_decode($body);
    if ($decoded->code !== "200") {
      return [];
    }
    return $decoded->response;
  }

  public function getMemberships() {
    if(!OAuth::isConfigured()) {
      return [];
    }
    $consumer = $this->getConsumer();
    $response = $consumer->sendRequest(OAuth::$BASE_PATH . "api/1.0/membership/", [], 'GET');
    $body = $response->getResponse()->getBody();
      $decoded = json_decode($body);
    return $decoded->response;
  }

  public function createSubscription($user, $membership, $createdDate = null) {

      $membership_level = pmpro_getMembershipLevelForUser($user->ID);
      $params = [
          "first_name" => $user->first_name,
          "last_name" => $user->last_name,
          "email" => $user->user_email,
          "paid" => "paid", // is this correct?
          "quantity" => 1,
          // FIXME: this cannot be null. Should be pulled from PMP
          // "created_date" => $createdDate,
      ];
      if ($membership_level) {
          $params["created_date"] = date("Y-m-d", $membership_level->startdate);
      }

      $consumer = $this->getConsumer();
      $response = $consumer->sendRequest(OAuth::$BASE_PATH . "api/1.0/membership/" . $membership->UID . "/subscriber", $params, 'POST');
      $body = $response->getResponse()->getBody();
      $response = json_decode($body);
      return $response;
  }
  

  public function finish() {

    try {
      $request_token = get_option("buyplaytix_request_token", NULL);
      $request_secret = get_option("buyplaytix_request_secret", NULL);
      if(empty($request_token) || empty($request_secret)) {
        return;
      }
      $consumer = new \HTTP_OAuth_Consumer(OAuth::$KEY, OAuth::$SECRET,
          get_option("buyplaytix_request_token", NULL),
          get_option("buyplaytix_request_secret", NULL));
      $this->fixRequest($consumer);
      $consumer->getAccessToken(OAuth::$BASE_PATH . "oauth_access_token", $_REQUEST["oauth_verifier"]);

      update_option("buyplaytix_token", $consumer->getToken());
      update_option("buyplaytix_secret", $consumer->getTokenSecret());
      update_option("buyplaytix_request_token", NULL);
      update_option("buyplaytix_request_secret", NULL);      
    } catch(\Exception $e) {
      print $e;
    }


  }

  public function signin($redirect) {

    try {
      $consumer = new \HTTP_OAuth_Consumer(OAuth::$KEY, OAuth::$SECRET);
      $this->fixRequest($consumer);
      $request_token_info = $consumer->getRequestToken(OAuth::$BASE_PATH . 'oauth_request_token', $redirect);
      update_option("buyplaytix_request_token", $consumer->getToken());
      update_option("buyplaytix_request_secret", $consumer->getTokenSecret());
      $url = $consumer->getAuthorizeUrl(OAuth::$BASE_PATH . 'oauth_signin');
      wp_redirect($url);
      exit;
    } catch(\Exception $e) {
      print $e;
    }
  }


  private function getConsumer() {
    $consumer = new \HTTP_OAuth_Consumer(OAuth::$KEY, OAuth::$SECRET, 
        get_option("buyplaytix_token", NULL),
        get_option("buyplaytix_secret", NULL));
    $this->fixRequest($consumer);
    return $consumer;
  }

  private function fixRequest($consumer) {
    $request = $consumer->getOAuthConsumerRequest();
    $config = $request->getConfig();
    $config["ssl_verify_peer"] = false;
    $request->setConfig($config);
  }
}

<?php

namespace Drupal\oauth_login_oauth2;

/**
 * Handles access token request to OAuth Provider.
 */
class AccessToken {

  /**
   * This function gets the access token from OAuth server.
   *
   * @return string
   *   Returns access token.
   */
  public static function getAccessToken($tokenendpoint, $grant_type, $clientid, $clientsecret, $code, $redirect_url, $send_headers, $send_body) {
    Utilities::addLogger(basename(__FILE__), __FUNCTION__, __LINE__, 'Access Token flow initiated.');
    if ($send_headers && !$send_body) {
      $response = Utilities::callService($tokenendpoint,
            'redirect_uri=' . urlencode($redirect_url) . '&grant_type=' . $grant_type . '&code=' . $code,
            [
              'Authorization' => 'Basic ' . base64_encode($clientid . ":" . $clientsecret),
              'Accept' => 'application/json',
              'Content-Type' => 'application/x-www-form-urlencoded',
            ]
        );
    }elseif (!$send_headers && $send_body) {
      $response = Utilities::callService($tokenendpoint,
            'redirect_uri=' . urlencode($redirect_url) . '&grant_type=' . $grant_type . '&client_id=' . urlencode($clientid) . '&client_secret=' . urlencode($clientsecret) . '&code=' . $code,
            [
              'Accept' => 'application/json',
              'Content-Type' => 'application/x-www-form-urlencoded',
            ]
            );
    }else {
      $response = Utilities::callService($tokenendpoint,
            'redirect_uri=' . urlencode($redirect_url) . '&grant_type=' . $grant_type . '&client_id=' . urlencode($clientid) . '&client_secret=' . urlencode($clientsecret) . '&code=' . $code,
            [
              'Authorization' => 'Basic ' . base64_encode($clientid . ":" . $clientsecret),
              'Accept' => 'application/json',
              'Content-Type' => 'application/x-www-form-urlencoded',
            ]
            );
    }

    $content = json_decode($response, TRUE);
    Utilities::addLogger(basename(__FILE__), __FUNCTION__, __LINE__, 'Access Token Content: <pre><code>' . print_r($content, TRUE) . '</code></pre>');
    if (isset($content["error"]) || isset($content["error_description"])) {
      if (isset($content["error"]) && is_array($content["error"])) {
        $content["error"] = $content["error"]["message"];
      }
      Utilities::showErrorMessage($content);
    }elseif (isset($content["access_token"]) && !empty($content["access_token"])) {
      $access_token = $content["access_token"];
    }else {
      exit('Invalid response received from OAuth Provider. Contact your administrator for more details.');
    }
    return $access_token;
  }

}

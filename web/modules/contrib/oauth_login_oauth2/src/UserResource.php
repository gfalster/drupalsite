<?php

namespace Drupal\oauth_login_oauth2;

/**
 * Class for getting userinfo from userinfo endpoint.
 */
class UserResource {

  /**
   * Makes api call to userinfo endpoint with access token to get user information.
   *
   * @param string $resource_owner_details_url
   *   The userinfo endpoint.
   * @param string $access_token
   *   The accesstoken.
   *
   * @return array
   *   Returns user info array.
   */
  public static function getResourceOwner($resource_owner_details_url, $access_token) {
    Utilities::addLogger(basename(__FILE__), __FUNCTION__, __LINE__, 'Userinfo flow initiated.');

    $response = Utilities::callService($resource_owner_details_url,
          NULL,
          ['Authorization' => 'Bearer ' . $access_token],
          'GET'
      );

    if (isset($response) && !empty($response)) {
      $content = json_decode($response, TRUE);
      Utilities::addLogger(basename(__FILE__), __FUNCTION__, __LINE__, 'Userinfo Content: <pre><code>' . print_r($content, TRUE) . '</code></pre>');
      if (isset($content["error"]) || isset($content["error_description"])) {
        if (isset($content["error"]) && is_array($content["error"])) {
          $content["error"] = $content["error"]["message"];
        }
        Utilities::showErrorMessage($content);
      }
      return $content;
    }
    return NULL;
  }

}

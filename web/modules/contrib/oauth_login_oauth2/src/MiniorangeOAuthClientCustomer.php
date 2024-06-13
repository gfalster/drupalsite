<?php

namespace Drupal\oauth_login_oauth2;

/**
 * Class for handling registration of new customer.
 */
class MiniorangeOAuthClientCustomer {

  /**
   * The email of user.
   *
   * @var string
   */
  public $email;

  /**
   * The phone of user.
   *
   * @var string
   */
  public $phone;

  /**
   * The password.
   *
   * @var string
   */
  public $password;

  /**
   * The otp token.
   *
   * @var string
   */
  public $otpToken;

  /**
   * The default customer-id.
   *
   * @var string
   */
  private $defaultCustomerId;

  /**
   * The default customer api-key.
   *
   * @var string
   */
  private $defaultCustomerApiKey;

  /**
   * Constructs a new MiniorangeOAuthClientCustomer object.
   *
   * @param string $email
   *   The email of user.
   * @param string $phone
   *   The phone of user.
   * @param string $password
   *   The password of user.
   * @param string $otp_token
   *   The otp token of user.
   */
  public function __construct($email, $phone, $password, $otp_token) {
    $this->email = $email;
    $this->phone = $phone;
    $this->password = $password;
    $this->otpToken = $otp_token;
    $this->defaultCustomerId = "16555";
    $this->defaultCustomerApiKey = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
  }

  /**
   * Check if customer exists.
   *
   * @return string
   *   Returns api call response.
   */
  public function checkCustomer() {
    if (!Utilities::isCurlInstalled()) {
      return json_encode([
        "status" => 'CURL_ERROR',
        "statusMessage" => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.',
      ]);
    }

    $url = MiniorangeOAuthClientConstants::BASE_URL . '/moas/rest/customer/check-if-exists';
    $ch = curl_init($url);
    $email = $this->email;

    $fields = [
      'email' => $email,
    ];
    $field_string = json_encode($fields);

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_ENCODING, "");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json', 'charset: UTF - 8',
      'Authorization: Basic',
    ]);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
    $content = curl_exec($ch);
    if (curl_errno($ch)) {
      $error = [
        '%method' => 'checkCustomer',
        '%file' => 'customer_setup.php',
        '%error' => curl_error($ch),
      ];
      \Drupal::logger('oauth_login_oauth2')->notice($error);
    }
    curl_close($ch);

    return $content;
  }

  /**
   * Creates customer.
   *
   * @return string
   *   Returns api call response.
   */
  public function createCustomer() {
    if (!Utilities::isCurlInstalled()) {
      return json_encode([
        "statusCode" => 'ERROR',
        "statusMessage" => '. Please check your configuration.',
      ]);
    }
    $url = MiniorangeOAuthClientConstants::BASE_URL . '/moas/rest/customer/add';
    $ch = curl_init($url);
    $fields = [
      'companyName' => $_SERVER['SERVER_NAME'],
      'areaOfInterest' => 'DRUPAL ' . Utilities::moGetDrupalCoreVersion() . ' OAuth Login',
      'email' => $this->email,
      'phone' => $this->phone,
      'password' => $this->password,
    ];
    $field_string = json_encode($fields);

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_ENCODING, "");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json',
      'charset: UTF - 8',
      'Authorization: Basic',
    ]);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
    $content = curl_exec($ch);

    if (curl_errno($ch)) {
      $error = [
        '%method' => 'createCustomer',
        '%file' => 'customer_setup.php',
        '%error' => curl_error($ch),
      ];
      \Drupal::logger('oauth_login_oauth2')->notice($error);
    }
    curl_close($ch);
    return $content;
  }

  /**
   * Gets customer keys..
   *
   * @return string
   *   Returns api call response.
   */
  public function getCustomerKeys() {
    if (!Utilities::isCurlInstalled()) {
      return json_encode([
        "apiKey" => 'CURL_ERROR',
        "token" => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.',
      ]);
    }

    $url = MiniorangeOAuthClientConstants::BASE_URL . '/moas/rest/customer/key';
    $ch = curl_init($url);
    $email = $this->email;
    $password = $this->password;

    $fields = [
      'email' => $email,
      'password' => $password,
    ];
    $field_string = json_encode($fields);

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_ENCODING, "");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json',
      'charset: UTF - 8',
      'Authorization: Basic',
    ]);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
    $content = curl_exec($ch);
    if (curl_errno($ch)) {
      $error = [
        '%method' => 'getCustomerKeys',
        '%file' => 'customer_setup.php',
        '%error' => curl_error($ch),
      ];
      \Drupal::logger('oauth_login_oauth2')->notice($error);
    }
    curl_close($ch);

    return $content;
  }

  /**
   * Sends otp.
   *
   * @return string
   *   Returns api call response.
   */
  public function sendOtp() {
    if (!Utilities::isCurlInstalled()) {
      return json_encode([
        "status" => 'CURL_ERROR',
        "statusMessage" => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.',
      ]);
    }
    $url = MiniorangeOAuthClientConstants::BASE_URL . '/moas/api/auth/challenge';
    $ch = curl_init($url);
    $customer_key = $this->defaultCustomerId;
    $api_key = $this->defaultCustomerApiKey;

    $username = \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_customer_admin_email');

    /* Current time in milliseconds since midnight, January 1, 1970 UTC. */
    $current_time_in_millis = round(microtime(TRUE) * 1000);

    /* Creating the Hash using SHA-512 algorithm */
    $string_to_hash = $customer_key . $current_time_in_millis . $api_key;
    $hash_value = hash("sha512", $string_to_hash);

    $customer_key_header = "Customer-Key: " . $customer_key;
    $timestamp_header = "Timestamp: " . $current_time_in_millis;
    $authorization_header = "Authorization: " . $hash_value;

    $fields = [
      'customerKey' => $customer_key,
      'email' => $username,
      'authType' => 'EMAIL',
    ];
    $field_string = json_encode($fields);

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_ENCODING, "");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', $customer_key_header,
      $timestamp_header, $authorization_header,
    ]);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
    $content = curl_exec($ch);

    if (curl_errno($ch)) {
      $error = [
        '%method' => 'sendOtp',
        '%file' => 'customer_setup.php',
        '%error' => curl_error($ch),
      ];
      \Drupal::logger('oauth_login_oauth2')->notice($error);
    }
    curl_close($ch);
    return $content;
  }

  /**
   * Validates otp.
   *
   * @return string
   *   Returns api call response.
   */
  public function validateOtp($transaction_id) {
    if (!Utilities::isCurlInstalled()) {
      return json_encode([
        "status" => 'CURL_ERROR',
        "statusMessage" => '<a href="http://php.net/manual/en/curl.installation.php">PHP cURL extension</a> is not installed or disabled.',
      ]);
    }

    $url = MiniorangeOAuthClientConstants::BASE_URL . '/moas/api/auth/validate';
    $ch = curl_init($url);

    $customer_key = $this->defaultCustomerId;
    $api_key = $this->defaultCustomerApiKey;

    $current_time_in_millis = round(microtime(TRUE) * 1000);

    $string_to_hash = $customer_key . $current_time_in_millis . $api_key;
    $hash_value = hash("sha512", $string_to_hash);

    $customer_key_header = "Customer-Key: " . $customer_key;
    $timestamp_header = "Timestamp: " . $current_time_in_millis;
    $authorization_header = "Authorization: " . $hash_value;

    $fields = [
      'txId' => $transaction_id,
      'token' => $this->otpToken,
    ];

    $field_string = json_encode($fields);

    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
    curl_setopt($ch, CURLOPT_ENCODING, "");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', $customer_key_header,
      $timestamp_header, $authorization_header,
    ]);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $field_string);
    $content = curl_exec($ch);

    if (curl_errno($ch)) {
      $error = [
        '%method' => 'validateOtp',
        '%file' => 'customer_setup.php',
        '%error' => curl_error($ch),
      ];
      \Drupal::logger('oauth_login_oauth2')->notice($error);
    }
    curl_close($ch);
    return $content;
  }

}

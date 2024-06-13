<?php

namespace Drupal\oauth_login_oauth2;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class for handling utility functions in project.
 */
class Utilities {

  /**
   * Displays setup call form.
   *
   * @param array $form
   *   The form elements array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The formstate.
   */
  public static function scheduleMeeting(&$form, &$form_state) {
    $form['miniorange_oauth_client_setup_guide_link'] = [
      '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container_2">',
    ];

    $form['heading_setupcall'] = [
      '#markup' => '<h3>Setup a Call / Screen-share session with miniOrange Technical Team</h3><hr><br>',
    ];

    $timezone = [];

    foreach (Utilities::$zones as $key => $value) {
      $timezone[$value] = $key;
    }

    $form['miniorange_oauth_client_timezone'] = [
      '#type' => 'select',
      '#title' => t('Select Timezone'),
      '#options' => $timezone,
      '#default_value' => 'Etc/GMT',
    ];

    $form['miniorange_oauth_client_meeting_time'] = [
      '#type' => 'datetime',
      '#title' => 'Date and Time',
      '#default_value' => DrupalDateTime::createFromTimestamp(time()),
      '#format' => '',
    ];

    $form['mo_schedule_call_email'] = [
      '#type' => 'textfield',
      '#title' => t('Email ID'),
      '#default_value' => \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_customer_admin_email'),
      '#attributes' => ['style' => 'width:100%', 'placeholder' => 'Enter your Email ID'],
    ];

    $form['mo_schedule_call_description'] = [
      '#type' => 'textarea',
      '#title' => t('How may we help you?'),
      '#cols' => '10',
      '#rows' => '3',
      '#attributes' => ['style' => 'width:100%', 'placeholder' => 'Write your query here'],
    ];
    $form['markup_div1'] = [
      '#markup' => '<div> <p class="setup_call">Meeting details will be sent to your email. Please verify the email before submitting the meeting request.</p>',
    ];

    $form['miniorange_oauth_support_submit_click1'] = [
      '#type' => 'submit',
      '#value' => t('Setup a Call'),
      '#submit' => ['::setup_call'],
      '#limit_validation_errors' => [],
      '#attributes' => ['style' => 'background: #337ab7;color: #ffffff;text-shadow: 0 -1px 1px #337ab7, 1px 0 1px #337ab7, 0 1px 1px #337ab7, -1px 0 1px #337ab7;box-shadow: 0 1px 0 #337ab7;border-color: #337ab7 #337ab7 #337ab7;display:block;margin: auto;'],
    ];

    $form['markup_div_end1'] = [
      '#markup' => '</div>',
    ];

    $form['miniorange_oauth_support_div_cust1'] = [
      '#markup' => '</div></div>',
    ];
  }

  /**
   * Sends support query.
   *
   * @param string $email
   *   The email of user.
   * @param string $phone
   *   The phone of user.
   * @param string $query
   *   The query of user.
   * @param string $query_type
   *   The query type of user.
   */
  public static function sendSupportQuery($email, $phone, $query, $query_type) {
    $support = new MiniorangeOAuthClientSupport($email, $phone, $query, $query_type);
    $support_response = json_decode($support->sendSupportQuery(), TRUE);

    if (isset($support_response['status']) && $support_response['status'] == "SUCCESS") {
      \Drupal::messenger()->addMessage(t('Support query successfully sent. We will get back to you shortly.'));
    }
    else {
      \Drupal::messenger()->addMessage(t('Error sending support query. Please reach out to <a href="mailto:drupalsupport@xecurify.com">drupalsupport@xecurify.com</a>'), 'error');
    }
  }

  /**
   * Sends setup call request.
   *
   * @param array $form
   *   The form elements array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The formstate.
   */
  public static function scheduleCall(array &$form, FormStateInterface $form_state) {

    $timezone = $form['miniorange_oauth_client_timezone']['#value'];
    $mo_timezone = [];

    foreach (Utilities::$zones as $key => $value) {
      $mo_timezone[$value] = $key;
    }

    $mo_date = $form['miniorange_oauth_client_meeting_time']['#value']['date'];
    $mo_time = $form['miniorange_oauth_client_meeting_time']['#value']['time'];
    $email = trim($form['mo_schedule_call_email']['#value']);
    $query = trim($form['mo_schedule_call_description']['#value']);

    if (empty($email)||empty($query)) {
      \Drupal::messenger()->addMessage(t('The <b><u>Email</u></b> and <b><u>Query</u></b> fields are mandatory.'), 'error');
      return;
    }
    elseif (!\Drupal::service('email.validator')->isValid($email)) {
      \Drupal::messenger()->addMessage(t('The email address <b><i>' . $email . '</i></b> is not valid.'), 'error');
      return;
    }

    $support = new MiniorangeOAuthClientSupport($email, '', $query, 'Call Request', $mo_timezone[$timezone], $mo_date, $mo_time);
    $support_response = json_decode($support->sendSupportQuery(), TRUE);

    if (isset($support_response['status']) && $support_response['status'] == "SUCCESS") {
      \Drupal::messenger()->addMessage(t('Meeting request successfully sent. We will get back to you shortly.'));
    }
    else {
      \Drupal::messenger()->addMessage(t('Error sending Meeting request. Please reach out to <a href="mailto:drupalsupport@xecurify.com">drupalsupport@xecurify.com</a>'), 'error');
    }
  }

  /**
   * Makes api request to respective endpoint.
   *
   * @param string $url
   *   The endpoint where we make api request.
   * @param array $fields
   *   The api request body.
   * @param array $header
   *   The api request header.
   * @param string $get_post
   *   The method of api request.
   * @param bool $logError
   *   The boolean to log error.
   *
   * @return string|null
   *   Returns api call response.
   */
  public static function callService($url, $fields, $header = FALSE, $get_post = '', $logError = TRUE) {
    if (!Utilities::isCurlInstalled()){
      return json_encode([
        "statusCode" => 'ERROR',
        "statusMessage" => 'cURL is not enabled on your site. Please enable the cURL module.',
      ]);
    }
    $fieldString = is_string($fields) ? $fields : json_encode($fields);
    if ($get_post == 'GET') {
      try {
        $response = \Drupal::httpClient()
          ->get($url, [
            'headers' => $header,
            'verify' => FALSE,
          ]);
        return $response->getBody();
      }catch (\Exception $exception) {
        if ($logError) {
          $error = [
            '%error' => $exception->getMessage(),
          ];
          if (isset($_COOKIE['Drupal_visitor_mo_oauth_test']) && ($_COOKIE['Drupal_visitor_mo_oauth_test'] == TRUE)) {
            \Drupal::logger('oauth_login_oauth2')->notice('Error:  %error', $error);
            self::showErrorMessage($error);
          }else {
            \Drupal::logger('oauth_login_oauth2')->notice('Error:  %error', $error);
            $response = new RedirectResponse(Url::fromRoute('user.login')->toString());
            $response->send();
            \Drupal::messenger()->addError(t('Something went wrong, Please contact your administrator'));
            exit;
          }
        }
      }
    }else {
      try {
        $response = \Drupal::httpClient()
          ->post($url, [
            'body' => $fieldString,
            'allow_redirects' => TRUE,
            'http_errors' => FALSE,
            'decode_content' => TRUE,
            'verify' => FALSE,
            'headers' => $header,
          ]);
        return $response->getBody()->getContents();
      }catch (\Exception $exception) {
        if ($logError) {
          $error = [
            '%error' => $exception->getMessage(),
          ];
          if (isset($_COOKIE['Drupal_visitor_mo_oauth_test']) && ($_COOKIE['Drupal_visitor_mo_oauth_test'] == TRUE)) {
            \Drupal::logger('oauth_login_oauth2')->notice('Error:  %error', $error);
            self::showErrorMessage($error);
          }else {
            \Drupal::logger('oauth_login_oauth2')->notice('Error:  %error', $error);
            $response = new RedirectResponse(Url::fromRoute('user.login')->toString());
            $response->send();
            \Drupal::messenger()->addError(t('Something went wrong, Please contact your administrator'));
            exit;
          }
        }
      }
    }
    return NULL;
  }


  /**
   * Creates array OAuth Client Module features.
   *
   * @return array
   *   Returns array of features.
   */
  public static function getOAuthFeaturelist(){
    $feature_list = [
      "Auto Create user in Drupal" => "Auto Create user in Drupal",
      "OpenID Connect" => "OpenID Connect",
      "Multiple OAuth Providers" => "Multiple OAuth Providers",
      "Advanced Attribute Mapping" => "Advanced Attribute Mapping",
      "Advanced Role Mapping" => "Advanced Role Mapping",
      "Profile Mapping" => "Profile Mapping",
      "Role based Restriction" => "Role based Restriction",
      "Custom Redirect after Login and Logout" => "Custom Redirect after Login and Logout",
      "Page Restriction" => "Page Restriction",
      "Domain Restriction" => "Domain Restriction",
      "Replace Drupal Login Page with OAuth Server Login Page" => "Replace Drupal Login Page with OAuth Server Login Page",
      "Login Reports and Analytics" => "Login Reports and Analytics"
    ];
    return $feature_list;
  }

  /**
  * Sends mail to user on skipping feedback.
  */
  public static function miniorangeOauthLoginSkipFeedback($modules_version, $email) {
    $url         = MiniorangeOAuthClientConstants::BASE_URL . '/moas/api/notify/send';
    $config      = \Drupal::config('oauth_login_oauth2.settings');
    $customerKey = $config->get('miniorange_oauth_client_customer_id');
    $apikey      = $config->get('miniorange_oauth_client_customer_api_key');

    if ($customerKey == '') {
      $customerKey = "16555";
      $apikey      = "fFd2XcvTGDemZvbw1bcUesNJWEqKbbUq";
    }
    $currentTimeInMillis = Utilities::getOauthTimestamp();
    $stringToHash        = $customerKey . $currentTimeInMillis . $apikey;
    $hashValue           = hash("sha512", $stringToHash);
    $fromEmail           = 'no-reply@xecurify.com';
    $subject             = 'Regarding Drupal OAuth Login Module - ' . $modules_version;

    $content =
      '<div>Hello there,
            <br><br>Thank you for your interest in our miniOrange Drupal OAuth/OIDC Login module. We hope you find it useful and easy to configure.
            <br><br>We wanted to follow up and check if you were able to successfully set up our module with your desired OAuth/OIDC Server. If you encountered any issues or have any questions, please feel free to reach out to us at <a href="mailto:' . MiniorangeOAuthClientConstants::SUPPORT_EMAIL . '" target="_blank">' . MiniorangeOAuthClientConstants::SUPPORT_EMAIL . '</a>. Our team is always available to assist you, and we can also set up an online meeting to troubleshoot any problems you may be experiencing.
            <br><br>We would also appreciate it if you could provide us with some more information about your use case so that we can provide a solution that meets your specific requirements.
            <br><br>If you have any questions or need any assistance, please don\'t hesitate to contact us. We are excited to help you simplify your Single Sign-On use case requirements with our module.
            <br><br>Thanks and Regards,
            <br>miniOrange Drupal team
            <br><br><b>Note: </b>This is an auto-generated mail. Please do not reply to this email. If you need any assistance or have any questions, please contact us at <a href="mailto:' . MiniorangeOAuthClientConstants::SUPPORT_EMAIL . '" target="_blank">' . MiniorangeOAuthClientConstants::SUPPORT_EMAIL . '</a>.';

    $fields = [
      'customerKey'    => $customerKey,
      'sendEmail'     => TRUE,
      'email'           => [
        'customerKey'   => $customerKey,
        'fromEmail'     => $fromEmail,
        'fromName'           => 'miniOrange',
        'toEmail'            => $email,
        'toName'             => $email,
        'subject'           => $subject,
        'content'           => $content,
      ],
    ];

    $field_string = json_encode($fields);

    $response = self::callService($url,
      $field_string,
      [
        'Content-Type' => 'application/json',
        'Customer-Key' => $customerKey,
        'Timestamp' => $currentTimeInMillis,
        'Authorization' => $hashValue,
      ],
      'POST'
    );
  }

  /**
   * Displays error message.
   *
   * @param array $get
   *   The array of errors.
   */
  public static function showErrorMessage($get) {
    echo '<div style="font-family:Calibri;padding:0 3%;">
            <div style="color: #a94442;background-color: #f2dede;padding: 15px;margin-bottom: 20px;text-align:center;border:1px solid #E6B3B2;font-size:18pt;">
            ERROR
            </div><div style="color: #a94442;font-size:14pt; margin-bottom:20px;">';

    foreach ($get as $key => $val) {
      if ($key == 'state') {
        continue;
      }
      echo '<p><strong>' . $key . ': </strong>' . $val . '</p>';
    }
    echo '</div></div>';
    exit;
  }

  /**
   * Creates tabwise config variables array.
   *
   * @param string $class_name
   *   The name of tab.
   *
   * @return array
   *   Returns configuration variables of specified class name.
   */
  public static function getVariableArray($class_name) {

    if ($class_name == "mo_options_enum_client_configuration") {
      $class_object = [
        'App_selected'  => 'miniorange_oauth_client_app',
        'Display_link' => 'miniorange_auth_client_display_name',
        'Client_ID' => 'miniorange_auth_client_client_id',
        'Client_secret' => 'miniorange_auth_client_client_secret',
        'Client_scope' => 'miniorange_auth_client_scope',
        'Authorized_endpoint' => 'miniorange_auth_client_authorize_endpoint',
        'Access_token_endpoint' => 'miniorange_auth_client_access_token_ep',
        'Userinfo_endpoint' => 'miniorange_auth_client_user_info_ep',
        'Callback_url' => 'miniorange_auth_client_callback_uri',
        'credentials_via_header' => 'miniorange_oauth_send_with_header_oauth',
        'credentials_via_body' => 'miniorange_oauth_send_with_body_oauth',
        'Enable_login_with_oauth' => 'miniorange_oauth_enable_login_with_oauth',
      ];
    }
    elseif ($class_name == "mo_options_enum_attribute_mapping") {
      $class_object = [
        'Email_attribute_value'    => 'miniorange_oauth_client_email_attr_val',
      ];
    }
    elseif ($class_name == "mo_options_enum_signin_settings") {
      $class_object = [
        'Base_URL_value'    => 'miniorange_oauth_client_base_url',
      ];
    }
    return $class_object;
  }

  /**
   * Add loogers.
   *
   * @param string $file
   *   The filename.
   * @param string $function
   *   The function name.
   * @param string $line
   *   The line number.
   * @param string $message
   *   The message.
   */
  public static function addLogger($file, $function, $line, $message) {
    if (\Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_enable_logging')) {
      \Drupal::logger('oauth_login_oauth2')->debug(date('d F Y H:i:s', time()) . ' ' . $file . ', ' . $function . ' , ' . $line . ': ' . $message);
    }
  }

  /**
   * This function is used to get the timestamp value.
   */
  public static function getOauthTimestamp() {
    $url = 'https://login.xecurify.com/moas/rest/mobile/get-timestamp';
    $content = Utilities::callService($url, [], []);

    if (empty($content)) {
      $currentTimeInMillis = round(microtime(TRUE) * 1000);
      $currentTimeInMillis = number_format($currentTimeInMillis, 0, '', '');
    }
    return empty($content) ? $currentTimeInMillis : $content;
  }

  /**
   * Checks Drupal version of site.
   *
   * @return string
   *   Returns Drupal version of site.
   */
  public static function moGetDrupalCoreVersion() {
    $drupal_version = explode(".", \DRUPAL::VERSION);
    return $drupal_version[0];
  }

  /**
   * Checks base_url of site.
   *
   * @return string
   *   Returns base url of site.
   */
  public static function getOAuthBaseURL($base_url) {
    if (!empty(\Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_base_url'))) {
      $baseUrlValue = \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_base_url');
    }
    else {
      $baseUrlValue = $base_url;
    }

    return $baseUrlValue;
  }

  /**
   * Checks curl extension is installed or not.
   *
   * @return int
   *   Return 1 if curl is installed else false.
   */
  public static function isCurlInstalled() {
    if (in_array('curl', get_loaded_extensions())) {
      return 1;
    }
    else {
      return 0;
    }
  }

  /**
   * Checks if module is getting uninstallled on cli.
   *
   * @return bool
   *   Returns true if cli else false
   */
  public static function drupalIsCli() {
    $server = \Drupal::request()->server;
    $server_software = $server->get('SERVER_SOFTWARE');
    $server_argc = $server->get('argc');

    if (!isset($server_software) && (php_sapi_name() == 'cli' || (is_numeric($server_argc) && $server_argc > 0))) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Checks if customer is registered.
   *
   * @return bool
   *   Returns true if registred else false.
   */
  public static function isCustomerRegistered() {
    if (
          empty(\Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_customer_admin_email'))||
          empty(\Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_customer_id')) ||
          empty(\Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_customer_admin_token')) ||
          empty(\Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_customer_api_key'))) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Show attribute list coming from server on Attribute Mapping tab.
   *
   * @param array $form
   *   The form elements array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The formstate.
   */
  public static function showAttrListFromIdp(&$form, $form_state) {
    $server_attrs = \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_show_attr_list_from_server');
    $application_name = \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_login_config_application');
    $server_attrs = isset($server_attrs) && !empty($server_attrs) ? json_decode($server_attrs, TRUE) : '';

    if (empty($server_attrs)) {
      Utilities::nofeaturelisted($form, $form_state);
      return;
    }

    $form['miniorange_idp_guide_link'] = [
      '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container_2" id="mo_oauth_guide_vt">',
    ];

    $form['miniorange_saml_attr_header'] = [
      '#markup' => '<div class="mo_attr_table">Attributes received from ' . ucfirst($application_name) . ' :</div><br>',
    ];

    $icnt = count($server_attrs);
    if ($icnt >= 8) {
      $scrollkit = 'scrollit';
    }
    else {
      $scrollkit = '';
    }

    $form['mo_saml_attrs_list_idp'] = [
      '#markup' => '<div class="table-responsive mo_guide_text-center" style="font-family: sans-serif;font-size: 12px;">
                        <div class="' . $scrollkit . ' mo-apps-attribute-list">
                <table class="mo_guide_table mo_guide_table-striped mo_guide_table-bordered" style="border: 1px solid #ddd;max-width: 100%;border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th class="mo_guide_text-center mo_td_values">ATTRIBUTE NAME</th>
                            <th class="mo_guide_text-center mo_td_values">ATTRIBUTE VALUE</th>
                        </tr>
                              </thead>',
    ];

    $someattrs = '';
    self::showAttr($server_attrs, $someattrs);

    $form['miniorange_saml_guide_table_list'] = [
      '#markup' => '<tbody style="font-weight:bold;font-size: 12px;color:gray;">' . $someattrs . '</tbody></table></div>',
    ];

    $form['miniorange_break'] = [
      '#markup' => '<br>',
    ];

    $form['miniorange_saml_clear_attr_list'] = [
      '#type' => 'submit',
      '#value' => t('Clear Attribute List'),
      '#submit' => ['::clearAttrList'],
      '#button_type' => 'primary',
      '#limit_validation_errors' => [],
    ];

    $form['miniorange_saml_guide_clear_list_note'] = [
      '#markup' => '<br><br><div style="font-size: 13px;"><b>NOTE : </b>Please clear this list after configuring the module to hide your confidential attributes.<br>
                                      Click on <b>Test configuration</b> in <b>Configure Application</b> tab to populate the list again.</div>',
    ];

    $form['miniorange_saml_guide_table_end'] = [
      '#markup' => '</div>',
    ];
  }

  /**
   * Displays attributes received from OAuth provider on test configuration window.
   */
  public static function showAttr($attrs, &$result, $depth = 0, $carry = '', $tr = '<tr>', $td = '<td>') {
    if (!is_array($attrs) || sizeof($attrs) < 1) {
      return is_array($attrs) ? '' : $attrs . '</td></tr>';
    }

    foreach ($attrs as $key => $value) {
      if (is_array($value)) {
        if ($depth == 0) {
          $carry = $tr . $td . $key;
          self::showAttr($attrs[$key], $result, $depth + 1, $carry, $tr, $td);
        }
        else {
          self::showAttr($attrs[$key], $result, $depth + 1, $carry . '.' . $key, $tr, $td);
        }
      }
      else {
        if ($depth == 0) {
          $result .= $tr . $td . $key . '</td>' . $td . $value . '</td></tr>';
        }
        else {
          if (!empty($carry)) {
            $result .= $carry . '.' . $key . '</td>' . $td . $value . '</td></tr>';
          }
        }
      }
    }
  }

  /**
   * The countries => timezone array.
   *
   * @var array
   *   The array of different countries with their timezone.
   */
  public static $zones = [
    "Niue Time (GMT-11:00)" => "Pacific/Niue",
    "Samoa Standard Time (GMT-11:00) " => "Pacific/Pago_Pago",
    "Cook Islands Standard Time (GMT-10:00)" => "Pacific/Rarotonga",
    "Hawaii-Aleutian Standard Time (GMT-10:00) " => "Pacific/Honolulu",
    "Tahiti Time (GMT-10:00)" => "Pacific/Tahiti",
    "Marquesas Time (GMT-09:30)" => "Pacific/Marquesas",
    "Gambier Time (GMT-09:00)" => "Pacific/Gambier",
    "Hawaii-Aleutian Time (Adak) (GMT-09:00)" => "America/Adak",
    "Alaska Time - Anchorage(GMT-08:00)" => "America/Anchorage",
    "Alaska Time - Juneau (GMT-08:00)" => "America/Juneau",
    "Alaska Time - Metlakatla (GMT-08:00)" => "America/Metlakatla",
    "Alaska Time - Nome (GMT-08:00)" => "America/Nome",
    "Alaska Time - Sitka (GMT-08:00)" => "America/Sitka",
    "Alaska Time - Yakutat (GMT-08:00)" => "America/Yakutat",
    "Pitcairn Time (GMT-08:00)" => "Pacific/Pitcairn",
    "Mexican Pacific Standard Time (GMT-07:00)" => "America/Hermosillo",
    "Mountain Standard Time - Creston (GMT-07:00)" => "America/Creston",
    "Mountain Standard Time - Dawson (GMT-07:00)" => "America/Dawson",
    "Mountain Standard Time - Dawson Creek (GMT-07:00)" => "America/Dawson_Creek",
    "Mountain Standard Time - Fort Nelson (GMT-07:00)" => "America/Fort_Nelson",
    "Mountain Standard Time - Phoenix (GMT-07:00)" => "America/Phoenix",
    "Mountain Standard Time - Whitehorse (GMT-07:00)" => "America/Whitehorse",
    "Pacific Time - Los Angeles (GMT-07:00)" => "America/Los_Angeles",
    "Pacific Time - Tijuana (GMT-07:00)" => "America/Tijuana",
    "Pacific Time - Vancouver (GMT-07:00)" => "America/Vancouver",
    "Central Standard Time - Belize (GMT-06:00)" => "America/Belize",
    "Central Standard Time - Costa Rica (GMT-06:00)" => "America/Costa_Rica",
    "Central Standard Time - El Salvador (GMT-06:00)" => "America/El_Salvador",
    "entral Standard Time - Guatemala (GMT-06:00)" => "America/Guatemala",
    "Central Standard Time - Managua (GMT-06:00)" => "America/Managua",
    "Central Standard Time - Regina (GMT-06:00)" => "America/Regina",
    "Central Standard Time - Swift Current (GMT-06:00)" => "America/Swift_Current",
    "Central Standard Time - Tegucigalpa (GMT-06:00)" => "America/Tegucigalpa",
    "Easter Island Time (GMT-06:00)" => "Pacific/Easter",
    "Galapagos Time (GMT-06:00)" => "Pacific/Galapagos",
    "Mexican Pacific Time - Chihuahua (GMT-06:00)" => "America/Chihuahua",
    "Mexican Pacific Time - Mazatlan (GMT-06:00)" => "America/Mazatlan",
    "Mountain Time - Boise (GMT-06:00)" => "America/Boise",
    "Mountain Time - Cambridge Bay (GMT-06:00)" => "America/Cambridge_Bay",
    "Mountain Time - Denver (GMT-06:00)" => "America/Denver",
    "Mountain Time - Edmonton (GMT-06:00)" => "America/Edmonton",
    "Mountain Time - Inuvik (GMT-06:00)" => "America/Inuvik",
    "(Mountain Time - Ojinaga (GMT-06:00)" => "America/Ojinaga",
    "Mountain Time - Yellowknife (GMT-06:00)" => "America/Yellowknife",
    "Acre Standard Time - Eirunepe (GMT-05:00)" => "America/Eirunepe",
    "Acre Standard Time - Rio Branco (GMT-05:00)" => "America/Rio_Branco",
    "Central Time - Bahia Banderas (GMT-05:00)" => "America/Bahia_Banderas",
    "Central Time - Beulah, North Dakota (GMT-05:00)" => "America/North_Dakota/Beulah",
    "Central Time - Center, North Dakota (GMT-05:00)" => "America/North_Dakota/Center",
    "Central Time - Chicago (GMT-05:00)" => "America/Chicago",
    "Central Time - Knox, Indiana (GMT-05:00)" => "America/Indiana/Knox",
    "Central Time - Matamoros (GMT-05:00)" => "America/Matamoros",
    "Central Time - Menominee (GMT-05:00)" => "America/Menominee",
    "Central Time - Merida (GMT-05:00)" => "America/Merida",
    "Central Time - Mexico City (GMT-05:00)" => "America/Mexico_City",
    "Central Time - Monterrey (GMT-05:00)" => "America/Monterrey",
    "Central Time - New Salem, North Dakota (GMT-05:00)" => "America/North_Dakota/New_Salem",
    "Central Time - Rainy River (GMT-05:00)" => "America/Rainy_River",
    "Central Time - Rankin Inlet (GMT-05:00)" => "America/Rankin_Inlet",
    "Central Time - Resolute (GMT-05:00)" => "America/Resolute",
    "Central Time - Tell City, Indiana (GMT-05:00)" => "America/Indiana/Tell_City",
    "Central Time - Winnipeg (GMT-05:00)" => "America/Winnipeg",
    "Colombia Standard Time (GMT-05:00)" => "America/Bogota",
    "Eastern Standard Time - Atikokan (GMT-05:00)" => "America/Atikokan",
    "Eastern Standard Time - Cancun (GMT-05:00)" => "America/Cancun",
    "Eastern Standard Time - Jamaica (GMT-05:00)" => "America/Jamaica",
    "Eastern Standard Time - Panama (GMT-05:00)" => "America/Panama",
    "Ecuador Time (GMT-05:00)" => "America/Guayaquil",
    "Peru Standard Time (GMT-05:00)" => "America/Lima",
    "Amazon Standard Time - Boa Vista (GMT-04:00)" => "America/Boa_Vista",
    "Amazon Standard Time - Campo Grande (GMT-04:00)" => "America/Campo_Grande",
    "Amazon Standard Time - Cuiaba (GMT-04:00)" => "America/Cuiaba",
    "Amazon Standard Time - Manaus (GMT-04:00)" => "America/Manaus",
    "Amazon Standard Time - Porto Velho (GMT-04:00)" => "America/Porto_Velho",
    "Atlantic Standard Time - Barbados (GMT-04:00)" => "America/Barbados",
    "Atlantic Standard Time - Blanc-Sablon (GMT-04:00)" => "America/Blanc-Sablon",
    "Atlantic Standard Time - Curacao (GMT-04:00)" => "America/Curacao",
    "Atlantic Standard Time - Martinique (GMT-04:00)" => "America/Martinique",
    "Atlantic Standard Time - Port of Spain (GMT-04:00)" => "America/Port_of_Spain",
    "Atlantic Standard Time - Puerto Rico (GMT-04:00)" => "America/Puerto_Rico",
    "Atlantic Standard Time - Santo Domingo (GMT-04:00)" => "America/Santo_Domingo",
    "Bolivia Time (GMT-04:00)" => "America/La_Paz",
    "Chile Time (GMT-04:00)" => "America/Santiago",
    "Cuba Time (GMT-04:00)" => "America/Havana",
    "Eastern Time - Detroit (GMT-04:00)" => "America/Detroit",
    "Eastern Time - Grand Turk (GMT-04:00)" => "America/Grand_Turk",
    "Eastern Time - Indianapolis (GMT-04:00)" => "America/Indiana/Indianapolis",
    "Eastern Time - Iqaluit (GMT-04:00)" => "America/Iqaluit",
    "Eastern Time - Louisville (GMT-04:00)" => "America/Kentucky/Louisville",
    "Eastern Time - Marengo, Indiana (GMT-04:00)" => "America/Indiana/Marengo",
    "Eastern Time - Monticello, Kentucky (GMT-04:00)" => "America/Kentucky/Monticello",
    "Eastern Time - Nassau (GMT-04:00)" => "America/Nassau",
    "Eastern Time - New York (GMT-04:00)" => "America/New_York",
    "Eastern Time - Nipigon (GMT-04:00)" => "America/Nipigon",
    "Eastern Time - Pangnirtung (GMT-04:00)" => "America/Pangnirtung",
    "Eastern Time - Petersburg, Indiana (GMT-04:00)" => "America/Indiana/Petersburg",
    "Eastern Time - Port-au-Prince (GMT-04:00)" => "America/Port-au-Prince",
    "Eastern Time - Thunder Bay (GMT-04:00)" => "America/Thunder_Bay",
    "Eastern Time - Toronto (GMT-04:00)" => "America/Toronto",
    "Eastern Time - Vevay, Indiana (GMT-04:00)" => "America/Indiana/Vevay",
    "Eastern Time - Vincennes, Indiana (GMT-04:00)" => "America/Indiana/Vincennes",
    "Eastern Time - Winamac, Indiana (GMT-04:00)" => "America/Indiana/Winamac",
    "Guyana Time (GMT-04:00)" => "America/Guyana",
    "Paraguay Time (GMT-04:00)" => "America/Asuncion",
    "Venezuela Time (GMT-04:00)" => "America/Caracas",
    "Argentina Standard Time - Buenos Aires (GMT-03:00)" => "America/Argentina/Buenos_Aires",
    "Argentina Standard Time - Catamarca (GMT-03:00)" => "America/Argentina/Catamarca",
    "Argentina Standard Time - Cordoba (GMT-03:00)" => "America/Argentina/Cordoba",
    "Argentina Standard Time - Jujuy (GMT-03:00)" => "America/Argentina/Jujuy",
    "Argentina Standard Time - La Rioja (GMT-03:00)" => "America/Argentina/La_Rioja",
    "Argentina Standard Time - Mendoza (GMT-03:00)" => "America/Argentina/Mendoza",
    "Argentina Standard Time - Rio Gallegos (GMT-03:00)" => "America/Argentina/Rio_Gallegos",
    "Argentina Standard Time - Salta (GMT-03:00)" => "America/Argentina/Salta",
    "Argentina Standard Time - San Juan (GMT-03:00)" => "America/Argentina/San_Juan",
    "Argentina Standard Time - San Luis (GMT-03:00)" => "America/Argentina/San_Luis",
    "Argentina Standard Time - Tucuman (GMT-03:00)" => "America/Argentina/Tucuman",
    "Argentina Standard Time - Ushuaia (GMT-03:00)" => "America/Argentina/Ushuaia",
    "Atlantic Time - Bermuda (GMT-03:00)" => "Atlantic/Bermuda",
    "Atlantic Time - Glace Bay (GMT-03:00)" => "America/Glace_Bay",
    "Atlantic Time - Goose Bay (GMT-03:00)" => "America/Goose_Bay",
    "Atlantic Time - Halifax (GMT-03:00)" => "America/Halifax",
    "Atlantic Time - Moncton (GMT-03:00)" => "America/Moncton",
    "Atlantic Time - Thule (GMT-03:00)" => "America/Thule",
    "Brasilia Standard Time - Araguaina (GMT-03:00)" => "America/Araguaina",
    "Brasilia Standard Time - Bahia (GMT-03:00)" => "America/Bahia",
    "Brasilia Standard Time - Belem (GMT-03:00)" => "America/Belem",
    "Brasilia Standard Time - Fortaleza (GMT-03:00)" => "America/Fortaleza",
    "Brasilia Standard Time - Maceio (GMT-03:00)" => "America/Maceio",
    "Brasilia Standard Time - Recife (GMT-03:00)" => "America/Recife",
    "Brasilia Standard Time - Santarem (GMT-03:00)" => "America/Santarem",
    "Brasilia Standard Time - Sao Paulo (GMT-03:00)" => "America/Sao_Paulo",
    "Chile Time (GMT-03:00)" => "America/Santiago",
    "Falkland Islands Standard Time (GMT-03:00)" => "Atlantic/Stanley",
    "French Guiana Time (GMT-03:00)" => "America/Cayenne",
    "Palmer Time (GMT-03:00)" => "Antarctica/Palmer",
    "Punta Arenas Time (GMT-03:00)" => "America/Punta_Arenas",
    "Rothera Time (GMT-03:00)" => "Antarctica/Rothera",
    "Suriname Time (GMT-03:00)" => "America/Paramaribo",
    "Uruguay Standard Time (GMT-03:00)" => "America/Montevideo",
    "Newfoundland Time (GMT-02:30)" => "America/St_Johns",
    "Fernando de Noronha Standard Time (GMT-02:00)" => "America/Noronha",
    "South Georgia Time (GMT-02:00)" => "Atlantic/South_Georgia",
    "St. Pierre & Miquelon Time (GMT-02:00)" => "America/Miquelon",
    "West Greenland Time (GMT-02:00)" => "America/Nuuk",
    "Cape Verde Standard Time (GMT-01:00)" => "Atlantic/Cape_Verde",
    "Azores Time (GMT+00:00)" => "Atlantic/Azores",
    "Coordinated Universal Time (GMT+00:00)" => "UTC",
    "East Greenland Time (GMT+00:00)" => "America/Scoresbysund",
    "Greenwich Mean Time (GMT+00:00)" => "Etc/GMT",
    "Greenwich Mean Time - Abidjan (GMT+00:00)" => "Africa/Abidjan",
    "Greenwich Mean Time - Accra (GMT+00:00)" => "Africa/Accra",
    "Greenwich Mean Time - Bissau (GMT+00:00)" => "Africa/Bissau",
    "Greenwich Mean Time - Danmarkshavn (GMT+00:00)" => "America/Danmarkshavn",
    "Greenwich Mean Time - Monrovia (GMT+00:00)" => "Africa/Monrovia",
    "Greenwich Mean Time - Reykjavik (GMT+00:00)" => "Atlantic/Reykjavik",
    "Greenwich Mean Time - Sao Tome (GMT+00:00)" => "Africa/Sao_Tome",
    "Central European Standard Time - Algiers (GMT+01:00)" => "Africa/Algiers",
    "Central European Standard Time - Tunis (GMT+01:00)" => "Africa/Tunis",
    "Ireland Time (GMT+01:00)" => "Europe/Dublin",
    "Morocco Time (GMT+01:00)" => "Africa/Casablanca",
    "United Kingdom Time (GMT+01:00)" => "Europe/London",
    "West Africa Standard Time - Lagos (GMT+01:00)" => "Africa/Lagos",
    "West Africa Standard Time - Ndjamena (GMT+01:00)" => "Africa/Ndjamena",
    "Western European Time - Canary (GMT+01:00)" => "Atlantic/Canary",
    "Western European Time - Faroe (GMT+01:00)" => "Atlantic/Faroe",
    "Western European Time - Lisbon (GMT+01:00)" => "Europe/Lisbon",
    "Western European Time - Madeira (GMT+01:00)" => "Atlantic/Madeira",
    "Western Sahara Time (GMT+01:00)" => "Africa/El_Aaiun",
    "Central Africa Time - Khartoum (GMT+02:00)" => "Africa/Khartoum",
    "Central Africa Time - Maputo (GMT+02:00)" => "Africa/Maputo",
    "Central Africa Time - Windhoek (GMT+02:00)" => "Africa/Windhoek",
    "Central European Time - Amsterdam (GMT+02:00)" => "Europe/Amsterdam",
    "Central European Time - Andorra (GMT+02:00)" => "Europe/Andorra",
    "Central European Time - Belgrade (GMT+02:00)" => "Europe/Belgrade",
    "Central European Time - Berlin (GMT+02:00)" => "Europe/Berlin",
    "Central European Time - Brussels (GMT+02:00)" => "Europe/Brussels",
    "Central European Time - Budapest (GMT+02:00)" => "Europe/Budapest",
    "Central European Time - Ceuta (GMT+02:00)" => "Africa/Ceuta",
    "Central European Time - Copenhagen (GMT+02:00)" => "Europe/Copenhagen",
    "Central European Time - Gibraltar (GMT+02:00)" => "Europe/Gibraltar",
    "Central European Time - Luxembourg (GMT+02:00)" => "Europe/Luxembourg",
    "Central European Time - Madrid (GMT+02:00)" => "Europe/Madrid",
    "Central European Time - Malta (GMT+02:00)" => "Europe/Malta",
    "Central European Time - Monaco (GMT+02:00)" => "Europe/Monaco",
    "Central European Time - Oslo (GMT+02:00)" => "Europe/Oslo",
    "Central European Time - Paris (GMT+02:00)" => "Europe/Paris",
    "Central European Time - Prague (GMT+02:00)" => "Europe/Prague",
    "Central European Time - Rome (GMT+02:00)" => "Europe/Rome",
    "Central European Time - Stockholm (GMT+02:00)" => "Europe/Stockholm",
    "Central European Time - Tirane (GMT+02:00)" => "Europe/Tirane",
    "Central European Time - Vienna (GMT+02:00)" => "Europe/Vienna",
    "Central European Time - Warsaw (GMT+02:00)" => "Europe/Warsaw",
    "Central European Time - Zurich (GMT+02:00)" => "Europe/Zurich",
    "Eastern European Standard Time - Cairo (GMT+02:00)" => "Africa/Cairo",
    "Eastern European Standard Time - Kaliningrad (GMT+02:00)" => "Europe/Kaliningrad",
    "Eastern European Standard Time - Tripoli (GMT+02:00)" => "Africa/Tripoli",
    "South Africa Standard Time (GMT+02:00)" => "Africa/Johannesburg",
    "Troll Time (GMT+02:00)" => "Antarctica/Troll",
    "Arabian Standard Time - Baghdad (GMT+03:00)" => "Asia/Baghdad",
    "Arabian Standard Time - Qatar (GMT+03:00)" => "Asia/Qatar",
    "Arabian Standard Time - Riyadh (GMT+03:00)" => "Asia/Riyadh",
    "East Africa Time - Juba (GMT+03:00)" => "Africa/Juba",
    "East Africa Time - Nairobi (GMT+03:00)" => "Africa/Nairobi",
    "Eastern European Time - Amman (GMT+03:00)" => "Asia/Amman",
    "Eastern European Time - Athens (GMT+03:00)" => "Europe/Athens",
    "Eastern European Time - Beirut (GMT+03:00)" => "Asia/Beirut",
    "Eastern European Time - Bucharest (GMT+03:00)" => "Europe/Bucharest",
    "Eastern European Time - Chisinau (GMT+03:00)" => "Europe/Chisinau",
    "Eastern European Time - Damascus (GMT+03:00)" => "Asia/Damascus",
    "Eastern European Time - Gaza (GMT+03:00)" => "Asia/Gaza",
    "Eastern European Time - Hebron (GMT+03:00)" => "Asia/Hebron",
    "Eastern European Time - Helsinki (GMT+03:00)" => "Europe/Helsinki",
    "Eastern European Time - Kiev (GMT+03:00)" => "Europe/Kiev",
    "Eastern European Time - Nicosia (GMT+03:00)" => "Asia/Nicosia",
    "Eastern European Time - Riga (GMT+03:00)" => "Europe/Riga",
    "Eastern European Time - Sofia (GMT+03:00)" => "Europe/Sofia",
    "Eastern European Time - Tallinn (GMT+03:00)" => "Europe/Tallinn",
    "Eastern European Time - Uzhhorod (GMT+03:00)" => "Europe/Uzhgorod",
    "Eastern European Time - Vilnius (GMT+03:00)" => "Europe/Vilnius",
    "Eastern European Time - Zaporozhye (GMT+03:00)" => "Europe/Zaporozhye",
    "Famagusta Time (GMT+03:00)" => "Asia/Famagusta",
    "Israel Time" => "Asia/Jerusalem (GMT+03:00)",
    "Kirov Time" => "Europe/Kirov (GMT+03:00)",
    "Moscow Standard Time - Minsk (GMT+03:00)" => "Europe/Minsk",
    "Moscow Standard Time - Moscow (GMT+03:00)" => "Europe/Moscow",
    "Moscow Standard Time - Simferopol (GMT+03:00)" => "Europe/Simferopol",
    "Syowa Time (GMT+03:00)" => "Antarctica/Syowa",
    "Turkey Time (GMT+03:00)" => "Europe/Istanbul",
    "Armenia Standard Time (GMT+04:00)" => "Asia/Yerevan",
    "Astrakhan Time (GMT+04:00)" => "Europe/Astrakhan",
    "Azerbaijan Standard Time (GMT+04:00)" => "Asia/Baku",
    "Georgia Standard Time (GMT+04:00)" => "Asia/Tbilisi",
    "Gulf Standard Time (GMT+04:00)" => "Asia/Dubai",
    "Mauritius Standard Time (GMT+04:00)" => "Indian/Mauritius",
    "Reunion Time (GMT+04:00)" => "Indian/Reunion",
    "Samara Standard Time (GMT+04:00)" => "Europe/Samara",
    "Saratov Time (GMT+04:00)" => "Europe/Saratov",
    "Seychelles Time (GMT+04:00)" => "Indian/Mahe",
    "Ulyanovsk Time (GMT+04:00)" => "Europe/Ulyanovsk",
    "Volgograd Standard Time (GMT+04:00)" => "Europe/Volgograd",
    "Afghanistan Time (GMT+04:30)" => "Asia/Kabul",
    "Iran Time (GMT+04:30)" => "Asia/Tehran",
    "French Southern & Antarctic Time (GMT+05:00)" => "Indian/Kerguelen",
    "Maldives Time (GMT+05:00)" => "Indian/Maldives",
    "Mawson Time (GMT+05:00)" => "Antarctica/Mawson",
    "Pakistan Standard Time (GMT+05:00)" => "Asia/Karachi",
    "Tajikistan Time (GMT+05:00)" => "Asia/Dushanbe",
    "Turkmenistan Standard Time (GMT+05:00)" => "Asia/Ashgabat",
    "Uzbekistan Standard Time - Samarkand (GMT+05:00)" => "Asia/Samarkand",
    "Uzbekistan Standard Time - Tashkent (GMT+05:00)" => "Asia/Tashkent",
    "West Kazakhstan Time - Aqtau (GMT+05:00)" => "Asia/Aqtau",
    "West Kazakhstan Time - Aqtobe (GMT+05:00)" => "Asia/Aqtobe",
    "West Kazakhstan Time - Atyrau (GMT+05:00)" => "Asia/Atyrau",
    "West Kazakhstan Time - Oral (GMT+05:00)" => "Asia/Oral",
    "West Kazakhstan Time - Qyzylorda (GMT+05:00)" => "Asia/Qyzylorda",
    "Yekaterinburg Standard Time (GMT+05:00)" => "Asia/Yekaterinburg",
    "Indian Standard Time - Colombo (GMT+05:30)" => "Asia/Colombo",
    "Indian Standard Time - Kolkata (GMT+05:30)" => "Asia/Kolkata",
    "Nepal Time (GMT+05:45)" => "Asia/Kathmandu",
    "Bangladesh Standard Time (GMT+06:00)" => "Asia/Dhaka",
    "Bhutan Time (GMT+06:00)" => "Asia/Thimphu",
    "East Kazakhstan Time - Almaty (GMT+06:00)" => "Asia/Almaty",
    "East Kazakhstan Time - Kostanay (GMT+06:00)" => "Asia/Qostanay",
    "Indian Ocean Time (GMT+06:00)" => "Indian/Chagos",
    "Kyrgyzstan Time (GMT+06:00)" => "Asia/Bishkek",
    "Omsk Standard Time (GMT+06:00)" => "Asia/Omsk",
    "Urumqi Time (GMT+06:00)" => "Asia/Urumqi",
    "Vostok Time (GMT+06:00)" => "Antarctica/Vostok",
    "Cocos Islands Time (GMT+06:30)" => "Indian/Cocos",
    "Myanmar Time (GMT+06:30)" => "Asia/Yangon",
    "Barnaul Time (GMT+07:00)" => "Asia/Barnaul",
    "Christmas Island Time (GMT+07:00)" => "Indian/Christmas",
    "Davis Time (GMT+07:00)" => "Antarctica/Davis",
    "Hovd Standard Time (GMT+07:00)" => "Asia/Hovd",
    "Indochina Time - Bangkok (GMT+07:00)" => "Asia/Bangkok",
    "Indochina Time - Ho Chi Minh City (GMT+07:00)" => "Asia/Ho_Chi_Minh",
    "Krasnoyarsk Standard Time - Krasnoyarsk (GMT+07:00)" => "Asia/Krasnoyarsk",
    "Krasnoyarsk Standard Time - Novokuznetsk (GMT+07:00)" => "Asia/Novokuznetsk",
    "Novosibirsk Standard Time (GMT+07:00)" => "Asia/Novosibirsk",
    "Tomsk Time (GMT+07:00)" => "Asia/Tomsk",
    "Western Indonesia Time - Jakarta (GMT+07:00)" => "Asia/Jakarta",
    "Western Indonesia Time - Pontianak (GMT+07:00)" => "Asia/Pontianak",
    "Australian Western Standard Time - Casey (GMT+08:00)" => "Antarctica/Casey",
    "Australian Western Standard Time - Perth (GMT+08:00)" => "Australia/Perth",
    "Brunei Darussalam Time (GMT+08:00)" => "Asia/Brunei",
    "Central Indonesia Time (GMT+08:00)" => "Asia/Makassar",
    "China Standard Time - Macao (GMT+08:00)" => "Asia/Macau",
    "China Standard Time - Shanghai (GMT+08:00)" => "Asia/Shanghai",
    "Hong Kong Standard Time (GMT+08:00)" => "Asia/Hong_Kong",
    "Irkutsk Standard Time (GMT+08:00)" => "Asia/Irkutsk",
    "Malaysia Time - Kuala Lumpur (GMT+08:00)" => "Asia/Kuala_Lumpur",
    "Malaysia Time - Kuching (GMT+08:00)" => "Asia/Kuching",
    "Philippine Standard Time (GMT+08:00)" => "Asia/Manila",
    "Singapore Standard Time (GMT+08:00)" => "Asia/Singapore",
    "Taipei Standard Time (GMT+08:00)" => "Asia/Taipei",
    "Ulaanbaatar Standard Time - Choibalsan (GMT+08:00)" => "Asia/Choibalsan",
    "Ulaanbaatar Standard Time - Ulaanbaatar (GMT+08:00)" => "Asia/Ulaanbaatar",
    "Australian Central Western Standard Time (GMT+08:45)" => "Australia/Eucla",
    "East Timor Time (GMT+09:00)" => "Asia/Dili",
    "Eastern Indonesia Time (GMT+09:00)" => "Asia/Jayapura",
    "Japan Standard Time (GMT+09:00)" => "Asia/Tokyo",
    "Korean Standard Time - Pyongyang (GMT+09:00)" => "Asia/Pyongyang",
    "Korean Standard Time - Seoul (GMT+09:00)" => "Asia/Seoul",
    "Palau Time" => "Pacific/Palau (GMT+09:00)",
    "Yakutsk Standard Time - Chita (GMT+09:00)" => "Asia/Chita",
    "Yakutsk Standard Time - Khandyga (GMT+09:00)" => "Asia/Khandyga",
    "Yakutsk Standard Time - Yakutsk (GMT+09:00)" => "Asia/Yakutsk",
    "Australian Central Standard Time (GMT+09:30)" => "Australia/Darwin",
    "Central Australia Time - Adelaide (GMT+09:30)" => "Australia/Adelaide",
    "Central Australia Time - Broken Hill (GMT+09:30)" => "Australia/Broken_Hill",
    "Australian Eastern Standard Time - Brisbane (GMT+10:00)" => "Australia/Brisbane",
    "Australian Eastern Standard Time - Lindeman (GMT+10:00)" => "Australia/Lindeman",
    "Chamorro Standard Time (GMT+10:00)" => "Pacific/Guam",
    "Chuuk Time (GMT+10:00)" => "Pacific/Chuuk",
    "Dumont-dUrville Time (GMT+10:00)" => "Antarctica/DumontDUrville",
    "Eastern Australia Time - Currie (GMT+10:00)" => "Australia/Currie",
    "Eastern Australia Time - Hobart (GMT+10:00)" => "Australia/Hobart",
    "Eastern Australia Time - Melbourne (GMT+10:00)" => "Australia/Melbourne",
    "Eastern Australia Time - Sydney (GMT+10:00)" => "Australia/Sydney",
    "Papua New Guinea Time (GMT+10:00)" => "Pacific/Port_Moresby",
    "Vladivostok Standard Time - Ust-Nera (GMT+10:00)" => "Asia/Ust-Nera",
    "Vladivostok Standard Time - Vladivostok (GMT+10:00)" => "Asia/Vladivostok",
    "Lord Howe Time (GMT+10:30)" => "Australia/Lord_Howe",
    "Bougainville Time (GMT+11:00)" => "Pacific/Bougainville",
    "Kosrae Time (GMT+11:00)" => "Pacific/Kosrae",
    "Macquarie Island Time (GMT+11:00)" => "Antarctica/Macquarie",
    "Magadan Standard Time (GMT+11:00)" => "Asia/Magadan",
    "New Caledonia Standard Time (GMT+11:00)" => "Pacific/Noumea",
    "Norfolk Island Time (GMT+11:00)" => "Pacific/Norfolk",
    "Ponape Time (GMT+11:00)" => "Pacific/Pohnpei",
    "Sakhalin Standard Time (GMT+11:00)" => "Asia/Sakhalin",
    "Solomon Islands Time (GMT+11:00)" => "Pacific/Guadalcanal",
    "Srednekolymsk Time (GMT+11:00)" => "Asia/Srednekolymsk",
    "Vanuatu Standard Time (GMT+11:00)" => "Pacific/Efate",
    "Anadyr Standard Time (GMT+12:00)" => "Asia/Anadyr",
    "Fiji Time (GMT+12:00)" => "Pacific/Fiji",
    "Gilbert Islands Time (GMT+12:00)" => "Pacific/Tarawa",
    "Marshall Islands Time - Kwajalein (GMT+12:00)" => "Pacific/Kwajalein",
    "Marshall Islands Time - Majuro (GMT+12:00)" => "Pacific/Majuro",
    "Nauru Time (GMT+12:00)" => "Pacific/Nauru",
    "New Zealand Time (GMT+12:00)" => "Pacific/Auckland",
    "Petropavlovsk-Kamchatski Standard Time (GMT+12:00)" => "Asia/Kamchatka",
    "Tuvalu Time (GMT+12:00)" => "Pacific/Funafuti",
    "Wake Island Time (GMT+12:00)" => "Pacific/Wake",
    "Wallis & Futuna Time (GMT+12:00)" => "Pacific/Wallis",
    "Chatham Time (GMT+12:45)" => "Pacific/Chatham",
    "Apia Time (GMT+13:00)" => "Pacific/Apia",
    "Phoenix Islands Time (GMT+13:00)" => "Pacific/Enderbury",
    "Tokelau Time (GMT+13:00)" => "Pacific/Fakaofo",
    "Tonga Standard Time (GMT+13:00)" => "Pacific/Tongatapu",
    "Line Islands Time (GMT+14:00)" => "Pacific/Kiritimati",
  ];

  /**
   * Displays customer support button.
   */
  public static function moOAuthShowCustomerSupportIcon(array &$form, FormStateInterface $form_state) {
    $base_url = \Drupal::request()->getSchemeAndHttpHost().\Drupal::request()->getBasePath();
    $support_image_path = $base_url . '/' . \Drupal::service('extension.list.module')->getPath('oauth_login_oauth2') . '/includes/images';
    $form['mo_oauth_login_customer_support_icon'] = [
      '#markup' => t('<a class="use-ajax mo-bottom-corner" data-dialog-type="modal" data-dialog-options="{&quot;width&quot;:&quot;45%&quot;}" href="CustomerSupportClient"><img src="' . $support_image_path . '/mo-customer-support.png" alt="support image"></a>'),
    ];
  }

  /**
   * Displays form to get feedback if new is needed to customer.
   *
   * @param array $form
   *   The form elements array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The formstate.
   */
  public static function nofeaturelisted(&$form, &$form_state) {
    $module_path = \Drupal::service('extension.list.module')->getPath('oauth_login_oauth2');
    $base_url = \Drupal::request()->getSchemeAndHttpHost().\Drupal::request()->getBasePath();
    $form['miniorange_no_feature_list'] = [
      '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container_2">',
    ];

    $form['mo_oauth_horizontal_tabs'] = [
      '#type' => 'horizontal_tabs',
    ];

    $form['miniorange_more_features'] = [
      '#type' => 'fieldset',
      '#title' => t('Looking for more features?'),
      '#group' => 'mo_oauth_horizontal_tabs',
      '#open' => TRUE,
    ];

    $form['miniorange_more_features']['miniorange_no_feature_list1'] = [
      '#markup' => '<div class="mo_oauth_no_feature">
                                   <img src="' . $base_url . '/' . $module_path . '/includes/images/more_features.jpg" alt="More Features icon" height="200px" width="200px"></div>',
    ];

    $form['miniorange_more_features']['miniorange_no_feature_list2'] = [
      '#markup' => '<div>In case you do not find your desired feature or if you want any custom feature in the module, please mail us on <a href="mailto:drupalsupport@xecurify.com">drupalsupport@xecurify.com</a>
                              and we will implement it for you.</div>',

    ];
    self::faq($form, $form_state);

    $form['miniorange_oauth_div_close'] = [
      '#markup' => '</div>',
    ];
  }

  /**
   * Displays FAQ's links.
   *
   * @param array $form
   *   The form elements array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The formstate.
   */
  public static function faq(&$form, &$form_state) {
    $form['miniorange_more_features']['miniorange_oauth_faq_button_css'] = [
      '#attached' => [
        'library' => 'oauth_login_oauth2/oauth_login_oauth2.style_settings',
      ],
    ];

    $form['miniorange_more_features']['miniorange_faq'] = [
      '#markup' => '<br><div class="mo_oauth_client_text_center"><b></b>
                          <a class="button button--primary button--small" href="https://faq.miniorange.com/kb/drupal/drupal-oauth-oidc-sso/" target="_blank">FAQs</a>
                                    <b></b><a class="button button--small" href="https://forum.miniorange.com/" target="_blank">Ask questions on forum</a></div>',
    ];
  }

}

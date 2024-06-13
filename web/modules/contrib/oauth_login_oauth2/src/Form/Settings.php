<?php

namespace Drupal\oauth_login_oauth2\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\oauth_login_oauth2\Utilities;

/**
 * Class for handling settings tab of module.
 */
class Settings extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'miniorange_oauth_client_settings';
  }

  /**
   * {@inheritDoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $base_url = \Drupal::request()->getSchemeAndHttpHost().\Drupal::request()->getBasePath();
    $baseUrlValue = \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_base_url');
    $url_path = $base_url . '/' . \Drupal::service('extension.list.module')->getPath('oauth_login_oauth2') . '/includes/Providers';

    $form['markup_library'] = [
      '#attached' => [
        'library' => [
          "oauth_login_oauth2/oauth_login_oauth2.admin",
          "oauth_login_oauth2/oauth_login_oauth2.style_settings",
          "core/drupal.dialog.ajax"
        ],
      ],
    ];

    $form['markup_top'] = [
      '#markup' => '<div class="mo_oauth_table_layout mo_oauth_container2">',
    ];

    $form['markup_custom_sign_in'] = [
      '#type' => 'fieldset',
      '#title' => t('SIGN IN SETTINGS'),
    ];

    $form['markup_custom_sign_in']['miniorange_oauth_client_base_url'] = [
      '#type' => 'textfield',
      '#title' => t('Base URL: '),
      '#default_value' => $baseUrlValue,
      '#attributes' => ['id' => 'mo_oauth_vt_baseurl', 'style' => 'width:73%;', 'placeholder' => 'Enter Base URL'],
      '#description' => '<b>Note: </b>If your provider only support HTTPS Callback URL and you have HTTP site, please save your base site URL with HTTPS here.',
      '#suffix' => '<br>',
      '#prefix' => '<hr>',
    ];

    $form['markup_custom_sign_in']['miniorange_oauth_client_siginin1'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#attributes' => ['style' => 'margin: auto; display:block; '],
      '#value' => t('Update'),
    ];

    $form['markup_top_auto_create'] = array(
      '#type' => 'fieldset',
      '#title' => t('AUTO CREATE USERS<a href="licensing"><img class="mo_oauth_pro_icon1" src="' . $url_path . '/pro.png" alt="Premium and Enterprise"><span class="mo_pro_tooltip">Available in the Standard, Premium and Enterprise version</span></a>'),
    );

    $form['markup_top_auto_create']['miniorange_oauth_enable_autocreate_users'] = array(
      '#type' => 'checkbox',
      '#disabled' => true,
      '#prefix' => '<hr>',
      '#default_value' => false,
      '#title' => t('Enable auto creation of users'),
      '#description' => t('<b>Note:</b> Users will be automatically created in Drupal upon Single Sign-On login if they do not already exist.'),

    );

    $form['markup_top_auto_create']['miniorange_oauth_client_redirect_for_unregister'] = array(
      '#type' => 'url',
      '#disabled' => true,
      '#maxlength' => '900',
      '#title' => t('Redirect URL for Unregistered Users'),
      '#default_value' =>$base_url.'/user/login',
      '#attributes' => array('style' => 'width:73%','placeholder' => 'Enter complete URL'),
      '#description' => t('If empty, the Unregistered users will be redirected to the login page (<i>' . $base_url . '/user/login</i>).'),
      );

    $form['markup_top_auto_create']['miniorange_oauth_autocreate_in_blocked_status'] = array(
      '#type' => 'checkbox',
      '#disabled' => true,
      '#default_value' => false,
      '#title' => t('Create new users in Blocked Status'),
    );

    $form['markup_top_auto_create']['miniorange_oauth_client_redirect_for_blocked_status'] = array(
      '#type' => 'url',
      '#disabled' => true,
      '#maxlength' => '900',
      '#title' => t('Redirect URL for Blocked Users'),
      '#default_value' =>$base_url.'/user/login',
      '#attributes' => array('style' => 'width:73%', 'placeholder' => 'Enter complete URL'),
      '#description' => t('If empty, the Blocked users will be redirected to the login page (<i>' .$base_url.'/user/login</i>).'),
    );

    $form['markup_custom_sign_in1'] = [
      '#type' => 'fieldset',
      '#title' => t('ADVANCED SIGN IN SETTINGS <a href="licensing"><img class="mo_oauth_pro_icon1" src="' . $url_path . '/pro.png" alt="Premium and Enterprise"><span class="mo_pro_tooltip">Available in the Premium and Enterprise version</span></a><a class="mo_oauth_client_how_to_setup" href="https://developers.miniorange.com/docs/oauth-drupal/sign-in-settings#sign-in-settings-features" target="_blank">[What are Sign in settings feature]</a>'),
    ];

    $form['markup_custom_sign_in1']['miniorange_oauth_force_auth'] = [
      '#type' => 'checkbox',
      '#title' => t('Protect website against anonymous access'),
      '#disabled' => TRUE,
      '#prefix' => '<hr>',
      '#description' => t('<b>Note: </b>Users will be redirected to your OAuth server for login in case user is not logged in and tries to access website.<br><br>'),
    ];

    $form['markup_custom_sign_in1']['miniorange_oauth_auto_redirect'] = [
      '#type' => 'checkbox',
      '#title' => t('Check this option if you want to <b> Auto-redirect to OAuth Provider/Server </b>'),
      '#disabled' => TRUE,
      '#description' => t('<b>Note: </b>Users will be redirected to your OAuth server for login when the login page is accessed.<br><br>'),
    ];

    $form['markup_custom_sign_in1']['miniorange_oauth_enable_backdoor'] = [
      '#type' => 'checkbox',
      '#title' => t('Check this option if you want to enable <b>backdoor login </b>'),
      '#disabled' => TRUE,
      '#description' => t('<b>Note: </b>Checking this option creates a backdoor to login to your Website using Drupal credentials<br> incase you get locked out of your OAuth server.
                <br><b>Note down this URL: </b>Available in <a href="' . $base_url . '/admin/config/people/oauth_login_oauth2/licensing"><b>Premium, Enterprise</b></a> versions of the module.'),
    ];


    $form['redirect_url_login_logout'] = array(
      '#type' => 'fieldset',
      '#title' => t('REDIRECTION AFTER SSO LOGIN AND LOGOUT<a href="licensing"><img class="mo_oauth_pro_icon1" src="' . $url_path . '/pro.png" alt="Premium and Enterprise"><span class="mo_pro_tooltip">Available in the Standard, Premium and Enterprise version</span></a>'),
    );


    $form['redirect_url_login_logout']['miniorange_oauth_client_default_relaystate'] = array(
      '#type' => 'url',
      '#disabled' => true,
      '#maxlength' => '900',
      '#title' => t('Redirect URL after login'),
      '#default_value' =>$base_url.'/user',
      '#attributes' => array('style' => 'width:73%','placeholder' => 'Enter complete URL'),
      '#description' => t('Keep this field empty if you want to redirect the users to the same page from where they initiated the login.'),

    );

    $form['redirect_url_login_logout']['miniorange_oauth_logout_redirect'] = array(
      '#type' => 'checkbox',
      '#disabled' => true,
      '#title' => t('Keep users on the same page after logout'),
    );

    $form['redirect_url_login_logout']['miniorange_oauth_client_logout_url'] = array(
      '#type' => 'url',
      '#disabled' => true,
      '#maxlength' => '900',
      '#title' => t('Redirect URL after logout'),
      '#default_value' => $base_url,
      '#attributes' => array('style' => 'width:73%','placeholder' => 'Enter complete URL'),
    );

    $form['redirect_url_login_logout']['miniorange_oauth_slo'] = array(
      '#type' => 'checkbox',
      '#disabled' => true,
      '#title' => t('Enable Single Logout'),
      '#description' => t('Log out users from the Identity Provider (OAuth Server) if they log out from Drupal. Please note, that the effectiveness of this feature depends on the support provided by the OAuth provider. Not all OAuth providers support this.'),

    );

    $form['markup_custom_sign_in2'] = [
      '#type' => 'fieldset',
      '#title' => t('DOMAIN & PAGE RESTRICTION <a href="licensing"><img class="mo_oauth_pro_icon1" src="' . $url_path . '/pro.png" alt="Enterprise"><span class="mo_pro_tooltip">Available in the Enterprise version</span></a><a class="mo_oauth_client_how_to_setup" href="https://developers.miniorange.com/docs/oauth-drupal/sign-in-settings#domain-restriction" target="_blank">[What is Domain and Page Restriction]</a>'),
    ];

    $form['markup_custom_sign_in2']['miniorange_oauth_client_white_list_url'] = [
      '#type' => 'textfield',
      '#title' => t('Allowed Domains'),
      '#attributes' => [ 'placeholder' => 'Enter semicolon(;) separated domains (Eg. xxxx.com; xxxx.com)'],
      '#description' => t('<b>Note: </b> Enter <b>semicolon(;) separated</b> domains to allow SSO. Other than these domains will not be allowed to do SSO.'),
      '#disabled' => TRUE,
      '#prefix' => '<hr>',
    ];

    $form['markup_custom_sign_in2']['miniorange_oauth_client_black_list_url'] = [
      '#type' => 'textfield',
      '#title' => t('Restricted Domains'),
      '#attributes' => [ 'placeholder' => 'Enter semicolon(;) separated domains (Eg. xxxx.com; xxxx.com)'],
      '#description' => t('<b>Note: </b> Enter <b>semicolon(;) separated</b> domains to restrict SSO. Other than these domains will be allowed to do SSO.'),
      '#disabled' => TRUE,
    ];

    $form['markup_custom_sign_in2']['miniorange_oauth_client_page_restrict_url'] = [
      '#type' => 'textfield',
      '#title' => t('Page Restriction'),
      '#attributes' => ['placeholder' => 'Enter semicolon(;) separated page URLs (Eg. xxxx.com/yyy; xxxx.com/yyy)'],
      '#description' => t('<b>Note: </b> Enter <b>semicolon(;) separated</b> URLs to restrict unauthorized access.'),
      '#disabled' => TRUE,
    ];

    $form['save_settings_button'] = [
      '#type' => 'submit',
      '#value' => t('Save Settings'),
      '#disabled'=> TRUE,
      '#attributes' => ['style' => 'margin: auto; display:block; '],

    ];

    Utilities::moOAuthShowCustomerSupportIcon($form, $form_state);
    return $form;
  }

  /**
   * Submit Handler for settings tab.
   *
   * @param array $form
   *   The form elements array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The formstate.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $base_url = \Drupal::request()->getSchemeAndHttpHost().\Drupal::request()->getBasePath();
    $baseUrlvalue = trim($form['markup_custom_sign_in']['miniorange_oauth_client_base_url']['#value']);
    if (!empty($baseUrlvalue) && filter_var($baseUrlvalue, FILTER_VALIDATE_URL) == FALSE) {
      \Drupal::messenger()->adderror(t('Please enter a valid URL'));
      return;
    }
    \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_base_url', $baseUrlvalue)->save();
    $miniorange_auth_client_callback_uri = !empty($baseUrlvalue) ? $baseUrlvalue . "/mo_login" : $base_url . "/mo_login";
    \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_auth_client_callback_uri', $miniorange_auth_client_callback_uri)->save();
    \Drupal::messenger()->addMessage(t('Configurations saved successfully.'));
  }

  /**
   * Displays setup call form.
   *
   * @param array $form
   *   The form elements array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The formstate.
   */
  public static function setup_call(array &$form, FormStateInterface $form_state) {
    Utilities::scheduleCall($form, $form_state);
  }

}

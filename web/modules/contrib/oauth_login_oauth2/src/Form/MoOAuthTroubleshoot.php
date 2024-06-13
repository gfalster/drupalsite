<?php

namespace Drupal\oauth_login_oauth2\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\FormBase;
use Drupal\oauth_login_oauth2\Utilities;

/**
 * Class for troubleshooting issues in module.
 */
class MoOAuthTroubleshoot extends FormBase {

  /**
   * {@inheritDoc}
   */
  public function getFormId() {
    return 'miniorange_oauth_client_troubleshoot';
  }

  /**
   * Showing Settings form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $base_url = \Drupal::request()->getSchemeAndHttpHost().\Drupal::request()->getBasePath();
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

    $form['markup_custom_troubleshoot'] = [
      '#type' => 'fieldset',
      '#title' => t('DEBUGGING AND TROUBLESHOOT'),
    ];

    $form['markup_custom_troubleshoot']['miniorange_oauth_client_enable_logging'] = [
      '#type' => 'checkbox',
      '#title' => t('Enable Logging'),
      '#default_value' => \Drupal::config('oauth_login_oauth2.settings')->get('miniorange_oauth_client_enable_logging'),
      '#description' => 'Enabling this checkbox will add loggers under the <a href="' . $base_url . '/admin/reports/dblog?type%5B%5D=oauth_login_oauth2" target="_blank">Reports</a> section',
      '#suffix' => '<br>',
      '#prefix' => '<hr>',
    ];

    $form['markup_custom_troubleshoot']['miniorange_oauth_client_siginin1'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => t('Save Configuration'),
    ];

    $form['markup_custom_export_logs'] = [
      '#type' => 'fieldset',
      '#title' => t('EXPORT MODULE LOGS'),
    ];

    $form['markup_custom_export_logs']['miniorange_oauth_client_enable_logging'] = [
      '#type' => 'submit',
      '#value' => t('Download Module Logs'),
      '#limit_validation_errors' => [],
      '#submit' => ['::miniorange_module_logs'],
      '#prefix' => '<hr> Click on the button below to download module related logs.<br><br>',
    ];

    $form['mo_markup_div_imp'] = ['#markup' => '</div>'];
    Utilities::moOAuthShowCustomerSupportIcon($form, $form_state);
    return $form;
  }

  /**
   * Submit handler for troubleshoot form.
   *
   * @param array $form
   *   The form elements array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The formstate.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();
    $enable_logs = $form_values['miniorange_oauth_client_enable_logging'];
    \Drupal::configFactory()->getEditable('oauth_login_oauth2.settings')->set('miniorange_oauth_client_enable_logging', $enable_logs)->save();
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

  /**
   * Filters data.
   */
  public static function mofilterData(&$str) {
    $str = preg_replace("/\t/", "\\t", $str);
    $str = preg_replace("/\r?\n/", "\\n", $str);
    if (strstr($str, '"')) {
      $str = '"' . str_replace('"', '""', $str) . '"';
    }
  }

  /**
   * Exports watchdog logs to excel file.
   */
  public static function miniorange_module_logs() {

    $connection = \Drupal::database();

    // Excel file name for download.
    $fileName = "drupal_oauth_login_loggers_" . date('Y-m-d') . ".xls";

    // Column names.
    $fields = ['WID', 'UID', 'TYPE', 'MESSAGE', 'VARIABLES', 'SEVERITY', 'LINK', 'LOCATION', 'REFERER', 'HOSTNAME', 'TIMESTAMP'];

    // Display column names as first row.
    $excelData = implode("\t", array_values($fields)) . "\n\n";

    // Fetch records from database.
    $query = $connection->query("SELECT * from {watchdog} WHERE type = 'oauth_login_oauth2' OR severity = 3")->fetchAll();

    foreach ($query as $row) {
      $lineData = [$row->wid, $row->uid, $row->type, $row->message, $row->variables, $row->severity, $row->link, $row->location, $row->referer, $row->hostname, $row->timestamp];
      array_walk($lineData, static function(&$value) {
        self::mofilterData($value);
      });

      $excelData .= implode("\t", array_values($lineData)) . "\n";
    }

    // Headers for download.
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=\"$fileName\"");

    // Render excel data.
    echo $excelData;
    exit;
  }

}

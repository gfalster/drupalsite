<?php

namespace Drupal\hot_stocks\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;


/**
 * Configurations to interact with Charles Scwhab API.
 */
class HotStocksConfigForm extends ConfigFormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'hot_stocks_config_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('hot_stocks.settings');
        $form['category'] = [
            '#type' => 'checkboxes',
            '#title' => $this->t('Data Category'),
            '#options' => [
                'stocks' => $this->t('Stocks'),
                'bonds' => $this->t('Bonds'),
                'options' => $this->t('Options'),
            ],
        ];        
        $form['api_url'] = [
            '#type' => 'textfield',
            '#title' => $this->t('API URL'),
            '#default_value' => $config->get('api_url'),
            '#description' => $this->t('The URL of the remote REST server.'),
        ];
        $form['api_key'] = [
            '#type' => 'textfield',
            '#title' => $this->t('API Key'),
            '#default_value' => $config->get('api_key'),
            '#description' => $this->t('The API key to authenticate with the remote REST server.'),
        ];
        $form['api_secret'] = [
            '#type' => 'textfield',
            '#title' => $this->t('API Secret'),
            '#default_value' => $config->get('api_secret'),
            '#description' => $this->t('The API secret to authenticate with the remote REST server.'),
        ];
        return parent::buildForm($form, $form_state);
    }   

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $this->config('hot_stocks.settings')
            ->set('api_url', $form_state->getValue('api_url'))
            ->set('api_key', $form_state->getValue('api_key'))
            ->set('api_secret', $form_state->getValue('api_secret'))
            ->save();

            //$config = $this->configFactory()->getEditable('hot_stocks.settings');
            //$config->set('api_url', $form_state->getValue('api_url'));
        parent::submitForm($form, $form_state);
    }
    
    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames() {
        return [
            'hot_stocks.settings',
        ];
    }
}
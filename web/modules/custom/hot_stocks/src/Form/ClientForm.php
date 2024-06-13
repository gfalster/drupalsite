<?php
namespace Drupal\hot_stocks\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

/**
 * User for form for personalization with with Charles Scwhab API.
 * 
 */
class ClientForm extends FormBase {

    /**
     * {@inheritdoc}
     */
    public function getFormId() {
        return 'hot_stocks_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        
        $config = $this->config('hot_stocks.settings');

        $form['fullname'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Full Name'),
            '#default_value' => $config->get('name'),
            '#description' => $this->t('Your name.'),
        ];
        $form['email'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Email'),
            '#default_value' => $config->get('email'),
            '#description' => $this->t('The API key to authenticate with the remote REST server.'),
        ];

        $form['brokage'] = [
            '#type' => 'checkboxes',
            '#title' => $this->t('Brokage Account'),
            '#options' => [
                'charlesschwab' => $this->t('Charles Schwab'),
                'ninjatrader' => $this->t('NinjaTrader'),
                'robinhood' => $this->t('Robinhood'),
            ],
        ];        

        $form['offering'] = [
            '#type' => 'checkboxes',
            '#title' => $this->t('Service of Interest'),
            '#options' => [
                'learning' => $this->t('Learn financial markets'),
                'advicement' => $this->t('Seek financial advice'),
                'investment' => $this->t('Invest in finacial markets'),
                'management' => $this->t('Work with portfolio managers'),               
            ],
            '#description' => $this->t('The service offerings you are interested in.'),
        ];
        $form['actions'] = ['#type' => 'actions'];
        $form['actions']['send'] = [
          '#type' => 'submit',
          '#value' => $this->t('Submit'),
          '#attributes' => [
            'class' => [
              'use-ajax',
              'button--primary',
            ],
          ],
          '#ajax' => [
            'callback' => [$this, 'submitModalFormAjax'],
            'event' => 'click',
          ],
        ];
    
        $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

        return $form;
    }   

    public function submitForm(array &$form, FormStateInterface $form_state) {
        $this->config('hot_stocks.settings')
            ->set('brokerage', $form_state->getValue('brokerage'))
            ->set('name', $form_state->getValue('name'))
            ->set('email', $form_state->getValue('email'))
            ->set('offerings', $form_state->getValue('offerings'))
            ->save();
        
    }

    /**
     * {@inheritdoc}
     */
    public function validateForm(array &$form, FormStateInterface $form_state) {
        $api_url = $form_state->getValue('api_url');
    }
}
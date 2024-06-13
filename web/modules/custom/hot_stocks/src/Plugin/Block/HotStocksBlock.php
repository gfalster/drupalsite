<?php

namespace Drupal\hot_stocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Entity\EntityDisplayRepository;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a hot stocks block.
 *
 * @Block(
 *   id = "hot_stocks_block",
 *   admin_label = @Translation("Hot Stocks"),
 *   category = @Translation("Custom"),
 * )
 */
class HotStocksBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * The renderer.
   *
   * @var \RendererInterface
   */
  protected $renderer;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepository
   */
  protected $entityDisplayRepository;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $currentRouteMatch;

  /**
   * The hoot stocks block configuration entity.
   * @var \Drupal\hot_stocks\Form\ClientForm
   */
  protected $clientForm;

  /**
   * Constructs the plugin instance.
   * 
   * @param array $configuration
   *  A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   * The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   * The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * The entity manager.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   * The form builder.
   * @param \RendererInterface $renderer
   * The renderer.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $current_route_match
   * The current route match.
   */
  public function __construct(array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    FormBuilderInterface $form_builder,
    RendererInterface $renderer,
    CurrentRouteMatch $current_route_match) 
  {

    $this->entityTypeManager = $entity_type_manager;
    $this->formBuilder = $form_builder;
    $this->renderer = $renderer;
    $this->currentRouteMatch = $current_route_match;

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

    
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('config.factory'),
      $container->get('form_builder'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $default_form = $this->getClientForm();

    return [
      'label' => $this->t('Hot Stocks'),
      'client_form' => $default_form,
      'form_display' => 'default',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    $output = [
      'description'=> [
        '#markup' => $this->configuration['Client Informaiton Form'],
      ],
    ];

    $output['form'] = $this->formBuilder->getForm('Drupal\hot_stocks\Form\ClientForm');
    return $output;
  }

}

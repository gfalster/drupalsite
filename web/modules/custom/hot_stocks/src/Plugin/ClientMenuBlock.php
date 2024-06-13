<?php

namespace Drupal\hot_stocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\taxonomy\TermInterface;

/**
 * Provides a 'ClientMenuBlock' block.
 *
 * @Block(
 *   id = "client_menu_block",
 *   admin_label = @Translation("Client Menu Block"),
 *   category = @Translation("Custom"),
 * )
 */
class ClientMenuBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['#theme'] = 'client_menu_block';
    $build['#client_menu'] = $this->getClientMenu();
    return $build;
  }

  /**
   * Get the client menu.
   *
   * @return array
   *   The client menu.
   */
  protected function getClientMenu() {
    $menu = [];
    $menu['home'] = [
      'title' => $this->t('Home'),
      'url' => Url::fromRoute('<front>'),
    ];
    $menu['about'] = [
      'title' => $this->t('About'),
      'url' => Url::fromRoute('entity.node.canonical', ['node' => 1]),
    ];
    $menu['services'] = [
      'title' => $this->t('Services'),
      'url' => Url::fromRoute('entity.node.canonical', ['node' => 2]),
    ];
    $menu['contact'] = [
      'title' => $this->t('Contact'),
      'url' => Url::fromRoute('entity.node.canonical', ['node' => 3]),
    ];
    return $menu;
  }
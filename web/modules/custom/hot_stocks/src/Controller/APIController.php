<?php

namespace Drupal\hot_stocks\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;
use Drupal\hot_stocks\Request\StocksRequest;
use Drupal\hot_stocks\Response\StocksResponse;

/**
 * Controller for the hot stocks API.
 */
class APIController extends ControllerBase {

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The Stocks Request.
   *
   * @var \Drupal\hot_stocks\Request\StocksRequest
   */
  protected $stocksRequest;

  /**
   * The Stocks Response.
   *
   * @var \Drupal\hot_stocks\Response\StocksResponse
   */
  protected $stocksResponse;

  /**
   * Constructor.
   *
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   * @param \Drupal\hot_stocks\Request\StocksRequest $stocksRequest
   *   The Stocks Request.
   * @param \Drupal\hot_stocks\Response\StocksResponse $stocksResponse
   *   The Stocks Response.
   */
  public function __construct(LoggerInterface $logger, StocksRequest $stocksRequest, StocksResponse $stocksResponse) {
    $this->logger = $logger;
    $this->stocksRequest = $stocksRequest;
    $this->stocksResponse = $stocksResponse;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('logger.factory')->get('hot_stocks'),
      $container->get('hot_stocks.stocks_request'),
      $container->get('hot_stocks.stocks_response')
    );
  }

  /**
   * Get the hot stocks.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   */
  public function getHotStocks() {
    $this->logger->info('Getting hot stocks');

    $response = $this->stocksRequest->getStocks();

    return new JsonResponse($this->stocksResponse->getResponse($response));
  }
}

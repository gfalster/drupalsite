<?php

namespace Drupal\hot_stocks\api;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Symphony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;


/**
 * Here we interact with Charles Scwhab API.
 */
class HotStocksCWApi {

    /**
     * The client used to send HTTP requests.
     * 
     * @var \GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * Configurations.
     * 
     * @var \Drupal\Core\Config\ConfigFactoryInterface
     */
    protected $config;

    /**
     * Logger.
     * 
     * @var \Drupal\Core\Logger\LoggerChannelFactory
     */
    protected $logger;

    /**
     * Request stack.
     * 
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    /**
     * The URL of the remote REST server.
     * 
     * @var string
     */
    protected $remoteUrl;

    /**
     * The headers used when sending HTTP request.
     * 
     * The headers are very important when communicating with the REST server.
     * They are used by the server the verify that it supports the sent data
     * (Content-Type) and that it supports the type of response that the client
     * wants.
     * 
     * @var array
     */
    

    protected $clientHeaders = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ];

    /**
     * The authentication parameters used when calling the remote REST server.
     * 
     * @var array
     */
    protected $clientAuth;

    /**
     * The constructor.
     * 
     * @param \GuzzleHttp\ClientInterface $client
     *   The HTTP client.
     * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
     *   The Config Factory.
     * @param \Drupal\Core\Logger\LoggerChannelFactory $logger
     *   The Logger.
     * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
     *   The Request Stack.
     */
    public function __construct(ClientInterface $client, ConfigFactoryInterface $config_factory, LoggerChannelFactory $logger, RequestStack $request_stack) {
        $this->client = $client;
        $this->config = $config_factory->get('hot_stocks.settings');
        $this->logger = $logger->get('hot_stocks');
        $this->requestStack = $request_stack;
        $this->remoteUrl = $this->config->get('remote_url');

    }

    /**
     * Get the stock data from the remote server.
     * 
     * @param string $symbol
     *   The stock symbol.
     * 
     * @return array
     *   The stock data.
     */
    public function getStockData($symbol) {
        $url = $this->remoteUrl . '/stock/' . $symbol;
        $response = $this->client->get($url, [
            'headers' => $this->clientHeaders,
        ]);

        $data = Json::decode($response->getBody());
        return $data;

    }

    /**
     * Create nodes of stock data from the remote server.
     * 
     * @param string $node_type
     *   Contains the node type.
     * 
     * @return array
     *   A HTTP response.
     * 
     * @throws \GuzzleHttp\Exception\ConnectException
     * @@throws \InvalidArgumentException
     */
    public function createStockNodes($node_type) {
        $url = $this->remoteUrl . '/stocks';
        $response = $this->client->get($url, [
            'headers' => $this->clientHeaders,
        ]);

        $data = Json::decode($response->getBody());
        $node_storage = \Drupal::entityTypeManager()->getStorage('node');

        foreach ($data as $stock) {
            $node = $node_storage->create([
                'type' => $node_type,
                'title' => $stock['name'],
                'body' => $stock['description'],
            ]);
            $node->save();
        }

        return new Response('Stocks created', 200);
    

}
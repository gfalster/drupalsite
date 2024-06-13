<?php


namespace Drupal\hot_stocks\Request;

/**
 * API Request class.
 * 
 * @package Drupal\hot_stocks\Request
 */
abstract class Request {
    /**
     * Token
     * 
     * @var string
     */
    protected $token;

    /**
     * Base URL
     * 
     * @var string
     */
    protected $baseUrl;

    /**
     * Request parameters
     * 
     * @var array
     */
    protected $parameters = [];
    
    /**
     * Constructor
     * 
     * @param string $token
     * @param string $baseUrl
     * @param array $parameters
     */
    public function __construct(string $token, string $baseUrl, array $parameters = []) {
        $this->token = $token;
        $this->baseUrl = $baseUrl;
        $this->parameters = $parameters;

    }

}
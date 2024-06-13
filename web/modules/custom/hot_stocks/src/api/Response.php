<?php

namespace Drupal\hot_stocks\Response;

/**
 * API Response class.
 * 
 * @package Drupal\hot_stocks\Response
 */
abstract class Response {
    /**
     * Response data
     * 
     * @var array
     */
    protected $data = [];
    
    /**
     * Response status
     * 
     * @var int
     */
    protected $status;

    /**
     * Response message
     * 
     * @var string
     */
    protected $message;

    /**
     * Constructor
     * 
     * @param object $response
     */
    public function __construct(array $response) {
        $this->data = $response['data'];
        $this->status = $response['status'];
        $this->message = $response['message'];
    }

    /**
     * Get data
     * 
     * @return array
     */
    public function getData(): array {
        return $this->data;
    }

    /**
     * Get status
     * 
     * @return int
     */ 
    public function getStatus(): int {
        return $this->status;
    }

    /**
     * Get message
     * 
     * @return string
     */
    public function getMessage(): string {
        return $this->message;
    }
    
    /**
     * Get response
     * 
     * @return array
     */
    public function getResponse(): array {
        return [
            'data' => $this->data,
            'status' => $this->status,
            'message' => $this->message
        ];
    }

}
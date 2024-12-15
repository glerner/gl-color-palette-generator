<?php

/**
 * Mock WP_REST_Request class for testing
 */
class WP_REST_Request {
    private $params = [];
    private $method;
    private $route;

    public function __construct($method = '', $route = '', $params = []) {
        $this->method = $method;
        $this->route = $route;
        $this->params = $params;
    }

    public function get_param($key) {
        return isset($this->params[$key]) ? $this->params[$key] : null;
    }

    public function get_params() {
        return $this->params;
    }

    public function set_param($key, $value) {
        $this->params[$key] = $value;
    }

    public function get_method() {
        return $this->method;
    }

    public function get_route() {
        return $this->route;
    }
}

<?php
namespace WoR;

class Main {
  private static $instance;
  private $current_request;
  private $routes;

  public function __construct() {
    $this->set_vars();
  }

  public static function get_instance() {
    if (!isset(self::$instance)) {
      self::$instance = new Main();
    }

    return self::$instance;
  }

  public static function init() {
    //add_action('send_headers', array(__CLASS__, 'wor_headers'));
  }

  public static function wor_headers($wp_instance) {
    Template::delegate();
  }

  private function set_vars() {
    if (!isset($_SERVER['REQUEST_METHOD']) || 
        !isset($_SERVER['REQUEST_URI']) ||
        !isset($_SERVER['HTTP_USER_AGENT'])
    ) {
      return;
    }

    $this->current_request = new Route(array(
      'method'      => strtolower($_SERVER['REQUEST_METHOD']),
      'path'        => $_SERVER['REQUEST_URI'],
      'real_agent'  => $_SERVER['HTTP_USER_AGENT']
    ));

    add_action('send_headers', array(__CLASS__, 'wor_headers'));
  }

  public function add_routes() {
    $routes = func_get_args();

    \_u::each($routes, function($route, $index) {
      $method = key($route);
      $items = $route[$method];

      if (!Method::has_method(strtolower($method))) continue;

      $route = new Route($items);
      $route->method  = new Method($method);

      if (isset($items['headers']) && is_array($items['headers'])) {
        $route->headers = Headers::set_for_route($items['headers']);
      }

      $this->routes[] = $route;
    });
  }

  public function __get($prop) {
    return $this->$prop;
  }

  public function __set($prop, $val) {
    $this->$prop = $val;
  }
}


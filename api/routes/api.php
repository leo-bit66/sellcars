<?php

class Router {

    private $requestMethod;
    private $requestUri;
    private $routes = [];
    private $middleware = [];

    public function __construct($requestMethod, $requestUri) {
        $this->requestMethod = $requestMethod;
        $this->requestUri = $requestUri;
    }

    public function get($uri, $action, $middleware = []) {
        $this->routes['GET'][$uri] = ['action' => $action, 'middleware' => $middleware];
    }

    public function post($uri, $action, $middleware = []) {
        $this->routes['POST'][$uri] = ['action' => $action, 'middleware' => $middleware];
    }

    public function put($uri, $action, $middleware = []) {
        $this->routes['PUT'][$uri] = ['action' => $action, 'middleware' => $middleware];
    }

    public function delete($uri, $action, $middleware = []) {
        $this->routes['DELETE'][$uri] = ['action' => $action, 'middleware' => $middleware];
    }

    public function middleware($name, $callback) {
        $this->middleware[$name] = $callback;
    }

    public function resolve() {
        $method = strtoupper($this->requestMethod);

        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $uri => $route) {
                $pattern = $this->convertPatternToRegex($uri);

                if (preg_match($pattern, $this->requestUri, $matches)) {
                    $matches = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY); // Filter only named capture groups
                    foreach ($route['middleware'] as $middlewareName) {
                        if (isset($this->middleware[$middlewareName])) {
                            $this->middleware[$middlewareName]();
                        }
                    }
                    $this->executeAction($route['action'], $matches);
                    return;
                }
            }
        }

        http_response_code(404);
        exit();
    }

    private function convertPatternToRegex($pattern) {
        $pattern = preg_replace('/\//', '\/', $pattern);
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '(?P<$1>[a-zA-Z0-9_]+)', $pattern);
        return '/^' . $pattern . '$/';
    }

    private function executeAction($action, $params) {
        list($controller, $method) = explode('@', $action);

        if (class_exists($controller)) {
            $instance = new $controller();

            if (method_exists($instance, $method)) {
                call_user_func_array([$instance, $method], $params);
            } else {
                // Handle 404 Not Found for the method
                http_response_code(404);
            }
        } else {
            http_response_code(404);
        }
    }

}

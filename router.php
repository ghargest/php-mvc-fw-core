<?php

namespace ghargest\phpmvc;

use ghargest\phpmvc\exception\NotFoundException;

class Router {

    public Request $request;
    public Response $response;
    private array $routes = [];

    public function __construct(Request $request, Response $response) {
        
        $this->request = $request;
        $this->response = $response;
    }

    public function get($path, $callback) {

        $this->routes['get'][$path] = $callback;
    }

    public function post($path, $callback) {

        $this->routes['post'][$path] = $callback;
    }

    public function getCallback() {

        $path = trim($this->request->path(), '/');
        $method = $this->request->method();
        $routes = $this->routes[$method] ?? [];
        $routeParams = false;

        foreach ($routes as $route => $callback) {
            $route = trim($route, '/');
            $routeNames = [];
            if (!$route) { continue; }
            if (preg_match_all('/\{(\w+)(:[^}]+)?}/', $route, $matches)) { $routeNames = $matches[1]; }
            $routeRegex = "@^" . preg_replace_callback('/\{\w+(:([^}]+))?}/', fn($m) => isset($m[2]) ? "({$m[2]})" : "(\w+)", $route) . "$@";
            if (preg_match_all($routeRegex, $path, $valueMatches)) {
                $values = [];
                for ($i = 1; $i < count($valueMatches); $i++) {
                    $values[] = $valueMatches[$i][0];
                }
                $this->request->setRouteParams(array_combine($routeNames, $values));
                return $callback;
            }
        }
        return false;
    }

    public function resolve() {

        $path = $this->request->path();
        $method = $this->request->method();
        $callback = $this->routes[$method][$path] ?? false;

        if (!$callback) {
            $callback = $this->getCallback();
            if (!$callback) { throw new NotFoundException(); }
        }

        if (is_string($callback)) { return Application::$app->view->renderView($callback); }

        if (is_array($callback)) {
            /** @var \ghargest\phpmvc\Controller $controller */
            $controller = new $callback[0]();
            Application::$app->controller = $controller;
            $controller->action = $callback[1];
            $callback[0] = $controller;

            foreach ($controller->getMiddlewares() as $middleware) {
                $middleware->execute();
            }
        }

        return call_user_func($callback, $this->request, $this->response);
    }
}
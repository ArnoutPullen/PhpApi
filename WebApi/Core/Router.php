<?php

/**
 * Created by PhpStorm.
 * User: Gerjan
 * Date: 28-1-2017
 * Time: 23:03
 */
class Router
{

    private $route;
    private $dependencyResolver;
    private $onRouteFoundCallback;
    private $onRouteNotFoundCallback;
    private $middleWare = [];

    function __construct($prefix = null)
    {
        $this->route = $this->getCurrentUri();
        if ($prefix != null) {
            $this->route = str_replace($prefix, '', $this->route);
        }
        $this->dependencyResolver = new DependencyResolver();
    }

    private function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    public function run()
    {
        foreach ($this->middleWare as $middleware) {
            $middleware($this->getCurrentUri(), $this->getRequestMethod(), $this->getRequestHeaders());
        }
        $routeFound = false;
        foreach (get_declared_classes() as $class) {
            if ($this->endsWith($class, "Controller")) {
                $reflectionClass = new ReflectionClass($class);
                $controllerRoute = CustomReflection::getAttributes($reflectionClass)['route'];
                foreach ($reflectionClass->getMethods() as $method) {
                    if ($method->name != "__construct") {
                        $reflectionMethod = new ReflectionMethod($method->class, $method->name);
                        $attributes = CustomReflection::getAttributes($reflectionMethod);
                        $methodRoute = $attributes['route'];
                        $requestMethod = $attributes['method'];
                        //die($this->route);
                        $pattern = '@^' . preg_replace('/\:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\-\_]+)', $controllerRoute . $methodRoute) . '[/]?$@D';
                        if (preg_match($pattern, $this->route, $matches) && strtolower($this->getRequestMethod()) == strtolower($requestMethod)) {
                            array_shift($matches);
                            $methodName = $method->name;
                            $routeFound = true;
                            call_user_func_array($this->onRouteFoundCallback, [$class, $methodName, $requestMethod, $matches, $attributes, $this->getRequestHeaders()]);
                        }
                    }
                }

            }
        }
        if (!$routeFound) {
            call_user_func_array($this->onRouteNotFoundCallback, []);
        }
    }

    private function endsWith($haystack, $needle)
    {
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }

    public function getRequestHeaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if ((substr($name, 0, 5) == 'HTTP_') || ($name == 'CONTENT_TYPE') || ($name == 'CONTENT_LENGTH')) {
                $headers[str_replace(array(' ', 'Http'), array('-', 'HTTP'), ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }

    public function UseMiddleWare($func)
    {
        $this->middleWare[] = $func;
    }

    public function getRequestMethod()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if ($_SERVER['REQUEST_METHOD'] == 'HEAD') {
            ob_start();
            $method = 'GET';
        } elseif ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $headers = $this->getRequestHeaders();
            if (isset($headers['X-HTTP-Method-Override']) && in_array($headers['X-HTTP-Method-Override'], array('PUT', 'DELETE', 'PATCH'))) {
                $method = $headers['X-HTTP-Method-Override'];
            }
        }
        return $method;
    }

    public function onRouteFound($onRouteFoundCallback)
    {
        $this->onRouteFoundCallback = $onRouteFoundCallback;
    }

    public function onRouteNotFound($onRouteNotFoundCallback)
    {
        $this->onRouteNotFoundCallback = $onRouteNotFoundCallback;
    }

    private function getCurrentUri()
    {
        $basePath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)) . '/';
        $uri = substr($_SERVER['REQUEST_URI'], strlen($basePath));
        if (strstr($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }
        return '/' . $uri;
    }
}
<?php

namespace Pebble;

use Pebble\Exception\NotFoundException;
use Exception;

class Router
{

    /**
     * Holding routes
     */
    private $routes = [];

    /**
     * Allowed methods
     */
    private $allowedMethods = [
        'GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'CONNECT', 'OPTIONS', 'TRACE', 'PATCH',
    ];

    private $method = null;

    /**
     * Set allowed methods
     */
    public function setAllow(array $allow)
    {
        $this->allowedMethods = $allow;
    }

    /**
     * Check if a string starts with a neddle, ':param'
     */
    private function startsWith($haystack, $needle)
    {
        return substr_compare($haystack, $needle, 0, strlen($needle)) === 0;
    }

    /**
     * Check if a string is in the format ':param', ':username' or not
     */
    private function isParam($str)
    {
        if ($this->startsWith($str, ':')) {
            return true;
        }
    }

    /**
     * Split parts of an URL into an array
     */
    private function getUrlParts($route)
    {
        // Remove query string
        $route = strtok($route, '?');
        $url_parts = explode('/', $route);
        $url_parts_filterd = [];
        foreach ($url_parts as $url_part) {
            if ($url_part) {
                $url_parts_filterd[] = $url_part;
            }
        }
        return $url_parts_filterd;
    }

    /**
     * Check if a route with REQUEST_METHOD is set
     */
    private function filterRouteByRequestMethod()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        if (!isset($this->routes[$method])) {
            $this->routes[$method] = [];
        }   
    }

    /**
     * Filter out routes that does not have the correct length
     * /test/:hello/:world (3 in length)
     */
    private function filterRoutesByPartsLength()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $length = count($this->getUrlParts($_SERVER['REQUEST_URI']));
        
        $valid_routes = [];
        foreach ($this->routes[$method] as $key => $route) {
            $route_parts_length = count($route['parts']);

            if ($route_parts_length === $length) {

                // Add params array
                $route['params'] = [];
                $valid_routes[] = $route;
            }
        }

        $this->routes[$method] = $valid_routes;

    }

    /**
     * Compare each route part with each REQUEST_URI part and filter
     * out routes that does not match each and every URL part
     */
    private function filterRoutesByIndexPart($index, $part)
    {
        $method = $_SERVER['REQUEST_METHOD'];

        $valid_routes = [];
        foreach ($this->routes[$method] as $route) {

            $route_parts = $route['parts'];
            
            if ($this->isParam($route_parts[$index])) {

                // Extract value of param
                $param = ltrim($route_parts[$index], ':');
                $route['params'][$param] = $part; 
                $valid_routes[] = $route;
            }

            if ($route_parts[$index] == $part) {
                $valid_routes[] = $route;
            }
        }

        $this->routes[$method] = $valid_routes;
    }

    /**
     * Filter routes part by part
     */
    private function filterRoutesByParts()
    {     
        $current_url_parts = $this->getUrlParts($_SERVER['REQUEST_URI']);       
        foreach ($current_url_parts as $index => $part) {
            $this->filterRoutesByIndexPart($index, $part);
        }  
    }

    /**
     * Add a single route
     * `$router->add('GET', '/some/route/with/:param', \Some\Namespace::class, 'classMethod')`
     */
    public function add(string $request_method, string $route, string $class, string $class_method)
    {

        $this->request_method = $request_method;
        $this->routes[$request_method][] = [
            'route' => $route,
            'class' => $class,
            'method' => $class_method,
            'parts' => $this->getUrlParts($route),

        ];
    }

    /**
     * When all routes are loadedm then run the application
     * `$router->run()`
     */
    public function run()
    {

        $this->filterRouteByRequestMethod();    
        $this->filterRoutesByPartsLength();
        $this->filterRoutesByParts();

        $method = $_SERVER['REQUEST_METHOD'];
        
        if (empty($this->routes[$method])) {
            throw new NotFoundException('The page does not exist');
        } else {
            $routes = $this->routes[$method];
       
            foreach($routes as $route) {

                $params = $route['params'];
                $class_method = $route['method'];
                $class = $route['class'];
                $object = new $class();

                $object->$class_method($params);                    
                
            }
        }
    }
}

<?php

class GTKRoute
{
    public $prefix;
    public $suffix;
    public $isParameterized;
    public $childRoutes;
    public $handler;

    public function __construct($prefix, $isParameterized, $handler)
    {
        $this->prefix          = $prefix;
        $this->isParameterized = $isParameterized;
        $this->handler         = $handler;
    }

    public function canHandleRoute($partialRequestURI)
    {
        $prefixLength = strlen($this->prefix);

        if (substr($partialRequestURI, 0, $prefixLength) == $this->prefix)
        {
            return true;
        }

        return false;
    }

    public function handleRoute($requestURI)
    {

    }

    public function addChildRoute($route)
    {
        $this->childRoutes[] = $route;
    }
}

class GTKRouter 
{
    public $pattern;
    public $allowDefaultCallback = true;
    public $parent;

    private $routes  = [];
    private $routers = [];
    private $callback;

    public function __construct($options = []) 
    {
        if (isset($options['pattern']))
        {
            $this->pattern = $options['pattern'];
        }

        if (isset($options['callback']))
        {
            $this->callback = $options['callback'];
        }   
    }

    public function addRouter($routerPattern, $subRoutesClosure) 
    {
        $debug = false;


        $router          = new GTKRouter(); 
        $router->pattern = $routerPattern;
        $router->parent  = $this;

        if ($debug)
        {
            gtk_log("Adding router for pattern: $routerPattern - $router->pattern");
        }

        $subRoutesClosure($router);

        if (isset($this->routers[$routerPattern]))
        {
            die("Router already exists for pattern: $routerPattern");
        }

        $this->routers[$routerPattern] = $router;
    }

    public function addRoute($pattern, $handler) 
    {
        $this->routes[$pattern] = $handler;
    }

    public function respondsToRoute($uri) 
    {
        return $this->route($uri, null, false);
    }

    public function route($uri, &$exploded = null, $notTesting = true) 
    {
        $debug = false;


        if (!$exploded)
        {
            $exploded = explode('/', $uri);

            if ($exploded[0] == '')
            {
                array_shift($exploded);
            }
        }

        if ($debug)
        {
            gtk_log("Routing: $uri");
            gtk_log("Exploded: ".serialize($exploded));
            gtk_log("Router 0: ".$exploded[0]);
        }

        if (isset($this->routers[$exploded[0]]))
        {
            $handler = $this->routers[$exploded[0]];

            if ($handler)
            {
                if ($debug)
                {
                    gtk_log("Found router!");
                }
    
                array_shift($exploded);
                return $handler->route(implode("/", $exploded), $exploded);
            }
        }

        if ($uri == $this->pattern)
        {   
            if ($notTesting)
            {
                $this->callback();
            }
            return $this;
        }

        gtk_log("Checking for URI: $uri");

        foreach ($this->routes as $pattern => $handler) 
        {
            if ($debug)
            {
                gtk_log("Checking pattern / handler: $pattern");
            }


            if (strstr("%", $pattern))
            {
                die('Pattern contains % - not yet supported');
            }
            else if ($pattern == $uri)
            {
                if ($notTesting)
                {
                    if (is_callable($handler))
                    {
                        die($handler($uri));
                    }
                    else if ($handler instanceof GTKRouter) 
                    {
                        return $handler->route($uri, $exploded);
                    } 
                    else if (is_array($handler))
                    {
                        if (array_key_exists("handler", $handler))
                        {
                            $handler = $handler["handler"];
                            $result = callNestedFuncArray($handler);
                            return $result;
                        }                        
                    }
                }

                return $handler;
            }
        }

        return false;
    }

    public function callback()
    {
        if ($this->callback)
        {
            call_user_func($this->callback);
        }
        else if ($this->allowDefaultCallback)
        {
            $this->defaultCallback();
        }
        else
        {
            $this->notFoundCallback();
        }
    }

    public function notFoundCallback() 
    {
        echo "404 Not Found";
    }

    public function defaultCallback() {
        echo "Routes: \n";
        foreach ($this->routes as $pattern => $handler) {
            echo "- $pattern\n";
        }
    }
}



function callNestedFuncArray($nestedArray) 
{
    $debug = false;

    if ($debug)
    {
        gtk_log("Calling nested func array: ".serialize($nestedArray));
        gtk_log("Count nested array: ".count($nestedArray));

    }

    if (is_array($nestedArray) && gtk_count($nestedArray) == 2) 
    {
        $callback = $nestedArray[0];
        $params = is_array($nestedArray[1]) ? $nestedArray[1] : [$nestedArray[1]];
        return call_user_func_array($callback, $params);
    }
    throw new InvalidArgumentException('Invalid nested array structure');
}

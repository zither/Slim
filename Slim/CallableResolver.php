<?php

namespace Slim;

use Pimple\Container;

class CallableResolver 
{
    
    protected $container;
    
    protected $toResolve;
    
    protected $resolved;
    
    public function __construct($toResolve, Container $container)
    {
        $this->toResolve = $toResolve;
        $this->container = $container;
    }
    
    public function resolve()
    {
        preg_match('!^([^\:]+)\:([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)$!', $this->toResolve, $matches);
        $class = $matches[1];
        $method = $matches[2];
        
        if (isset($this->container[$class])) {
            $this->resolved = [$this->container[$class], $method];
        } else {
            if (!class_exists($class)) {
                throw new \RuntimeException('Route callable class does not exist');
            }
            $this->resolved = [new $class, $method];
        }
        if (!is_callable($this->resolved)) {
            throw new \RuntimeException('Route callable method does not exist');
        }
    }
    
    public function __invoke() 
    {
        if(!isset($this->resolved)) {
            $this->resolve();
        }
        return call_user_func_array($this->resolved, func_get_args());
    }
    
}
<?php

/**
 * Created by PhpStorm.
 * User: Gerjan
 * Date: 28-1-2017
 * Time: 20:51
 */
class DependencyResolver
{
    private $cache = [];

    function __construct()
    {

    }

    public function resolve($type)
    {
        $reflectionClass = new ReflectionClass($type);
        $params = CustomReflection::getConstructorParameters($reflectionClass);
        $args = [];
        foreach ($params as $param) {
            $cachedItem = $this->getFromCache($param->name);
            if ($cachedItem == null) {
                $args[] = $this->resolve($param->name);
            } else {
                $args[] = $cachedItem;
            }
        }
        $instance = $reflectionClass->newInstanceArgs($args);
        $this->cache[] = $instance;
        return $instance;
    }


    private function getFromCache($type)
    {
        foreach ($this->cache as $class) {
            if (get_class($class) == $type) {
                return $class;
            }
        }
        return null;
    }
}






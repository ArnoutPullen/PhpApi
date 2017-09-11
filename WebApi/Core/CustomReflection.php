<?php

/**
 * Created by PhpStorm.
 * User: Gerjan
 * Date: 28-1-2017
 * Time: 20:44
 */
class CustomReflection
{
    public static function getAttributes($method)
    {
        $attribute = [];
        $regex = '%^\s*\*\s*@+(?P<method>/?(?:[a-z0-9])+)+\("+(?P<value>/?(?:[a-zA-Z0-9~@#$^*()_+=[\]{}|\\,.?/: -]+/?)+)+"\)\s*$%im';
        preg_match_all($regex, $method->getDocComment(), $matches);
        for ($i = 0; $i < count($matches["method"]); $i++) {

            $attribute[$matches["method"][$i]] = $matches["value"][$i];

        }
        return $attribute;
    }

    public static function getConstructorParameters(ReflectionClass $reflectionClass)
    {
        $parameters = [];
        $reflectionMethod = $reflectionClass->getConstructor();
        foreach ($reflectionMethod->getParameters() AS $parameter) {
            $parameterClass = $parameter->getClass();
            if ($parameterClass != null) {
                $parameters[] = $parameterClass;
            }
        }
        return $parameters;
    }
}

class ClassInfo extends ReflectionClass
{

    public function getAttributes()
    {
        $attribute = [];
        $regex = '%^\s*\*\s*@+(?P<method>/?(?:[a-z0-9])+)+\("+(?P<value>/?(?:[a-zA-Z0-9~@#$^*()_+=[\]{}|\\,.?/: -]+/?)+)+"\)\s*$%im';
        preg_match_all($regex, $this->getDocComment(), $matches);
        for ($i = 0; $i < count($matches["method"]); $i++) {

            $attribute[$matches["method"][$i]] = $matches["value"][$i];

        }
        return $attribute;
    }

    public function getConstructorParameterTypes()
    {
        $parameters = [];
        $reflectionMethod = $this->getConstructor();
        foreach ($reflectionMethod->getParameters() AS $parameter) {
            $parameterClass = $parameter->getClass();
            if ($parameterClass != null) {
                $parameters[] = $parameterClass;
            }
        }
        return $parameters;
    }

    public function getConstructorParameterNames()
    {
        $parameters = [];
        $reflectionMethod = $this->getConstructor();
        foreach ($reflectionMethod->getParameters() AS $parameter) {
            $parameterClass = $parameter->getClass();
            if ($parameterClass != null) {
                $parameters[] = $parameterClass->name;
            }
        }
        return $parameters;
    }
}

class PropertyInfo extends ReflectionProperty
{
    public function getAttributes()
    {
        $attribute = [];
        $regex = '%^\s*\*\s*@+(?P<method>/?(?:[a-z0-9])+)+\("+(?P<value>/?(?:[a-zA-Z0-9~@#$^*()_+=[\]{}|\\,.?/: -]+/?)+)+"\)\s*$%im';
        preg_match_all($regex, $this->getDocComment(), $matches);
        for ($i = 0; $i < count($matches["method"]); $i++) {

            $attribute[$matches["method"][$i]] = $matches["value"][$i];

        }
        return $attribute;
    }
}

class MethodInfo extends ReflectionMethod
{

    public function getAttributes()
    {
        $attribute = [];
        $regex = '%^\s*\*\s*@+(?P<method>/?(?:[a-z0-9])+)+\("+(?P<value>/?(?:[a-zA-Z0-9~@#$^*()_+=[\]{}|\\,.?/: -]+/?)+)+"\)\s*$%im';
        preg_match_all($regex, $this->getDocComment(), $matches);
        for ($i = 0; $i < count($matches["method"]); $i++) {

            $attribute[$matches["method"][$i]] = $matches["value"][$i];

        }
        return $attribute;
    }

}
<?php

namespace OData;

/**
 * Created by PhpStorm.
 * User: Gerjan
 * Date: 17-8-2017
 * Time: 11:53
 */
class RangeVariableToken
{
    public $property = null;

    function __construct($property)
    {
        $this->property = $property;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: Gerjan
 * Date: 17-8-2017
 * Time: 11:26
 */

namespace OData;


class ContainsFunction
{
    function __construct($identifier, $string)
    {
        $this->identifier = $identifier;
        $this->string = $string;
    }
}
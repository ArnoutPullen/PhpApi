<?php

namespace OData;

/**
 * Created by PhpStorm.
 * User: Gerjan
 * Date: 29-7-2017
 * Time: 17:06
 */
class UnaryExpression
{
    public $kind;
    public $operand;

    function __construct($kind, $operand)
    {
        $this->kind = $kind;
        $this->operand = $operand;
    }
}

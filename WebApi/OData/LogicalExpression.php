<?php

namespace OData;

/**
 * Created by PhpStorm.
 * User: Gerjan
 * Date: 29-7-2017
 * Time: 16:16
 */
class LogicalExpression
{
    public $left;
    public $right;
    public $type;

    function __construct($type, $left, $right)
    {
        $this->type = $type;
        $this->left = $left;
        $this->right = $right;
    }
}

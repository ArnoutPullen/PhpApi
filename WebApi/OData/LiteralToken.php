<?php

namespace OData;

/**
 * Created by PhpStorm.
 * User: Gerjan
 * Date: 17-8-2017
 * Time: 11:26
 */
class LiteralToken
{
    public $type;
    public $text;

    function __construct($type, $text)
    {
        $this->text = $text;
        $this->type = $type;
    }
}

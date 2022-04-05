<?php

namespace OData;

/**
 * Created by PhpStorm.
 * User: Gerjan
 * Date: 17-8-2017
 * Time: 11:54
 */
class InnerPathToken
{

    public $propertyName;
    public $parent;
    public $null;

    /**
     * InnerPathToken constructor.
     * @param string $propertyName
     * @param $parent
     * @param null $null
     */
    public function __construct($propertyName, $parent, $null)
    {
        $this->propertyName = $propertyName;
        $this->parent = $parent;
        $this->null = $null;
    }
}
